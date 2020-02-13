<?php

namespace App;

use App\Product;
use App\User;

class Seller extends User
{
    public function products()
    {
    	return $this->hasMany(Product::class);
    }
}
