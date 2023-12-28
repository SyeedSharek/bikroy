<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\User;
use App\Response\AllResponse;
use Illuminate\Http\Request;
use Stripe\StripeClient;


class StripePaymentController extends Controller
{
    use AllResponse;

    public function stripePost(Request $request, $id)
    {
        try {
            // Load Stripe Secret Key from your .env file
            $stripeSecretKey = config('services.stripe.secret');
            $stripe = new StripeClient($stripeSecretKey);
            $plan = SubscriptionType::find($id);
            $value = $plan->price ;
            // Create a PaymentIntent with amount and currency
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $value * 100,
                'currency' => 'usd',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
            return response()->json(['clientSecret' =>  $paymentIntent->client_secret]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function StripeInfoStore(Request $request)
    {
        $user = auth('api')->user();
        $subscription = Subscription::create([
            'subscriptionType_id' => $request->subscriptionType_id,
            'transaction_id' => $request->transaction_id,
            'status' => $request->status
        ]);
        $user->update(['subscription_id' => $subscription->id]);
        // return $this->Response(true, 'Sucessfully created subscription', 200);
    }


}
