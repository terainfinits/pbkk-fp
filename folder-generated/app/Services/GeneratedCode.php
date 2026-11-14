import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
import os

# Ensure output directory exists
os.makedirs("pipeline/output", exist_ok=True)

def load_data(file_path: str) -> pd.DataFrame:
    """Load data from CSV file."""
    return pd.read_csv(file_path)

def clean_data(df: pd.DataFrame) -> pd.DataFrame:
    """Clean data: handle missing values, type conversion."""
    df = df.dropna()  # Example: drop missing rows
    df["date"] = pd.to_datetime(df["date"])  # Example: convert date column
    return df

def analyze_data(df: pd.DataFrame) -> dict:
    """Perform basic statistical analysis."""
    return {
        "summary_stats": df.describe(),
        "missing_values": df.isnull().sum(),
        "correlation_matrix": df.corr().to_dict(),
    }

def visualize_data(df: pd.DataFrame, output_path: str):
    """Generate visualizations and save to file."""
    plt.figure(figsize=(10, 6))
    sns.histplot(df["value"], bins=30, kde=True)
    plt.title("Distribution of Values")
    plt.savefig(output_path)
    plt.close()

def generate_report(analyis_results: dict, output_path: str):
    """Generate a text report from analysis results."""
    with open(output_path, "w") as f:
        f.write("Data Analysis Report\n")
        f.write("=" * 40 + "\n")
        f.write(f"Summary Statistics:\n{analyis_results['summary_stats']}\n")
        f.write(f"Missing Values:\n{analyis_results['missing_values']}\n")
        f.write(f"Correlation Matrix:\n{analyis_results['correlation_matrix']}\n")

def main():
    # Configuration
    input_file = "pipeline/data/raw_data.csv"
    cleaned_file = "pipeline/output/cleaned_data.csv"
    plot_file = "pipeline/output/distribution.png"
    report_file = "pipeline/output/analysis_report.txt"

    # Execute pipeline
    df = load_data(input_file)
    cleaned_df = clean_data(df)
    cleaned_df.to_csv(cleaned_file, index=False)
    analysis_results = analyze_data(cleaned_df)
    visualize_data(cleaned_df, plot_file)
    generate_report(analysis_results, report_file)

    print("Pipeline completed successfully.")

if __name__ == "__main__":
    main()