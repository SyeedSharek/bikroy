<?php

namespace App\Http\Controllers;

use App\Events\SentMailEvent;
use App\Http\Requests\Plan\PlanCreateRequest;
use App\Http\Requests\Plan\PlanUpdateRequest;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\User;
use App\Response\AllResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionTypeController extends Controller
{
    use AllResponse;

    // All Plans
    public function index()
    {
        // Fetch all subscription plans
        $plans = SubscriptionType::all();

        // Check if any plans were found
        if ($plans->count() != null) {
            // Return a JSON response with the list of plans
            return $this->PostsResponse($plans, 200);
        }

        // Return a response indicating that plans were not found
        return $this->Response(false, "Plan not found.", 404);
    }


    // Auto User Unsubscribe
    public function unsubscribe($subscriptedUser)
    {
        $subscriptedUser->update(['status' => false]);
    }

    // Membership page of Users
    // Check and manage user subscriptions and membership status.
    public function membership()
    {
        $userSubscriptionId = auth('api')->user()->subscription_id;
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


    public function create()
    {
        //
    }

    // Admin add Plans
    public function store(PlanCreateRequest $request)
    {
        // Check if the authenticated admin user has permission to create a subscription
        if (auth('admin')->user()->hasPermissionTo('create subscription')) {

            // Create a new subscription type record
            $subscriptionType = SubscriptionType::create([
                'name' => $request->name,
                'price' => $request->price,
                'period_type' => $request->period_type,
                'time_period' => $request->time_period,
            ]);

            // Check if the subscription type was created successfully
            if ($subscriptionType) {
                // Return a response indicating success
                return $this->Response(true, "Plan added successfully.", 200);
            }

            // Return a response indicating failure
            return $this->Response(false, "Plan not added.", 400);
        } else {
            // Return a response indicating unauthorized and forbidden access
            return $this->Response(false, "Unauthorized & Forbidden Access.", 403);
        }
    }


    public function show($id)
    {
        // $subscriptionType = SubscriptionType::find($id);
        // if ($subscriptionType != null) {
        //     return response()->json(
        //         [
        //             'message' => 'Subscription Type details.',
        //             'subscriptionType' => $subscriptionType,
        //         ],
        //         200,
        //     );
        // }
        // return response()->json(
        //     [
        //         'message' => 'Subscription Type not Found.',
        //     ],
        //     404,
        // );
    }

    // Update Plan page for Admin
    public function edit($id)
    {
        // Find the subscription plan by its ID
        $subscriptionType = SubscriptionType::find($id);

        // Check if the subscription plan exists
        if ($subscriptionType != null) {
            // Return a JSON response with the subscription plan details
            // return $this->PostsResponse($subscriptionType, 200);
            return response()->json(
                [
                    'subscriptionType' => $subscriptionType,
                ],
                200
            );
        }

        // Return a response indicating that the plan was not found
        return $this->Response(false, "Plan not found.", 404);
    }


    // Admin Update Plan
    public function update(PlanUpdateRequest $request, $id)
    {
        // Check if the authenticated admin user has permission to edit a subscription
        if (auth('admin')->user()->hasPermissionTo('edit subscription')) {

            // Find the subscription plan by its ID
            $subscriptionType = SubscriptionType::find($id);

            // Prepare the data to be updated
            // $requestData = [
            //     'name' => $request->name,
            //     'price' => $request->price,
            //     'period_type' => $request->period_type,
            //     'time_period' => $request->time_period,
            // ];
            $requestData = $request->validated();
            // Update the subscription plan with the new data
            $subscriptionType->update($requestData);

            // Check if the update was successful
            if ($subscriptionType) {
                // Return a response indicating success
                return $this->Response(true, "Plan updated successfully.", 200);
            }

            // Return a response indicating failure
            return $this->Response(false, "Plan not updated.", 400);
        } else {
            // Return a response indicating unauthorized and forbidden access
            return $this->Response(false, "Unauthorized & Forbidden Access.", 403);
        }
    }


    // Plan Delete by Admin
    public function destroy($id)
    {
        // Check if the authenticated admin user has permission to delete a subscription
        if (auth('admin')->user()->hasPermissionTo('delete subscription')) {

            // Find the subscription plan by its ID
            $subscriptionType = SubscriptionType::find($id);

            // Check if the subscription plan exists
            if ($subscriptionType != null) {
                // Delete the subscription plan
                $subscriptionType->delete();

                // Return a response indicating success
                return $this->Response(true, "Plan deleted successfully.", 200);
            }

            // Return a response indicating that the plan was not found
            return $this->Response(false, "Plan not found.", 404);
        } else {
            // Return a response indicating unauthorized and forbidden access
            return $this->Response(false, "Unauthorized & Forbidden Access.", 403);
        }
    }
}
