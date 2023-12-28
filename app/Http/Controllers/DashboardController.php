<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use App\Response\AllResponse;
use App\Traits\HaumanReadable;

class DashboardController extends Controller
{
    use AllResponse, HaumanReadable;
    public function userPanel(Request $request)
    {
        $userId = auth('api')->user()->id;
        $subscribtionId = auth('api')->user()->subscription_id;
        $plan = 0;
        if ($subscribtionId) {
            $plan = Subscription::find($subscribtionId)->with('plan')->get();
        }
        $postCount = Product::where('user_id', $userId)->count();
        $postVisitor = Product::where('user_id', $userId)->popularAllTime()->get()->sum('visit_count_total');
        $totalProfit = Product::where('user_id', $userId)->where('is_sold', true)->get();
        $totalProfit = $totalProfit->sum('price');

        return $this->PostsResponse([
            'currentPlan' => $plan,
            'totalAds' => $this->convert($postCount),
            'totalVisitor' => $this->convert($postVisitor),
            'totalPrice' => $this->convert($totalProfit)
        ], 200);
    }
    public function adminPanel()
    {
        $userall = User::all()->count();
        $allProduct = Product::all()->count();
        $inactiveProduct = Product::where('status', 0)->count();
        $subscriptions = Subscription::where('status', 1)->with('plan')->get();
        $totalProfit = $subscriptions->sum(function ($subscription) {
            return $subscription->plan->price;
        });
        return $this->PostsResponse([
            'allUsers' => $this->convert($userall),
            'totalAds' => $this->convert($allProduct),
            'inactiveAds' => $this->convert($inactiveProduct),
            'totalProfit' => $this->convert($totalProfit)
        ], 200);
    }
}
