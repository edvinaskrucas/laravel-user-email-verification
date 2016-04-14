# User email verification for Laravel 5


[![Build Status](https://travis-ci.org/edvinaskrucas/laravel-user-email-verification.png?branch=master)](https://travis-ci.org/edvinaskrucas/laravel-user-email-verification)

---

## Installation

Require this package in your composer.json by typing in your console:

```
composer require edvinaskrucas/laravel-user-email-verification
```

### Registering to use it with laravel

Add following lines to ```app/config/app.php```

ServiceProvider array

```php
Krucas\LaravelUserEmailVerification\UserEmailVerificationServiceProvider::class,
```

### Publishing config file

If you want to edit default config file, just publish it to your app folder.

    php artisan vendor:publish --provider="Krucas\LaravelUserEmailVerification\UserEmailVerificationServiceProvider" --tag="config"


### Publishing translations

In order to customise translations you need to publish it.

    php artisan vendor:publish --provider="Krucas\LaravelUserEmailVerification\UserEmailVerificationServiceProvider" --tag="translations"


### Publishing views

Package comes with default views, if you want to edit them, just publish it.

    php artisan vendor:publish --provider="Krucas\LaravelUserEmailVerification\UserEmailVerificationServiceProvider" --tag="views"

## Usage

### Configuration

Package comes with several configuration options.

| Setting | Description |
| --- | --- |
| ```default``` | Default broker driver. |
| ```verify``` | MUST or MUST NOT user validate his account before login. |
| ```repositories``` | Config of all repositories which can be used. |
| ```brokers``` | Config of all brokers which can be used. |

### Install default controller, routes and migrations

```
php artisan verification:make
```

Command above will add default ```VerifyController``` to ```app/Http/Controllers/Auth/VerifyController.php``` which
will provide default verification behaviour.

Also routes will be modified, it will add default routes for verification controller.

Migrations will add extra columns to ```users``` table to identify if user is verified or not, also
token table will be added to store verification tokens.

After running command you have to install new migrations, this can be done with this command:

```
php artisan migrate
```

After all these steps you need to adjust default auth controller provided by Laravel, these adjustments
will enable authentication controller to send verification email and will not allow non-verified users to login.

### Clear expired tokens

Package comes with useful command to clear expired tokens, just replace ```{broker}``` with your broker name.

```
php artisan verification:clear-tokens {broker}
```

---

More info can be found here: [http://www.krucas.com/2016/04/user-email-verification-for-laravel-5/](http://www.krucas.com/2016/04/user-email-verification-for-laravel-5/)