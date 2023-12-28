<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\Admin\AdminLoginRequest;
use App\Http\Requests\Auth\Admin\AdminProfileUpdateRequest;
use App\Http\Requests\Auth\Admin\AdminRegisterRequest;
use App\Http\Requests\Auth\Admin\AdminUserUpdateRequest;
use App\Models\Admin;
use App\Models\User;
use App\Response\AllResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Intervention\Image\Facades\Image;

class AdminController extends Controller
{
    use AllResponse;
    // Initialize the middleware for the controller
    public function __construct()
    {
        $this->middleware('jwt:admin', ['except' => ['Login', 'Register']]);
    }
    // Admin Registration
    public function Register(AdminRegisterRequest $request)
    {
        if (auth('admin')->user()->hasPermissionTo('create adminuser')) {
            $images = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $images->getClientOriginalExtension();
            Image::make($images)->resize(300, 300)->save('uploads/profile/' . $name_gen);
            $save_url = 'uploads/profile/' . $name_gen;
            $user = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'image' => $save_url,
                'password' => Hash::make($request->password),
            ]);
            $user->syncRoles($request->roles);
            return $this->SuccessResponseWithUser($user, 'Admin Created Successfully', 201);
        } else {
            return $this->Response(false, "Unauthorized & Forbidden Access", 403);
        }
    }

    // Admin Login
    public function Login(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $authAdmin = Auth::guard('admin')->user();
            $token = auth('admin')->login($authAdmin);
            $admin = Admin::with('roles')->find($authAdmin->id);
            // $role = Role::with('permissions')->find($admin->roles[0]->id);

            return response()->json([
                'status' => true,
                'message' => 'Admin Login successfully',
                'access_token' => $token,
                'user' => $admin,
                'token_type' => 'bearer',
                // 'role' => $role,
                'expires_in' => auth('admin')->factory()->getTTL(),
            ], 201);
        }
        return $this->Response(false, "User Not Found", 401);
         
    }

    // Admin Profile
    public function me()
    {
        $admin = auth('admin')->user()->email;
        $role = Admin::find(auth('admin')->user()->id)->load('roles')->pluck('name')->first();

        return $this->SuccessResponseWithRoles($admin, $role, 200);
    }
    // Loggin Admin data
    public function profile()
    {
        $admin = auth('admin')->user();
        $role = Role::with('permissions')->find($admin->roles[0]->id);
        $user = auth('admin')->user();
        return $this->SuccessResponseWithRoles($user, $role, 200);
    }
    // Admin profile update
    public function Update(AdminProfileUpdateRequest $request)
    {
        $user = auth('admin')->user();
        if ($request->hasFile('image')) {
            if (file_exists($user->image)) {
                unlink('uploads/profile/' . $user->image);
            }
            $images = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $images->getClientOriginalExtension();
            Image::make($images)->resize(300, 300)->save('uploads/profile/' . $name_gen);
            $save_url = 'uploads/profile/' . $name_gen;
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'image' => $save_url,
            ]);
        } else {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);
        }
        if ($request->has('old_password') && !Hash::check($request->old_password, $user->password)) {
            return $this->Response(false, 'Old Password is incorrect', 400);
        }
        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
            return $this->Response(true, 'Profile & Password updated Successfully', 201);
        }
        return $this->Response(true, 'Profile Updated Successfully', 201);
        // return $this->Response(true, 'Profile Updated Successfully', 201);
    }


    // Admin Logout
    public function Adminlogout()
    {
        auth('admin')->logout(); // Use auth() to log the user out
        return $this->Response(true, 'logout Successfully', 201);
    }

    // Admin  delete
    public function AdminDelete($id)
    {
        $admin = Admin::find($id);
        $superadmin = $admin->is_superadmin;

        if (auth('admin')->user()->hasPermissionTo('delete adminuser')) {
            if ($superadmin == true) {
                return $this->Response(false, "Superadmin can't be deleted", 401);
            } else {
                // if Other admin section
                if (isset($admin)) {
                    $admin->delete();
                    return $this->Response(true, 'User deleted successfully', 200);
                } else {
                    return $this->Response(false, 'User failed to delete', 401);
                }
            }
        } else {
            return $this->Response(false, 'Forbidden', 403);
        }
    }

    // User Delete
    public function UserDelete($id)
    {
        if (auth('admin')->user()->hasPermissionTo('delete user')) {
            if (auth('admin')->user() !== null) {
                User::find($id)->delete();
                return $this->Response(true, 'User delete successfully', 200);
            } else {
                return $this->Response(false, 'User failed to delete', 401);
            }
        } else {
            return $this->Response(false, 'Forbidden', 403);
        }
    }

    // All Admin List
    public function AdminUser()
    {
        if (auth('admin')->user()->hasPermissionTo('adminuser list')) {
            if (auth('admin')->user() !== null) {
                $adminuser = Admin::with('roles')->paginate(10);
                return $this->SuccessResponseOnlyUser(true, $adminuser, 200);
            } else {
                return $this->Response(false, 'User Unauthorized', 401);
            }
        } else {
            return $this->Response(false, 'Forbidden', 403);
        }
    }


    public function AdminEdit()
    {
        $Admin = auth('admin')->user()->id;
        if (isset($Admin)) {
            return $this->SuccessResponseOnlyUser(true, $Admin, 200);
        }
        return $this->Response(false, 'User not found', 404);
    }
    public function AdminUserUpdate(AdminUserUpdateRequest $request, $id)
    {
        if (auth('admin')->user()->hasPermissionTo('user list')) {
            $user = Admin::find($id);
            if (!$user) {
                return $this->Response(false, 'User not found', 404);
            }
            if ($request->has('old_password') && !Hash::check($request->old_password, $user->password)) {
                return $this->Response(false, 'Old Password is incorrect', 400);
            }
            $validatedData = $request->validated();
            $user->update($validatedData);

            // Update password if provided
            if ($request->has('password')) {
                $user->update(['password' => Hash::make($request->input('password'))]);
            }

            // Sync user roles
            $user->syncRoles($request->input('roles'));
            // $user->with('roles')->get();
            // $user = Admin::with('roles')->find($id);
            // $AdminUserRole = Admin::with('roles')->latest()->get();
            // Return success response with the updated user
            return $this->SuccessResponseWithUser($user, 'User Update Successfully', 201);
        }

        // Return error response if the authenticated admin user does not have the required permission
        return $this->Response(false, 'Unauthorize', 403);
    }

    public function AdminUserSearch(Request $request)
    {
        if (auth('admin')->user()) {
            $search = $request->name;
            $Users = Admin::with('roles')->where('name', 'like', "%$search%")->orWhere('email', 'like', "%$search%")->orWhere('phone', 'like', "%$search%")
                ->orWhereHas('roles', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                })->with('roles')->get();
            if (count($Users) < 1) {
                return $this->Response(false, 'user not found', 404);
            }
            return $this->SuccessResponseOnlyUser(true, $Users, 201);
        }
        return $this->Response(false, 'Unauthorize', 400);
    }
    // All Customer User
    public function CustomerUser()
    {
        if (auth('admin')->user()->hasPermissionTo('user list')) {
            if (auth('admin')->user() !== null) {
                $customerUser = User::where('status', 1)->latest()->paginate(10);
                return $this->PostsResponse($customerUser, 200);
            } else {
                return $this->Response(false, 'User Unauthorized', 401);
            }
        } else {
            return $this->Response(false, 'Forbidden', 403);
        }
    }
    public function BannedUnbannedCustomerUser(Request $request)
    {

        if (auth('admin')->user()->hasPermissionTo('user list')) {
            $get = $request->id;

            if ($get == 1) {
                $customerUser = User::where('is_banned', true)->latest()->paginate(10);
                $customerUser->appends(['id' => $request->id]);
                return $this->PostsResponse($customerUser, 200);
            } elseif ($get == 0) {
                $customerUser = User::where('is_banned', false)->latest()->paginate(10);
                $customerUser->appends(['id' => $request->id]);
                return $this->PostsResponse($customerUser, 200);
            }
        } else {
            return $this->Response(false, 'Forbidden', 403);
        }
    }
    public function CustomerUserBanned($id)
    {
        if (auth('admin')->user()) {
            $user = User::find($id);
            if ($user !== null) {
                $user->update([
                    'is_banned' => true,
                ]);
                return $this->Response(true, 'User banned', 200);
            }
            return $this->Response(false, 'something went wrong', 400);
        }
        return $this->Response(false, 'Unauthorize', 400);
    }
    public function CustomerUserUnbanned($id)
    {
        if (auth('admin')->user()) {
            $user = User::find($id);
            if ($user !== null) {
                $user->update([
                    'is_banned' => false,
                ]);
                return $this->Response(true, 'User Unbanned successfully', 200);
            }
            return $this->Response(false, 'something went wrong', 400);
        }
        return $this->Response(false, 'Unauthorize', 400);
    }
    public function CustomerUserSearch(Request $request)
    {
        if (auth('admin')->user()) {
            $search = $request->name;
            $Users = User::where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('phone', 'like', "%$search%")
                ->orWhere('address', 'like', "%$search%")
                ->orWhere('postal_code', 'like', "%$search%")
                ->orWhere('city', 'like', "%$search%")
                ->orWhere('state', 'like', "%$search%")
                ->orWhere('country', 'like', "%$search%")
                ->get();
            if (count($Users) < 1) {
                return $this->Response(false, 'user not found', 404);
            }
            return $this->SuccessResponseOnlyUser(true, $Users, 201);
        }
    }
}
