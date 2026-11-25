# Fitur: Contoh keluaran workspace
# Koneksi lokal: berdiri sendiri; tidak ditemukan rujukan antarfile.
import pandas as pd

def load_data(file_path):
    """
    Load data from a CSV file.
    """
    try:
        data = pd.read_csv(file_path)
        print("Data loaded successfully.")
        return data
    except FileNotFoundError:
        print(f"Error: File not found at {file_path}")
        return None