<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\EmailVerifiedRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\User\UserForgetPasswordRequest;
use App\Mail\ForgotPasswordMail;
use App\Models\User;
use App\Notifications\RegisterNotification;
use App\Response\AllResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\User\UserRegisterRequest;
use App\Http\Requests\Auth\User\UserUpdateRequest;
use App\Http\Requests\Auth\User\UpdatePasswordRequest;
use App\Mail\VerificationMail;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use AllResponse;
    // Constructor
    public function __construct()
    {
        $this->middleware('jwt:api', ['except' => ['register', 'login', 'ForgetPassword', 'ResetPassword', 'updatePassword', 'auth', 'verified', 'EmailVerified']]);
    }
    // User Registration
    public function register(UserRegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'postal_code' => $request->postal_code,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country
        ])->syncRoles('user');

        if (isset($user)) {
            $user->remember_token = Str::random(30);
            $user->save();
            $url = env('FRONTEND_URL') . "/email-verification/?token=" . $user->remember_token . "&email=" . $user->email;
            Mail::to($user->email)->send(new VerificationMail($user, $url));
            // $user->notify(new RegisterNotification($user));
            return $this->Response(true, 'Check Your Email', 200);
        } else {
            return $this->Response(false, 'Failed to create user', 401);
        }
    }
    // Email verified
    public function EmailVerified(EmailVerifiedRequest $request)
    {
        $userverified = User::where('email', '=', $request->email)->where('remember_token', '=', $request->token)->first();
        if ($userverified) {
            User::where('id', $userverified->id)->update(['email_verified_at' => now()]);
            return $this->Response(true, 'Your email is verified', 200);
        }
        return $this->Response(false, 'User not found', 404);
    }
    // User Login function
    public function login(LoginRequest $request)
    {

        try {
            if (!JWTAuth::attempt($request->only(['email', 'password']))) {
                return $this->Response(false, "Email & Password do not match with our records", 401);
            }

            $credentials = $request->only('email', 'password');

            if ($token = auth('api')->attempt($credentials)) {
                $user = auth('api')->user();
                if (!$user->is_banned) {
                    if (User::find($user->id)->hasVerifiedEmail()) {
                        $tokenData = $this->respondWithToken($token);
                        $role = Role::find($user->roles[0]->id);

                        $userWithToken['jwt_token'] = $tokenData->original['access_token'];
                        return response()->json([
                            'status' => true,
                            'message' => 'User Login Successfully',
                            'user' => $userWithToken,
                            'role' => $role,
                        ], 200);
                    } else {
                        $user->remember_token = Str::random(30);
                        $user->save();
                        $url = env('FRONTEND_URL') . "/email-verification/?token=" . $user->remember_token . "&email=" . $user->email;
                        Mail::to($user->email)->send(new VerificationMail($user, $url));
                        return response()->json([
                            'status' => true,
                            'errors' => 'Verification link email',
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Your account is banned",
                    ], 403);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Get the authenticated user email
    public function me()
    {
        $user = auth('api')->user();

        if (Auth::guard('api')->user()) {
            return $this->SuccessResponseOnlyUser(true, $user, 200);
        } else {
            return $this->Response(false, 'Unauthorized', 400);
        }
    }
    public function profile()
    {
        $user = auth('api')->user();

        if (Auth::guard('api')->user()) {
            return $this->SuccessResponseOnlyUser(true, $user, 200);
        } else {
            return $this->Response(false, 'Unauthorized', 400);
        }
    }
    // Login user profile update
    public function Update(UserUpdateRequest $request)
    {
        $user = auth('api')->user();
        if ($request->has('old_password') && !Hash::check($request->old_password, $user->password)) {
            return $this->Response(false, 'Old Password is incorrect', 400);
        }
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->update([
            'name' => $request->name,
            // 'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'postal_code' => $request->postal_code,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
        ]);
        return $this->SuccessResponseWithUser($user, 'Profile Update successfully', 201);
    }

    // User Logout
    public function userlogout()
    {
        auth('api')->logout();
        return $this->Response(true, 'logout successfully', 200);
    }

    //  User delete
    // public function Delete()
    // {
    //     $user = User::find(auth('api')->user()->id);
    //     $product = Product::where('user_id', $user->id)->get()->count();
    //     // return $product;
    //     if ($product < 0) {
    //         $user->delete();
    //         return $this->Response(true, 'User deleted successfully', 200);
    //     } else {
    //         return $this->Response(false, 'Delete your post before account delete', 400);
    //     }
    // }

    // User Forget Password
    public function ForgetPassword(UserForgetPasswordRequest $request)
    {
        $email = $request->only('email');
        $user = User::where('email', '=', $email)->first();

        if (isset($user)) {
            $user->remember_token = Str::random(30);
            $user->save();
            $url = env('FRONTEND_URL') . "/password-reset/?token=" . $user->remember_token . "&email=" . $user->email;
            Mail::to($user->email)->send(new ForgotPasswordMail($user, $url));

            return $this->Response(true, 'Please Check Your Email Address', 200);
        } else {
            return $this->Response(false, 'Your email doesn\'t match our record', 404);
        }
    }
    // User Reset Password
    public function ResetPassword($token)
    {
        $user = User::where('remember_token', '=', $token)->first();

        if (isset($user)) {
            return $this->SuccessResponseWithUser($user, 'Enter new password', 200);
        } else {
            return $this->Response(false, 'User not found', 404);
        }
    }
    // Update user password using a reset token.
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $token = $request->token;
        $user = User::where('remember_token', '=', $token)->first();
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        return $this->Response(true, 'Password updated successfully', 200);
    }
    // Private methods respons with token
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 1440,
        ]);
    }
}
