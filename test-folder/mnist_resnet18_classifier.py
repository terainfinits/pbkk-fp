#!/usr/bin/env python3
"""
mnist_resnet18_classifier.py

Train a ResNet‑18 on MNIST to classify three categories:
    0 → digit 2
    1 → digit 9
    2 → any other digit (i.e., "not 9")

Usage
-----
python mnist_resnet18_classifier.py \
    --epochs 10 \
    --batch-size 128 \
    --lr 1e-3 \
    --device cuda   # or cpu
"""

import argparse
import os
import random

import torch
import torch.nn as nn
import torch.optim as optim
import torchvision
from torch.utils.data import DataLoader, Dataset
from torchvision import transforms


# ----------------------------------------------------------------------
# Helper: remap MNIST labels to the 3‑class problem
# ----------------------------------------------------------------------
class MNISTThreeClass(Dataset):
    """
    Wraps torchvision.datasets.MNIST and remaps its labels to:
        0 → original label 2
        1 → original label 9
        2 → all other digits
    """
    def __init__(self, root: str, train: bool, transform=None, download: bool = True):
        self.mnist = torchvision.datasets.MNIST(
            root=root, train=train, transform=transform, download=download
        )
        self.transform = transform

    def __len__(self):
        return len(self.mnist)

    def __getitem__(self, idx):
        img, label = self.mnist[idx]

        # Remap label
        if label == 2:
            new_label = 0
        elif label == 9:
            new_label = 1
        else:
            new_label = 2

        return img, new_label


# ----------------------------------------------------------------------
# Model definition – ResNet‑18 with a 3‑output head
# ----------------------------------------------------------------------
def get_resnet18(num_classes: int = 3, pretrained: bool = False) -> nn.Module:
    model = torchvision.models.resnet18(pretrained=pretrained)
    # Replace final FC layer
    model.fc = nn.Linear(in_features=model.fc.in_features, out_features=num_classes)
    return model


# ----------------------------------------------------------------------
# Training / evaluation utilities
# ----------------------------------------------------------------------
def train_one_epoch(
    model: nn.Module,
    loader: DataLoader,
    criterion,
    optimizer,
    device: torch.device,
) -> float:
    model.train()
    running_loss = 0.0
    correct = 0
    total = 0

    for inputs, targets in loader:
        inputs, targets = inputs.to(device), targets.to(device)

        optimizer.zero_grad()
        outputs = model(inputs)
        loss = criterion(outputs, targets)
        loss.backward()
        optimizer.step()

        running_loss += loss.item() * inputs.size(0)

        _, preds = torch.max(outputs, 1)
        correct += (preds == targets).sum().item()
        total += targets.size(0)

    epoch_loss = running_loss / total
    epoch_acc = correct / total
    return epoch_loss, epoch_acc


def evaluate(
    model: nn.Module,
    loader: DataLoader,
    criterion,
    device: torch.device,
) -> float:
    model.eval()
    running_loss = 0.0
    correct = 0
    total = 0

    with torch.no_grad():
        for inputs, targets in loader:
            inputs, targets = inputs.to(device), targets.to(device)

            outputs = model(inputs)
            loss = criterion(outputs, targets)

            running_loss += loss.item() * inputs.size(0)

            _, preds = torch.max(outputs, 1)
            correct += (preds == targets).sum().item()
            total += targets.size(0)

    val_loss = running_loss / total
    val_acc = correct / total
    return val_loss, val_acc


# ----------------------------------------------------------------------
# Main routine
# ----------------------------------------------------------------------
def main():
    parser = argparse.ArgumentParser(description="ResNet‑18 on MNIST (2 / 9 / not‑9)")
    parser.add_argument("--data-dir", default="./mnist_data", help="Root folder for MNIST")
    parser.add_argument("--epochs", type=int, default=10, help="Number of training epochs")
    parser.add_argument("--batch-size", type=int, default=128, help="Batch size")
    parser.add_argument("--lr", type=float, default=1e-3, help="Learning rate")
    parser.add_argument("--device", default="cuda" if torch.cuda.is_available() else "cpu",
                        help="Device to use (cuda or cpu)")
    parser.add_argument("--seed", type=int, default=42, help="Random seed for reproducibility")
    parser.add_argument("--pretrained", action="store_true", help="Use ImageNet‑pretrained weights")
    args = parser.parse_args()

    # ------------------------------------------------------------------
    # Reproducibility
    # ------------------------------------------------------------------
    torch.manual_seed(args.seed)
    random.seed(args.seed)
    if torch.cuda.is_available():
        torch.cuda.manual_seed_all(args.seed)

    device = torch.device(args.device)

    # ------------------------------------------------------------------
    # Data transforms
    # ------------------------------------------------------------------
    transform = transforms.Compose([
        transforms.Resize(224),                     # ResNet‑18 expects 224×224
        transforms.Grayscale(num_output_channels=3),  # Convert 1‑channel → 3‑channel
        transforms.ToTensor(),
        transforms.Normalize(mean=[0.5, 0.5, 0.5],
                             std=[0.5, 0.5, 0.5]),
    ])

    train_set = MNISTThreeClass(root=args.data_dir,
                                train=True,
                                transform=transform,
                                download=True)
    val_set = MNISTThreeClass(root=args.data_dir,
                              train=False,
                              transform=transform,
                              download=True)

    train_loader = DataLoader(train_set,
                              batch_size=args.batch_size,
                              shuffle=True,
                              num_workers=4,
                              pin_memory=True)
    val_loader = DataLoader(val_set,
                            batch_size=args.batch_size,
                            shuffle=False,
                            num_workers=4,
                            pin_memory=True)

    # ------------------------------------------------------------------
    # Model, loss, optimizer
    # ------------------------------------------------------------------
    model = get_resnet18(num_classes=3, pretrained=args.pretrained).to(device)
    criterion = nn.CrossEntropyLoss()
    optimizer = optim.Adam(model.parameters(), lr=args.lr)

    # ------------------------------------------------------------------
    # Training loop
    # ------------------------------------------------------------------
    best_val_acc = 0.0
    for epoch in range(1, args.epochs + 1):
        train_loss, train_acc = train_one_epoch(
            model, train_loader, criterion, optimizer, device
        )
        val_loss, val_acc = evaluate(model, val_loader, criterion, device)

        print(
            f"Epoch [{epoch}/{args.epochs}] "
            f"Train loss: {train_loss:.4f} | Train acc: {train_acc*100:5.2f}% "
            f"| Val loss: {val_loss:.4f} | Val acc: {val_acc*100:5.2f}%"
        )

        # Simple checkpointing (keep best validation model)
        if val_acc > best_val_acc:
            best_val_acc = val_acc
            ckpt_path = os.path.join(args.data_dir, "best_resnet18_mnist.pth")
            torch.save(model.state_dict(), ckpt_path)

    print(f"\nTraining finished. Best validation accuracy: {best_val_acc*100:.2f}%")
    print(f"Best model saved to: {ckpt_path}")


if __name__ == "__main__":
    main()