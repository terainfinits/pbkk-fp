import nltk
from nltk.inference import ResolutionProver
from nltk.sem import Expression

# Inisialisasi parser ekspresi logika NLTK
read_expr = Expression.fromstring


def main():
    print("=== DEMO FIRST-ORDER LOGIC (FOL) DENGAN NLTK ===\n")

    # -------------------------------------------------------------------------
    # 1. PENETAPAN PREMIS (FAKTA & ATURAN)
    # -------------------------------------------------------------------------

    # Premis 1 (Aturan Kuantifikasi Universal):
    # Logika: Forall x, Human(x) -> Mortal(x)
    # Penjelasan: Untuk setiap x, jika x adalah manusia, maka x fana (mortal).
    p1 = read_expr("all x. (human(x) -> mortal(x))")

    # Premis 2 (Fakta Proposional/Predikat):
    # Logika: Human(socrates)
    # Penjelasan: Socrates adalah seorang manusia.
    p2 = read_expr("human(socrates)")

    # Daftar premis yang dikumpulkan sebagai basis pengetahuan (Knowledge Base)
    premises = [p1, p2]

    print("Premis yang diketahui:")
    for i, p in enumerate(premises, 1):
        print(f"  Premis {i}: {p}")
    print()

    # -------------------------------------------------------------------------
    # 2. PENETAPAN KONKLUSI / KLAIM (GOAL)
    # -------------------------------------------------------------------------

    # Goal (Konklusi yang ingin dibuktikan):
    # Logika: Mortal(socrates)
    # Penjelasan: Apakah socrates fana (mortal)?
    goal = read_expr("mortal(socrates)")
    print(f"Konklusi yang ingin dibuktikan (Goal): {goal}\n")

    # -------------------------------------------------------------------------
    # 3. PROSES INFERENSI LOGIKA (RESOLUTION PROVER)
    # -------------------------------------------------------------------------
    # Prover akan mencoba membuktikan Goal berdasarkan Premis yang ada
    # menggunakan metode kontradiksi (Resolution Refutation).

    print("--- Proses Resolusi Logika ---")
    prover = ResolutionProver()
    result = prover.prove(goal, premises, verbose=True)

    # -------------------------------------------------------------------------
    # 4. HASIL EVALUASI
    # -------------------------------------------------------------------------
    print("\n--- Hasil Pembuktian ---")
    if result:
        print(
            "RESULT: TRUE (Konklusi Valid secara logika berdasarkan premis yang diberikan)."
        )
    else:
        print(
            "RESULT: FALSE (Konklusi TIDAK dapat dibuktikan dari premis yang ada)."
        )


if __name__ == "__main__":
    main()