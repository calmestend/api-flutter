<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = Auth::user()->cart;
        return response()->json($cart->products);
    }

    public function addProduct(Request $request, Product $product)
    {
        $cart = Auth::user()->cart;

        if (!$cart) {
            $cart = Cart::create(['user_id' => Auth::id()]);
        }

        $cart->products()->attach($product->id, ['quantity' => $request->quantity]);

        return response()->json(['message' => 'Product added to cart']);
    }

    public function removeProduct(Product $product)
    {
        $cart = Auth::user()->cart;

        if ($cart) {
            $cart->products()->detach($product->id);
        }

        return response()->json(['message' => 'Product removed from cart']);
    }

    public function updateProduct(Request $request, Product $product)
    {
        $cart = Auth::user()->cart;

        if ($cart) {
            $cart->products()->updateExistingPivot($product->id, ['quantity' => $request->quantity]);
        }

        return response()->json(['message' => 'Product quantity updated']);
    }
}
