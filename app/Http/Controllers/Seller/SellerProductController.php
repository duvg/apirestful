<?php

namespace App\Http\Controllers\Seller;

use App\User;
use App\Seller;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Transformers\ProductTransformer;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware('transform.input:' . ProductTransformer::class)->only(['store', 'update'])
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller)
    {
        // Get all products of seller
        $products = $seller->products;

        return $this->showAll($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $seller)
    {
        // Rules 
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'quantity' => 'required|integer|min:1',
            'image' => 'required|image'
        ];

        // Validate request
        $this->validate($request, $rules);

        // Get all data and modify
        $data = $request->all();

        $data['status'] = Product::PRODUCT_NOT_AVAILABLE;
        $data['image'] = $request->image->store('');

        $data['seller_id'] = $seller->id;

        // save
        $product = Product::create($data);

        return $this->showOne($product, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Seller $seller, Product $product)
    {
        // Rules
        $rules = [
            'quantity' => 'integer|min:1',
            'status' => 'in: '.Product::PRODUCT_AVAILABLE . ',' . Product::PRODUCT_NOT_AVAILABLE,
            'image' => 'image',
        ];

        // Validate request
        $this->validate($request, $rules);

        // Check if id received is id of seller product
        $this->verifySeller($seller, $product);

        $product->fill($request->only(
            'name',
            'description',
            'quantity'
        ));

        if ($request->has('status')) 
        {
            $product->status = $request->status;

            if ($product->estaDisponible() && $product->categories()->count() == 0) 
            {
                return $this->errorResponse('Un producto debe tener almenos una categoria para estar disponible', 409);
            }
        }

        // Veriofy if request has image
        if ($request->hasFile('image')) {
            Storage::delete($product->image);

            $product->image = $request->image->store('');
        }

        if ($product->isClean()) 
        {
            return $this->errorResponse('Se debe especificar al menos un valor diferente para cambiar', 422);
        }

        $product->save();

        return $this->showOne($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seller $seller, Product $product)
    {
        // Check if id received is id of seller product
        $this->verifySeller($seller, $product);

        Storage::delete($product->image);

        $product->delete();

        return $this->showOne($product);

    }

    // Check if id received is id of seller product
    protected function verifySeller(Seller $seller, Product $product)
    {
        if ($seller->id != $product->seller->id) 
        {
            throw new HttpException(422, 'El vendedor especificado no es el vendedor de este producto');
        }
    }
}
