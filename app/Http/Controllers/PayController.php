<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\OrderItem;
use App\Exceptions\ApiError;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PayController extends Controller
{
    public function createPay(Request $request, $orderId)
    {
        // Retrieve the order by ID and get the total_price
        $order = Order::findOrFail($orderId);
        $amount = $order->total_price;

        // Authenticate with Paymob to get the token
        $response = Http::post('https://accept.paymob.com/api/auth/tokens', [
            'username' => '01094733558',
            'password' => 'Hi01094733558$',
        ]);

        $token = $response->json('token'); // Use only the token, without concatenating profile_token

        // Create the payment link
        $response = Http::withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
            'Authorization' => 'Bearer ' . $token,
        ])->post('https://accept.paymob.com/api/ecommerce/payment-links', [
            'payment_methods' => [4853862],
            'amount_cents' => $amount * 100, // Convert amount to cents
            'is_live' => true,
        ]);

        // Check for success or failure
        if ($response->successful()) {
            $finalUrl = $response->json()['client_url'];
            return response()->json($finalUrl, 200);
        }

        // If there was an error, log it and return a response
        Log::error('Payment link creation failed: ' . $response->body());
        return response()->json(['message' => 'Failed to create payment link.'], 500);
    }


}
