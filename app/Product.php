<?php

namespace App;

use App\Seller;
use App\Category;
use App\Transaction;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\ProductTransformer;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

	const PRODUCT_AVAILABLE = 'disponible';
	const PRODUCT_NOT_AVAILABLE = 'no disponible';

    protected $fillable = [
    	'name',
    	'description',
    	'quantity',
    	'status',
    	'image',
    	'seller_id'
    ];

    protected $dates = ['deleted_at'];
    protected $hidden = [
        'pivot'
    ];

    // Transformer
    public $transformer = ProductTransformer::class;

    public function estaDisponible()
    {
    	return $this->status == Product::PRODUCT_AVAILABLE;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
