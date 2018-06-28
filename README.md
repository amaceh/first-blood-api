# First Blood Api

This service is hosted at http://first-blood.dx.am. this repo is temporary backend for our first blood application.

This service use [Slim PHP framework](https://www.slimframework.com/)

## Cara menggunakan
- Download source code
- Edit file `src/setting.php` lalu ubah line berikut, sesuaikan dengan setting database anda.
```php
'db' => [
            'host' => 'localhost',
            'user' => 'root',
            'pass' => '',
            'dbname' => 'first_blood',
            'driver' => 'mysql'
        ],
```
* lalu import file `database.sql` ke dalam database anda
* lalu untuk edit source code, umumnya code yang diubah hanya `src/middleware.php` dan `src/routes.php`
* api bisa diakses dengan alamat <a>http://localhost/firstblood-api</a> bila disimpan pada localhost
