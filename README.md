## Introduction

This package extends Socialite to enable passwordless authentication with the Affinidi OAuth2 provider.

Learn more about Laravel Socialite [here](https://laravel.com/docs/10.x/socialite)

## Installation & Basic Usage

To get started with Affinidi Socialite, follow these steps:

1. Install the Affinidi Socialite package using Composer:

```
composer require affinidi/laravel-socialite-affinidi
```

2. Add the following configuration to your `config/services.php` file:

```
'affinidi' => [
    'base_uri' => env('AFFINIDI_ISSUER'),
    'client_id' => env('AFFINIDI_CLIENT_ID'),
    'client_secret' => env('AFFINIDI_CLIENT_SECRET'),
    'redirect' => '/login/affinidi/callback',
],
```

3. Extend the setup of the Affinidi Socialite driver using our helper class in the `boot()` function of `app\Providers\AppServiceProvider.php`:

```
public function boot(): void
{
    $socialite = $this->app->make(Factory::class);
    // Setup the affinidi driver
    \Affinidi\SocialiteProvider\AffinidiSocialite::extend($socialite);
}
```

# Authentication

To authenticate users using an OAuth provider, you will need two routes: one for redirecting the user to the OAuth provider, and another for receiving the callback from the provider after authentication.

The example routes below demonstrate the implementation of both routes:

```
use Laravel\Socialite\Facades\Socialite;

Route::get('/login/affinidi/redirect', function () {
    return Socialite::driver('affinidi')->redirect();
});

Route::get('/login/affinidi/callback', function () {
    $user = Socialite::driver('affinidi')->user();

    // $user->token
});
```
