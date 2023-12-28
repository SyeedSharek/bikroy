<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Response\AllResponse;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalController extends Controller
{
    use AllResponse;
    public function paypalCredential()
    {
        $CliendId = config('paypal.sandbox.client_id');
        return response()->json([
            'clientId' => $CliendId,
            'currency' => "USD",
            'intent' => "capture",
        ]);
    }
    public function PaypalInfoStore(Request $request)
    {
        $user = auth('api')->user();
        $subscription = Subscription::create([
            'subscriptionType_id' => $request->subscriptionType_id,
            'transaction_id' => $request->transaction_id,
            'status' => $request->status
        ]);
        $user->update(['subscription_id' => $subscription->id]);
        return $this->Response(true, 'Your are successfully subscripted', 200);
    }
}
