<?php

namespace App\Http\Controllers\Product;

use App\User;
use App\Product;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Transformers\TransactionTransformer;

class ProductBuyerTransactionController extends ApiController
{

    public function __oncstruct()
    {
        parent::__construct();
        $this->middleware('transform.input' . TransactionTransformer::class)->only(['store']);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Product $product, User $buyer)
    {

        // Rules
        $rules = [
            'quantity' => 'required|integer|min:1',
        ];

        $this->validate($request, $rules);

        // Verify if the buyer and the seller are different
        if ($buyer->id == $product->seller_id) 
        {
            return $this->errorResponse('El comprador debe ser diferente al vendedor', 409);
        }

        // Check if buyer is verified
        if (!$buyer->isVerified()) 
        {
            return $this->errorResponse('El comprador debe ser un usurio verificado', 409);
        }

        // Check if buyer is verified
        if (!$product->seller->isVerified()) 
        {
            return $this->errorResponse('El vendedor debe ser un usurio verificado', 409);
        }

        // Check if product is available
        if (!$product->estaDisponible()) 
        {
            return $this->errorResponse('El producto para esta transacción no esta disponible', 409);
        }

        // Check quantity product
        if ($product->quantity < $request->quantity) 
        {
            return $this->errorResponse('El producto no tiene la cantidad disponible requerida para esta transacción', 409);
        }

        // Create transactions for this product

        return DB::transaction(function () use ($request, $product, $buyer ) {
            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([
                'quantity' => $request->quantity,
                'buyer_id' => $buyer->id,
                'product_id' => $product->id,
            ]);

            return $this->showOne($transaction, 201);
        });


    }
}
