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

version pre-release
- `v0.0.0` base project Agung pbkk-tugas-2 
- `v0.1.0` connect to gemini 
- `v0.2.0` connect to ollama cloud 
- `v0.2.1` modularize file controller 
- `v0.3.0` add terminal feature, bug exist in terminal 
- `v0.3.1` fix terminal bug 
- `v0.4.0` change workspace directory, automatically write file to text editor after prompt and UI accept/reject changes and view diff; bug input terminal when running python with function input()

