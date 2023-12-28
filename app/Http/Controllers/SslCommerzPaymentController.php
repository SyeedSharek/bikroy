<?php

namespace App\Http\Controllers;

use App\Events\SentMailEvent;
use Illuminate\Http\Request;
use App\Library\SslCommerz\SslCommerzNotification;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SslCommerzPaymentController extends Controller
{

    public function paynow()
    {
        $items = SubscriptionType::all();
        $user = User::all();

        //return $items;

        return view('pay_ssl', compact('items','user'));
    }



    public function index(Request $request, $id)
    {
        $plan = SubscriptionType::find($id);

        // $value = $plan->price;
    //   $user = auth()->user();
        $value = $plan->price;

        $post_data = array();
        $post_data['total_amount'] = $value; # You can not pay less than 10
        $post_data['currency'] = "BDT";
        $post_data['tran_id'] = uniqid();
        // Rest of your code...
    // tran_id must be unique
        # CUSTOMER INFORMATION
         $post_data['cus_name'] = 'Customer Name';
        $post_data['cus_email'] = 'customer@mail.com';
        $post_data['cus_add1'] = 'Customer Address';
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = '8801XXXXXXXXX';



        $post_data['user_id'] = 2;
        $post_data['subscriptionType_id'] = 1;

        // # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Store Test";
        $post_data['ship_add1'] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_phone'] = "";
        $post_data['ship_country'] = "Bangladesh";

        $post_data['shipping_method'] = "NO";
        $post_data['product_name'] = "Computer";
        $post_data['product_category'] = "Goods";
        $post_data['product_profile'] = "physical-goods";

        // # OPTIONAL PARAMETERS
        $post_data['value_a'] = "ref001";
        $post_data['value_b'] = "ref002";
        $post_data['value_c'] = "ref003";
        $post_data['value_d'] = "ref004";


        #Before  going to initiate the payment order status need to insert or update as Pending.
        $update_product = Subscription::where('transaction_id', $post_data['tran_id'])
            ->updateOrInsert([
                // 'name' => $post_data['cus_name'],
                // 'email' => $post_data['cus_email'],
                // 'phone' => $post_data['cus_phone'],
                // 'amount' => $post_data['total_amount'],
                // 'status' => false,
                // 'address' => $post_data['cus_add1'],
                // 'transaction_id' => $post_data['tran_id'],
                // 'currency' => $post_data['currency'],

                'user_id' =>2,
                'subscriptionType_id' => $plan->id,
                'status' => false,
                'transaction_id' => $post_data['tran_id'],
                'created_at' => now(),
                 'updated_at' => now(),
            ]);

        $sslc = new SslCommerzNotification();
        # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
        $payment_options = $sslc->makePayment($post_data, 'hosted');

        if (!is_array($payment_options)) {
            print_r($payment_options);
            $payment_options = array();
        }
    }



    public function success(Request $request)
    {
        echo "Transaction is Successful";

        //$user = auth('api')->user();


        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');

        $sslc = new SslCommerzNotification();

        #Check order status in order tabel against the transaction id or order id.
        $order_details = DB::table('subscriptions')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status')->first();

        if ($order_details->status == false) {
            // $validation = $sslc->orderValidate($request->all(), $tran_id, $amount, $currency);
            $validation = $sslc->orderValidate($request->all(), $tran_id, $amount, $currency);


            if ($validation) {
                /*
                That means IPN did not work or IPN URL was not set in your merchant panel. Here you need to update order status
                in order table as Processing or Complete.
                Here you can also sent sms or email for successfull transaction to customer
                */
                $update_product = DB::table('subscriptions')
                    ->where('transaction_id', $tran_id)
                    ->update(['status' => true, 'updated_at' => now()]);


                    // $subscriptedPlan = $update_product->subscriptionType_id;
                    // $plan = SubscriptionType::find($subscriptedPlan);
                    // $plan->update([
                    //     'count' => $plan->count + 1,
                    // ]);


                echo "<br >Transaction is successfully Completed";
                echo "<br >Email Sent ";
            }
        //    $userEmail = Auth::user()->email;
            // SentMailEvent::dispatch($user);
            $user = User::first()->email;
            // return $user;
          new   SentMailEvent($user);
            // SentMailEvent::dispatch("")

        } else if ($order_details->status == true || $order_details->status == 'Complete') {
            /*
             That means through IPN Order status already updated. Now you can just show the customer that transaction is completed. No need to udate database.
             */
            echo "Transaction is successfully Completed";
        } else {
            #That means something wrong happened. You can redirect customer to your product page.
            echo "Invalid Transaction";
        }
    }

    public function fail(Request $request)
    {
        $tran_id = $request->input('tran_id');

        $order_details = DB::table('subscriptions')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status')->first();

        if ($order_details->status == false) {

            echo "Transaction is Falied";
        } else if ($order_details->status == true || $order_details->status == 'Complete') {
            echo "Transaction is already Successful";
        } else {
            echo "Transaction is Invalid";
        }
    }

    public function cancel(Request $request)
    {
        $tran_id = $request->input('tran_id');

        $order_details = DB::table('subscriptions')
            ->where('transaction_id', $tran_id)
            ->select('transaction_id', 'status', 'currency', 'amount')->first();

        if ($order_details->status == false) {

            echo "Transaction is Cancel";
        } else if ($order_details->status == true || $order_details->status == 'Complete') {
            echo "Transaction is already Successful";
        } else {
            echo "Transaction is Invalid";
        }
    }

    public function ipn(Request $request)
    {
        #Received all the payement information from the gateway
        if ($request->input('tran_id')) #Check transation id is posted or not.
        {

            $tran_id = $request->input('tran_id');

            #Check order status in order tabel against the transaction id or order id.
            $order_details = DB::table('subscriptions')
                ->where('transaction_id', $tran_id)
                ->select('transaction_id', 'status', 'currency', 'amount')->first();

            if ($order_details->status == false) {
                $sslc = new SslCommerzNotification();
                $validation = $sslc->orderValidate($request->all(), $tran_id, $order_details->amount, $order_details->currency);
                if ($validation == TRUE) {
                    /*
                    That means IPN worked. Here you need to update order status
                    in order table as Processing or Complete.
                    Here you can also sent sms or email for successful transaction to customer
                    */
                    $update_product = DB::table('subscriptions')
                        ->where('transaction_id', $tran_id)
                        ->update(['status' => true]);

                    echo "Transaction is successfully Completed";
                }
            } else if ($order_details->status == true || $order_details->status == 'Complete') {

                #That means Order status already updated. No need to udate database.

                echo "Transaction is already successfully Completed";
            } else {
                #That means something wrong happened. You can redirect customer to your product page.

                echo "Invalid Transaction";
            }
        } else {
            echo "Invalid Data";
        }
    }
}
