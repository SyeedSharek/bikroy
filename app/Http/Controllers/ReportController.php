<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Response\AllResponse;
use App\Models\Product;

class ReportController extends Controller
{
    use AllResponse;

    // Post Reports of Daily, Weekly, Monthly, Yearly for Admin
    public function timePosts(Request $request){
        // Check if the authenticated admin user has the "post list" permission
        if (auth('admin')->user()->hasPermissionTo('post list')) {
            // Fetch all posts with related data using Eloquent relationships
            $posts = Product::with(['get_user', 'get_category', 'get_subcategory', 'get_brand', 'get_location', 'get_area']);
    
            // Filter Report of Daily Posts
            if ($request->name == 1) {
                $filterPosts = $posts->whereDate('created_at', now()->toDateString());
            }
            // Filter Report of Weekly Posts
            elseif ($request->name == 2) {
                $filterPosts = $posts->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            }
            // Filter Report of Monthly Posts
            elseif ($request->name == 3) {
                $filterPosts = $posts->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month);
            }
            // Filter Report of Yearly Posts
            elseif ($request->name == 4) {
                $filterPosts = $posts->whereYear('created_at', now()->year);
            } else {
                // Return an unauthorized access response
                return $this->Response(false, "Posts not found.", 404);
            }
    
            // Apply pagination
            $filterPosts = $filterPosts->paginate(10);
    
            // Check if posts were found
            if ($filterPosts->count() > 0) {
                // Return a successful response with the posts
                $filterPosts = $filterPosts->appends(['name' => $request->name]);
                return $this->PostsResponse($filterPosts, 200);
            }
    
            // Return a response indicating that no posts were found
            return $this->Response(false, "Posts not found.", 404);
        } else {
            // Return an unauthorized access response
            return $this->Response(false, "Unauthorized & Forbidden Access", 403);
        }
    }
    


    // Sold Reports of Daily, Weekly, Monthly, Yearly for Admin
    public function timeReport(Request $request){

        // Check if the authenticated admin user has the "post list" permission
        if (auth('admin')->user()->hasPermissionTo('report list')) {

            // Fetch all posts with related data using Eloquent relationships
            $posts = Product::with(['get_user', 'get_category', 'get_subcategory', 'get_brand', 'get_location', 'get_area'])->where('is_sold',true);

            // Filter Report of Daily Posts
            if($request->name == 1){

                $filterPosts = $posts->whereDate('created_at', now()->toDateString())
                                ->paginate(10);

            // Filter Report of Weekly Posts
            }else if($request->name == 2){

                $filterPosts = $posts->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                                ->paginate(10);

            // Filter Report of Monthly Posts
            }else if($request->name == 3){

                $filterPosts = $posts->whereYear('created_at', now()->year)
                                ->whereMonth('created_at', now()->month)
                                ->paginate(10);

            // Filter Report of Yearly Posts
            }else if($request->name == 4){

                $filterPosts = $posts->whereYear('created_at', now()->year)
                                ->paginate(10);

            }else {

                // Return an unauthorized access response
                return $this->Response(false, "Posts not found.", 404);

            }

            // Check if posts were found
            if (count($filterPosts) > 0) {
                // Return a successful response with the posts
                $filterPosts = $filterPosts->appends(['name' => $request->name]);
                return $this->PostsResponse($filterPosts, 200);
            }

            // Return a response indicating that no posts were found
            return $this->Response(false, "Posts not found.", 404);

        } else {
            // Return an unauthorized access response
            return $this->Response(false, "Unauthorized & Forbidden Access", 403);
        }
    }


    // Report of Sold Product for Admin
    public function report()
    {
        // Check if the authenticated admin user has the "report list" permission
        if (auth('admin')->user()->hasPermissionTo('report list')) {

            // Fetch sold posts with related data using Eloquent relationships
            $posts = Product::with(['get_user', 'get_category', 'get_subcategory', 'get_brand', 'get_location', 'get_area'])->where('is_sold', 1)->paginate(10);

            // Check if sold posts were found
            if (count($posts) > 0) {
                // Return a successful response with the sold posts
                return $this->PostsResponse($posts, 200);
            }

            // Return a response indicating that no sold posts were found
            return $this->Response(false, "Posts not found.", 404);
        } else {
            // Return an unauthorized access response
            return $this->Response(false, "Unauthorized & Forbidden Access", 403);
        }
    }

}
