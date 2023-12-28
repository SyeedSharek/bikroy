<?php

namespace App\Response;

use App\Models\Boosting;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use Carbon\Carbon;

trait AllResponse
{
    public function SuccessResponseWithUser($user, string $message, int $statusCode)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'user' => $user,
        ], $statusCode);
    }
    public function SuccessResponseOnlyUser($status, $user, int $statusCode)
    {
        return response()->json([
            'status' => $status,
            'adminUser' => $user,
        ], $statusCode);
    }
    public function SuccessResponseWithRoles($user, $role, int $statusCode)
    {
        return response()->json([
            'status' => true,
            'user' => $user,
            'role' => $role,
        ], $statusCode);
    }
    public function PostsResponse($data, int $statusCode)
    {
        return response()->json([
            'data' => $data,
        ], $statusCode);
    }
    public function Response($status, string $message, int $statusCode)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
        ], $statusCode);
    }
    public function updateExpiredBoostings($now_date)
    {
        Boosting::where('paymentStatus', false)
            ->whereDate('created_at', '<', $now_date->subDay(2))
            ->delete();

        $expiredBoostings = Boosting::where('end_boosting', '<', $now_date)
            ->where('paymentStatus', true)
            ->get();

        foreach ($expiredBoostings as $boosting) {
            // Update paymentStatus to false
            $boosting->update(['paymentStatus' => false]);
        }
        $boosted = Boosting::all();
        foreach ($boosted as $boost) {
            Product::where('user_id', $boost->user_id)
                ->where('id', $boost->product_id)
                ->update(['is_boost' => false]);
        }
    }
    public function membership($userSubscriptionId)
    {
        $currentDate = Carbon::now();
        $subscriptedUser = Subscription::find($userSubscriptionId);
        $unSubscriptedUsers = Subscription::where('status', 0)->get();
        if ($unSubscriptedUsers != null) {
            foreach ($unSubscriptedUsers as $unSubscriptedUser) {
                $unSubscriptedAt = $unSubscriptedUser->updated_at;
                $diffD = $unSubscriptedAt->diffInDays($currentDate);
                if ($diffD >= 1) {
                    $unSubscriptedUser->delete();
                }
            }
        }

        $plans = SubscriptionType::all();

        if ($subscriptedUser != null) {
            $subscriptedAt = $subscriptedUser->created_at;

            $planName = $subscriptedUser->plan->name;
            $periodType = $subscriptedUser->plan->period_type;
            $timePeriod = $subscriptedUser->plan->time_period;

            // Calculate EndDate
            if ($periodType === 'week') {
                $endDate = $subscriptedAt->addWeeks($timePeriod);
            } elseif ($periodType === 'month') {
                $endDate = $subscriptedAt->addMonths($timePeriod);
            } elseif ($periodType === 'year') {
                $endDate = $subscriptedAt->addYears($timePeriod);
            }
            // Calculate Remaining Days
            $reaminingAllDays = $currentDate->diffInDays($endDate);
            $remainingYears = intdiv($reaminingAllDays, 365);
            $remainingMonths = intdiv(($reaminingAllDays % 365), 30);
            $remainingDays = $reaminingAllDays % 30;

            // Calculate the difference in weeks, months, years since the subscription started
            $diffW = $subscriptedAt->diffInWeeks($currentDate);
            $diffM = $subscriptedAt->diffInMonths($currentDate);
            $diffY = $subscriptedAt->diffInYears($currentDate);

            // Check if the subscription period has elapsed
            if ($periodType === 'week' && $diffW >= $timePeriod) {

                $this->unsubscribe($subscriptedUser);
            } elseif ($periodType === 'month' && $diffM >= $timePeriod) {

                $this->unsubscribe($subscriptedUser);
            } elseif ($periodType === 'year' && $diffY >= $timePeriod) {

                $this->unsubscribe($subscriptedUser);
            } else {
                foreach ($plans as $plan) {
                    if ($planName == $plan->name) {
                        return response()->json(
                            [
                                'status' => true,
                                'plan' => $plan,
                                'reaminingAllDays' => $reaminingAllDays,
                                'remainingYears' => $remainingYears,
                                'remainingMonths' => $remainingMonths,
                                'remainingDays' => $remainingDays
                            ],
                            200,
                        );
                    }
                }
            }
        }
        $plans = SubscriptionType::all();
        if ($plans->count() != null) {
            return $this->PostsResponse($plans, 200);
        }
        return $this->Response(false, "Plan not found.", 404);
    }
}
