<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Seller;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all sellers
        $sellers = Seller::has('products')->get();

        // Resposne json(data, satus_code)
        return response()->json(['data' => $sellers], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Find a Seller
        $seller = Seller::has('products')->findOrFail($id);

        // Response json(data, status_code)
        return response()->json(['data' => $seller], 200);
    }
}
