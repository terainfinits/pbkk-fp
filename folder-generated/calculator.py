#!/usr/bin/env python3
"""
Simple Calculator

Fitur:
- Penjumlahan
- Pengurangan
- Perkalian
- Pembagian
- Menangani pembagian dengan nol
- Antarmuka teks interaktif
"""

def add(a: float, b: float) -> float:
    """Return a + b."""
    return a + b


def subtract(a: float, b: float) -> float:
    """Return a - b."""
    return a - b


def multiply(a: float, b: float) -> float:
    """Return a * b."""
    return a * b


def divide(a: float, b: float) -> float:
    """Return a / b. Raises ValueError on division by zero."""
    if b == 0:
        raise ValueError("Tidak dapat membagi dengan nol.")
    return a / b


def get_number(prompt: str) -> float:
    """Minta input angka dari pengguna, dengan validasi."""
    while True:
        try:
            return float(input(prompt))
        except ValueError:
            print("Input tidak valid. Masukkan angka.")


def get_operation() -> str:
    """Minta pilihan operasi dari pengguna."""
    ops = {
        "1": ("Penjumlahan", add),
        "2": ("Pengurangan", subtract),
        "3": ("Perkalian", multiply),
        "4": ("Pembagian", divide),
    }
    print("\nPilih operasi:")
    for key, (name, _) in ops.items():
        print(f"  {key}. {name}")
    while True:
        choice = input("input: ").strip()
        if choice.lower() == "q":
            return "q"
        if choice in ops:
            return ops[choice][1]
        print("Pilihan tidak valid, coba lagi.")


def main() -> None:
    print("=== Kalkulator Sederhana ===")
    while True:
        operation = get_operation()
        if operation == "q":
            print("Terima kasih! Sampai jumpa.")
            break

        a = get_number("Masukkan angka pertama: ")
        b = get_number("Masukkan angka kedua: ")

        try:
            result = operation(a, b)
            print(f"Hasil: {result}\n")
        except ValueError as e:
            print(f"Error: {e}\n")


if __name__ == "__main__":
    main()