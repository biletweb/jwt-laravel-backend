<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>
</p>

## Project Setup

```sh
composer install
```

### Setup the configuration file

```sh
rename .env.example to .env and do the setup
```

### Perform migrations to work with the database

```sh
php artisan migrate
```

### Generate a secret key for working with JWT

```sh
php artisan jwt:secret
```

### Start a local development server

```sh
php artisan serve
```

To work you need to install the frontend <a href="https://github.com/biletweb/jwt-vue-frontend">jwt-vue-frontend</a>
