<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
public function index($userId)
{
$cart = Cart::where('user_id', $userId)->first();
return response()->json($cart ? $cart->products()->withPivot('quantity')->get() : []);
}

public function addProduct(Request $request, $userId, Product $product)
{
$cart = Cart::firstOrCreate(['user_id' => $userId]);
$quantity = $request->quantity ?? 1;

$existingProduct = $cart->products()->where('product_id', $product->id)->first();
if ($existingProduct) {
$newQuantity = $existingProduct->pivot->quantity + $quantity;
$cart->products()->updateExistingPivot($product->id, ['quantity' => $newQuantity]);
} else {
$cart->products()->attach($product->id, ['quantity' => $quantity]);
}

return response()->json(['message' => 'Product added to cart']);
}

public function removeProduct(Request $request, $userId, Product $product)
{
$cart = Cart::where('user_id', $userId)->first();
$quantity = $request->quantity ?? 1;

if ($cart) {
$existingProduct = $cart->products()->where('product_id', $product->id)->first();
if ($existingProduct) {
$newQuantity = $existingProduct->pivot->quantity - $quantity;
if ($newQuantity > 0) {
$cart->products()->updateExistingPivot($product->id, ['quantity' => $newQuantity]);
} else {
$cart->products()->detach($product->id);
}
}
}

return response()->json(['message' => 'Product removed from cart']);
}

public function updateProduct(Request $request, $userId, Product $product)
{
$cart = Cart::where('user_id', $userId)->first();

if ($cart) {
$cart->products()->updateExistingPivot($product->id, ['quantity' => $request->quantity]);
}

return response()->json(['message' => 'Product quantity updated']);
}
}
