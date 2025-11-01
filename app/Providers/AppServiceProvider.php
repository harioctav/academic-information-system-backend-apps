<?php

namespace App\Providers;

use App\Channels\WhatsAppChannel;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    $loader = AliasLoader::getInstance();
    $loader->alias('GradeType', \App\Enums\Evaluations\GradeType::class);
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Passport::tokensExpireIn(now()->addDays(15));
    Passport::refreshTokensExpireIn(now()->addDays(30));
    Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    Passport::enablePasswordGrant();

    // Register custom WhatsApp notification channel
    Notification::extend('whatsapp', function ($app) {
      return new WhatsAppChannel();
    });
  }
}
