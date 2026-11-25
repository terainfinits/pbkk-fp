# Fitur: Contoh keluaran workspace
# Koneksi lokal: berdiri sendiri; tidak ditemukan rujukan antarfile.
def generate_primes(limit):
    """Generate a list of prime numbers up to the given limit."""
    def is_prime(n):
        """Check if a number is prime."""
        if n < 2:
            return False
        for i in range(2, int(n**0.5) + 1):
            if n % i == 0:
                return False
        return True

    primes = []
    for num in range(2, limit + 1):
        if is_prime(num):
            primes.append(num)
    return primes

if __name__ == "__main__":
    primes = generate_primes(50)
    print("Prime numbers up to 50:", primes)