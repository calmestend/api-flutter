<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Cart;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;

class PayPalController extends Controller
{
    public function createPayment()
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);

        $cart = Auth::user()->cart;
        $total = $cart->products->sum(function ($product) {
            return $product->pivot->quantity * $product->price;
        });

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "MXN",
                        "value" => $total
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] == 'approve') {
                    return redirect($link['href']);
                }
            }

            return redirect()->route('paypal.error');
        } else {
            return redirect()->route('paypal.error');
        }
    }

    public function capturePayment(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);

        $response = $provider->capturePaymentOrder($request->token);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            $cart = Auth::user()->cart;
            $purchase = Purchase::create([
                'user_id' => Auth::id(),
                'total' => $cart->products->sum(function ($product) {
                    return $product->pivot->quantity * $product->price;
                }),
                'details' => json_encode($cart->products)
            ]);

            $cart->products()->detach();

            return redirect()->route('paypal.success');
        } else {
            return redirect()->route('paypal.error');
        }
    }

    public function success()
    {
        return view('paypal.success');
    }

    public function error()
    {
        return view('paypal.error');
    }
}
