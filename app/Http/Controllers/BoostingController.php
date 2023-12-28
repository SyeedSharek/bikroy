<?php

namespace App\Http\Controllers;

use App\Models\Boosting;
use App\Models\Product;
use App\Models\Setting;
use App\Response\AllResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

class BoostingController extends Controller
{
    use AllResponse;
    public function boosting_product(Request $request, $id)
    {
        $now_date = Carbon::now();
        $req_date = $request->date;
        $end_boosting = Carbon::parse($req_date)->format('Y/m/d H:i:s');
        $diff_days = $now_date->diffInDays($end_boosting) + 1;
        $settings_data = Setting::first();
        $boosting_price = $settings_data->boosting_price;
        $discoount_price = $settings_data->boosting_discount_price;
        $total_discount_price =  ($boosting_price * $diff_days) - ($boosting_price * $diff_days * $discoount_price);
        if ($diff_days > 1) {
            $total_price_count =  $total_discount_price;
        } else {
            $total_price_count = $settings_data->boosting_price;
        }
        $user = auth('api')->user();
        $product = Product::where('user_id', $user->id)->find($id);
        $existingBoosting = Boosting::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('paymentStatus', true)
            ->first();
        if ($existingBoosting) {
            return $this->Response(false, "Product is already boosted.", 400);
        }
        $boost = Boosting::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'end_boosting' => $end_boosting,
            'days' =>  $diff_days,
            'price' => $total_price_count,
            'paymentStatus' => false
        ]);
    }
    // if user payment successful for product boost
    public function boostingInfoStore($id)
    {
        $user = auth('api')->user();
        Boosting::where('product_id', $id)->where('user_id', $user->id)->update([
            'paymentStatus' => true
        ]);
        $boosted = Boosting::where('product_id', $id)->where('user_id', $user->id)->first();
        Product::where('user_id', $boosted->user_id)->update(['is_boost' => 1]);
        return $this->Response(true, "Product Boosted successfully", 200);
    }
    public function StripePayment(Request $request, $id)
    {
        try {
            $stripeSecretKey = config('services.stripe.secret');
            $stripe = new StripeClient($stripeSecretKey);
            $user = auth('api')->user();
            $boosted = Boosting::where('product_id', $id)->where('user_id', $user->id)->first();
            $value = $boosted->price;
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
}
