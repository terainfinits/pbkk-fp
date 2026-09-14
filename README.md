# PBKK-Tugas-2

## Website Link

[http://172.188.98.77/](http://172.188.98.77/)

## Local Setup

```
git clone git@github.com:theRadn/PBKK-Tugas-2.git
cd PBKK-Tugas-2
composer install
npm install
cp .env.example .env
php key:generate
php artisan migrate
composer run dev
```

## Routes list
- `/` home page <br>
- `/agent/{tema?}` agent page <br>
- `/mahasiswa/{nrp 10 digit}` student profile page <br>
- `/hitung-ipk/{ip1?}/{ip2?}` ipk calculator <br>
- `/dashboard/mahasiswa/{nrp 10 digit}` student profile page but using dashboard prefix <br>
- `/dashboard/` home page but using dashboard prefix <br>
