<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CredentialController extends Controller
{
    public function updateEnvFile($key, $value)
    {
        $envFilePath = base_path('.env');

        if (File::exists($envFilePath)) {
            // Read the current content of the .env file
            $currentEnvFile = file_get_contents($envFilePath);

            // Check if the key already exists in the .env file
            if (Str::contains($currentEnvFile, "$key=")) {
                // If the key exists, replace its value
                $currentEnvFile = preg_replace("/$key=.*/", "$key=$value", $currentEnvFile);
            } else {
                // If the key doesn't exist, add a new line with the key and value
                $currentEnvFile .= "\n$key=$value";
            }

            // Write the updated content back to the .env file
            file_put_contents($envFilePath, $currentEnvFile);

            // Clear the config cache to apply the changes
            Artisan::call('config:clear');
            
        }

        return response()->json([
            'status' => false,
            'message' => 'Could not find .env file'
        ], 404);
    }

    public function paypal_credential(Request $request)
    {
        if (auth('admin')->user() !== null) {
            $validator = Validator::make(
                $request->all(),
                [
                    'PAYPAL_CLIENT_ID' => 'required',
                    'PAYPAL_SECRET'    => 'required',
                    'PAYPAL_MODE'      => 'required',
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation Error!',
                    'errors'  => $validator->errors(),

                ], 400);
            } else {
                $paypalClientId = $request->PAYPAL_CLIENT_ID;
                $paypalSecret = $request->PAYPAL_SECRET;
                $paypalMode = $request->PAYPAL_MODE;

                $this->updateEnvFile('PAYPAL_CLIENT_ID', $paypalClientId);
                $this->updateEnvFile('PAYPAL_SECRET', $paypalSecret);
                $this->updateEnvFile('PAYPAL_MODE', $paypalMode);

                return response()->json([
                    'status' => true,
                    'message' => 'Paypal Credential Successfully Updated',
                ], 200);
            }
        }
    }
    public function stripe_credential(Request $request)
    {
        if (auth('admin')->user() !== null) {
            $validator = Validator::make(
                $request->all(),
                [
                    'STRIPE_KEY' => 'required',
                    'STRIPE_SECRET'    => 'required',
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation Error!',
                    'errors'  => $validator->errors(),

                ], 400);
            } else {
                $StripeClientId = $request->STRIPE_KEY;
                $StripeSecret = $request->STRIPE_SECRET;

                $this->updateEnvFile('STRIPE_KEY', $StripeClientId);
                $this->updateEnvFile('STRIPE_SECRET', $StripeSecret);

                return response()->json([
                    'status' => true,
                    'message' => 'Stripe Credential Successfully Updated',
                ], 200);
            }
        }
    }
    public function ssl_credential(Request $request){

        if (auth('admin')->user() !== null) {
            $validator = Validator::make(
                $request->all(),
                [
                    'SSLCZ_STORE_ID' => 'required',
                    'SSLCZ_STORE_PASSWORD'    => 'required',
                    'SSLCZ_TESTMODE' => 'required',
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation Error!',
                    'errors'  => $validator->errors(),

                ], 400);
            } else {
                $sslStoreId = $request->SSLCZ_STORE_ID;
                $sslStorePassword = $request->SSLCZ_STORE_PASSWORD;
                $sslTestMode = $request->SSLCZ_TESTMODE;

                $this->updateEnvFile('SSLCZ_STORE_ID', $sslStoreId);
                $this->updateEnvFile('SSLCZ_STORE_PASSWORD', $sslStorePassword);
                $this->updateEnvFile('SSLCZ_TESTMODE', $sslTestMode);

                return response()->json([
                    'status' => true,
                    'message' => 'SslCommerz Credential Successfully Updated',
                ], 200);
            }
        }
    }
    public function mail_credential(Request $request){
        if (auth('admin')->user() !== null) {
            $validator = Validator::make(
                $request->all(),
                [
                    'MAIL_MAILER' => 'required',
                    'MAIL_HOST'    => 'required',
                    'MAIL_PORT' => 'required',
                    'MAIL_USERNAME' => 'required',
                    'MAIL_PASSWORD'    => 'required',
                    'MAIL_ENCRYPTION' => 'required',
                    'MAIL_FROM_ADDRESS' => 'required',
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation Error!',
                    'errors'  => $validator->errors(),
                ], 400);
            } else {

                $MAIL_MAILER =str_replace(' ','', $request->MAIL_MAILER);
                $MAIL_HOST = str_replace(' ','',$request->MAIL_HOST);
                $MAIL_PORT =str_replace(' ','' ,$request->MAIL_PORT);
                $MAIL_USERNAME =str_replace(' ','', $request->MAIL_USERNAME);
                $MAIL_PASSWORD = str_replace(' ','',$request->MAIL_PASSWORD) ;
                $MAIL_ENCRYPTION = str_replace(' ','', $request->MAIL_ENCRYPTION);
                $MAIL_FROM_ADDRESS = $request->MAIL_FROM_ADDRESS;
                $modify = strtolower(str_replace($MAIL_FROM_ADDRESS,"'$MAIL_FROM_ADDRESS'",$MAIL_FROM_ADDRESS));
                $this->updateEnvFile('MAIL_MAILER', $MAIL_MAILER);
                $this->updateEnvFile('MAIL_HOST', $MAIL_HOST);
                $this->updateEnvFile('MAIL_PORT', $MAIL_PORT);
                $this->updateEnvFile('MAIL_USERNAME', $MAIL_USERNAME);
                $this->updateEnvFile('MAIL_PASSWORD', $MAIL_PASSWORD);
                $this->updateEnvFile('MAIL_ENCRYPTION', $MAIL_ENCRYPTION);
                $this->updateEnvFile('MAIL_FROM_ADDRESS', $modify);

                return response()->json([
                    'status' => true,
                    'message' => 'Mail Credential Successfully Updated',
                ], 200);
            }
        }
    }
    public function stripePublishKey(){
        $key = config('services.stripe.key');
        return response()->json([
            'status' => true,
            'data' => $key
        ],200);
    }
}
