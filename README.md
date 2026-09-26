# PBKK-FP

## Website Link (VPS Agung)

[http://172.188.98.77/](http://172.188.98.77/)

## Local Setup

```
git clone https://github.com/terainfinits/pbkk-fp
cd pbkk-fp
```

Setelah di folder kerja jalankan command ini
```
composer install
```
```
npm install
```
```
cp .env.example .env
```
```
php artisan key:generate
```
```
php artisan migrate
```
```
composer run dev
```

## Routes list
- `/` home page <br>
- `/agent/{tema?}` agent page <br>
- `/mahasiswa/{nrp 10 digit}` student profile page <br>
- `/hitung-ipk/{ip1?}/{ip2?}` ipk calculator <br>
- `/dashboard/mahasiswa/{nrp 10 digit}` student profile page but using dashboard prefix <br>
- `/dashboard/` home page but using dashboard prefix <br>
