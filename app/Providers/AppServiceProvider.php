<?php

namespace App\Providers;

use App\User;
use App\Product;
use App\Mail\UserCreated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Product::updated(function($product) {
            if ($product->quantity == 0 && $product->estaDisponible()) 
            {
                $product->status = Product::PRODUCT_NOT_AVAILABLE;

                $product->save();
            }
        });

        User::created(function($user) {
            Mail::to($user)->send(new UserCreated($user));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
