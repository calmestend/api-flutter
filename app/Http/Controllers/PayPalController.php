<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Cart;
use App\Models\Purchase;
use App\Models\User;

class PayPalController extends Controller
{
    public function createPayment($userId)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);

        $user = User::findOrFail($userId);
        $cart = $user->cart;
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
            ],
            "application_context" => [
                "return_url" => route('paypal.capture', ['userId' => $user->id]),
                "cancel_url" => route('paypal.error')
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



    public function capturePayment(Request $request, $userId)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);

        $response = $provider->capturePaymentOrder($request->token);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            $user = User::findOrFail($userId);
            $cart = $user->cart;
            $purchase = Purchase::create([
                'user_id' => $user->id,
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
        return response()->json(['status' => 'success']);
    }

    public function error() {
        return view('paypal.error');
    }
}
