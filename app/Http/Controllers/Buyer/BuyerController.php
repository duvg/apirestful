<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all buyers 
        $buyers = Buyer::has('transactions')->get();

        // Response json(data, status_code)
        return response(['data' => $buyers], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Fin a buyer
        $buyer = Buyer::has('transactions')->findOrFail($id);

        // Response json(data, status_code)
        return response()->json(['data' => $buyer], 200);
    }

}
