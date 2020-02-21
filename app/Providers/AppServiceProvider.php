<?php

namespace App\Providers;

use App\User;
use App\Product;
use App\Mail\UserCreated;
use App\Mail\UserMailChanged;
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

        // Verificar la disponibilidad del producto
        Product::updated(function($product) {
            if ($product->quantity == 0 && $product->estaDisponible()) 
            {
                $product->status = Product::PRODUCT_NOT_AVAILABLE;

                $product->save();
            }
        });

        // Verificar el nuevo email
        User::updated(function($user) {
            if ($user->isDirty('email')) 
            {
                retry(5, function() use ($user ) {
                    Mail::to($user)->send(new UserMailChanged($user));    
                }, 100);
            }
        });

        // Envio de correo para verficar la nueva cuenta
        User::created(function($user) {
            retry(5, function() use ($user ) {
                Mail::to($user)->send(new UserCreated($user));
            }, 100);
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
