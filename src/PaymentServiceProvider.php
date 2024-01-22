<?php

namespace LiveControls\Payment;

use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
  public function register()
  {
  }

  public function boot()
  {
    $this->publishes([
      __DIR__.'/../config/config.php' => config_path('livecontrols_payment.php'),
    ], 'livecontrols.payment.config');
  }
}
