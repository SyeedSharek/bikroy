<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\ProductRequest;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Response\AllResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    use AllResponse;
    // Search of Product
    public function product_search(Request $request)
    {
        $product = Product::search($request->name)->get();
        if ($product->count() > 0) {
            return $this->PostsResponse($product, 200);
        }
        return $this->PostsResponse($product, 200);
    }


    // Filter of Product
    public function product_filter(Request $request)
    {
        // Get the search term from the request
        $category = $request->category;
        $subcategory = $request->subcategory;
        $brand = $request->brand;
        $location = $request->location;
        $area = $request->area;
        $minPrice = $request->minPrice;
        $maxPrice = $request->maxPrice;

        $posts = Product::where('status', true)->where('is_sold', false);

        // Filter for products based on related entities
        if ($category != null) {
            $posts = $posts->whereHas('get_category', function ($query) use ($category) {
                $query->where('category_name', 'like', "%$category%");
            });

            if ($subcategory != null) {
                $posts = $posts->whereHas('get_subcategory', function ($query) use ($subcategory) {
                    $query->where('name', 'like', "%$subcategory%");
                });

                if ($brand != null) {
                    $posts = $posts->whereHas('get_brand', function ($query) use ($brand) {
                        $query->where('name', 'like', "%$brand%");
                    });
                }
            }
        }

        if ($location != null) {
            $posts = $posts->whereHas('get_location', function ($query) use ($location) {
                $query->where('name', 'like', "%$location%");
            });

            if ($area != null) {
                $posts = $posts->whereHas('get_area', function ($query) use ($area) {
                    $query->where('name', 'like', "%$area%");
                });
            }
        }

        // Price range filter
        if ($minPrice != null) {
            $posts = $posts->where('price', '>=', $minPrice);
        }

        if ($maxPrice != null) {
            $posts = $posts->where('price', '<=', $maxPrice);
        }

        $posts = $posts->with(['get_user', 'get_category', 'get_subcategory', 'get_brand', 'get_location', 'get_area'])
            ->paginate(12);

        // Check if matching posts were found
        if ($posts->count() > 0) {
            // Return a successful response with the matching posts
            return $this->PostsResponse($posts, 200);
        } else {
            // Return a response indicating that no matching posts were found
            return $this->Response(false, "No matching posts found.", 404);
        }
    }
    // filter product with type latest & popularity
    public function productFilter($type)
    {
        if ($type === 'latest') {
            $product = Product::where('is_sold', 0)->where('status', 1)->latest()->take(12)->get();
            return $this->PostsResponse($product, 200);
        } elseif ($type === 'popular') {
            $product = Product::where('is_sold', 0)->where('status', 1)->popularAllTime()->take(12)->get();
            return $this->PostsResponse($product, 200);
        }
    }

    // All Posts for Admin
    public function index()
    {
        if (auth('admin')->user()->hasPermissionTo('post list')) {
            $posts = Product::where('is_sold', 0)->with(['get_user', 'get_category', 'get_subcategory', 'get_brand', 'get_location', 'get_area'])->paginate(10);
            if (count($posts) > 0) {
                return $this->PostsResponse($posts, 200);
            }
            return $this->Response(false, "Posts not found.", 404);
        } else {
            return $this->Response(false, "Unauthorized & Forbidden Access", 403);
        }
    }


    // Active products Which are not sold for Guest Users
    public function products(Request $request)
    {
        // if user membership is cancelled
        $allUsers = User::all();
        foreach ($allUsers as $user) {
            $this->membership($user->subscription_id);
        }
        // for user unboost
        $this->updateExpiredBoostings(Carbon::now());


        $skip = $request->skip ? $request->skip : 0;
        $boostedSkip = $request->boostedSkip ? $request->boostedSkip : 0;
        $take = $request->take ? $request->take : 30;
        $searchQuery = $request->searchQuery ? $request->searchQuery : "";
        // all products
        $products = [];
        // get boosted prodcuts
        $boostedProducts = Product::with('get_category')->where('status', 1)
            ->where('is_sold', 0)->where('is_boost', 1)->latest();

        if ($request->orderBy && $request->orderBy == "popularity") {
            $boostedProducts = $boostedProducts->popularAllTime();
        }
        // skipping old taken product
        $boostedProducts = $boostedProducts->skip($boostedSkip)->take(2)->get();


        // get normal product
        $not_boosted = Product::with('get_category')->where('status', 1)
            ->where('is_sold', 0)->where('is_boost', 0);
        //  filter Order by
        if ($request->orderBy && $request->orderBy == "popularity") {
            $not_boosted = $not_boosted->popularAllTime();
        }
        if ($searchQuery) {
            $not_boosted = $not_boosted->where('title', 'like', "%$searchQuery%");
        }
        // skipping old taken product
        $not_boosted = $not_boosted->skip($skip)->take($take)->get();


        // $products = [...$boostedProducts];
        // $products = [...$not_boosted];
        $products = [...$boostedProducts, ...$not_boosted];
        // getting max price
        $maxPrice = $boostedProducts->max('price');

        return response()->json(['data' => $products, 'max_price' => $maxPrice], 200);
    }

    // Posts that User uploaded
    public function userProduct()
    {
        $user = auth('api')->user();
        $posts = Product::with(['get_category', 'get_subcategory', 'get_brand', 'get_location', 'get_area'])
            ->where('user_id', $user->id)
            ->where('is_sold', false)
            ->popularAllTime()
            ->paginate(10);
        if (count($posts) > 0) {
            return $this->PostsResponse($posts, 200);
        }
    }
    // user sold out product
    public function userSoldout()
    {
        $user = auth('api')->user();
        $posts = Product::with(['get_category', 'get_subcategory', 'get_brand', 'get_location', 'get_area'])
            ->where('user_id', $user->id)
            ->where('is_sold', true)
            ->paginate(10);
        return $this->PostsResponse($posts, 200);
    }
    // User upload Post
    public function store(ProductRequest $request)
    {
        $setting = Setting::first();
        $user = auth()->user();
        $products = Product::where('user_id', $user->id);
        if ($request->is_new) {
            $new_limit = $setting->new_limit;
            $new_products_count = $this->countProduct($products, 1);
            if ($new_products_count >= $new_limit) {
                return $this->Response(false, "You can't upload new product today", 400);
            }
        } else {
            $old_limit = $setting->old_limit;
            $old_products_count = $this->countProduct($products, 0);
            if ($old_products_count >= $old_limit) {
                return $this->Response(false, "You can't upload old product today", 400);
            }
        }
        $imagesPaths = $this->uploadImage($request->file('images'));

        // Create a new product
        $products = Product::create([
            // Product attributes
            'title' => $request->title,
            'slug' => strtolower(str_replace(".", "", str_replace(" ", "_", $request->title))),
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'location_id' => $request->location_id,
            'area_id' => $request->area_id,
            'description' => $request->description,
            'price' => $request->price,
            'user_id' => $user->id,
            'is_new' => $request->is_new,
            'is_boost' => 0,
            'images' => json_encode($imagesPaths),
        ]);
        return $this->Response(true, "Post added successfully.", 201);
    }


    // Date,Post Limitation Cheack

    public function countProduct($products, $status): int
    {

        return $products->where('is_new', $status)->where('created_at', '>=', Carbon::now()->startOfDay())->count();
    }
    // Post Details
    public function show($id)
    {
        // Fetch the product details with related data using Eloquent relationships
        $post = Product::with(['get_user', 'get_category', 'get_subcategory', 'get_brand', 'get_location', 'get_area', 'get_user'])->find($id);

        // Check if the product was found
        if ($post != null) {
            // Track a visit for the product
            $post->visit()->withIp();

            // Return a JSON response with the product details
            return $this->PostsResponse($post, 200);
        }

        // Return a response indicating that the product was not found
        return $this->PostsResponse(false, 200);
    }


    // Edit Post page for User
    public function edit($id)
    {
        // Fetch the product details by ID
        $post = Product::find($id);

        // Check if the product was found
        if ($post != null) {
            // Return a JSON response with the product details
            return $this->PostsResponse($post, 200);
        }

        // Return a response indicating that the product was not found
        return $this->Response(false, "Post not found.", 404);
    }


    // User Update Post
    public function update(ProductRequest $request, $id)
    {
        // Fetch the product by ID
        $product = Product::find($id);

        // Get the currently authenticated user
        $user = auth()->user();

        // Check if images are present in the request
        if ($request->hasFile('images')) {
            // If images are present, unlink the old images and upload the new ones
            $this->unLinkImage($product->images);
            $imagesPaths = $this->uploadImage($request->file('images'));
        } else {
            // If no new images are provided, use the existing image paths
            $imagesPaths = json_decode($product->images);
        }

        // Prepare data for updating the product
        $requestData = [
            // Product attributes
            'title' => $request->title,
            'slug' => strtolower(str_replace(' ', '_', $request->title)),
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'location_id' => $request->location_id,
            'area_id' => $request->area_id,
            'description' => $request->description,
            'price' => $request->price,
            'user_id' => $user->id,
            'images' => json_encode($imagesPaths),
        ];

        // Update the product
        $product->update($requestData);

        // Check if the product was updated successfully
        if ($product) {
            return $this->Response(true, "Post updated successfully.", 200);
        }

        // Return a response indicating that the product was not updated
        return $this->Response(false, "Post not updated.", 400);
    }


    // Post Delete by User or Admin
    public function destroy($id)
    {
        // Fetch the product by ID
        $product = Product::find($id);

        // Check if the product was found
        if ($product != null) {
            // Unlink associated images
            $this->unLinkImage($product->images);

            // Delete the product
            $product->delete();

            // Return a response indicating that the product was deleted successfully
            return $this->Response(true, "Post deleted successfully.", 200);
        }

        // Return a response indicating that the product was not found
        return $this->Response(false, "Post not found.", 404);
    }


    // For uploading Image
    public function uploadImage($images)
    {
        $imagesPaths = [];
        $uploadPath = 'uploads/products/';
        if (!File::isDirectory($uploadPath)) {
            File::makeDirectory($uploadPath, 0777, true, true);
        }
        foreach ($images as $image) {
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(640, 360)->save($uploadPath . $imageName);
            $imagesPaths[] = "uploads/products/" . $imageName;
        }
        return $imagesPaths;
    }

    // For Unlink Image
    public function unLinkImage($jsonImages)
    {

        foreach ($jsonImages as $image) {

            if (file_exists($image)) {

                unlink('uploads/products/' . $image);
            }
        }
    }


    // Button Action for User if they have sold their product
    public function sold($id)
    {
        // Update the specified product's 'is_sold' attribute to 1
        Product::find($id)->update(['is_sold' => 1]);

        // Return a response indicating that the product has been sold
        return $this->Response(true, "Product has been sold.", 200);
    }


    // Button Action for Admin if they Active any product
    public function Active($id)
    {
        Product::find($id)->update(['status' => 1]);
        return $this->Response(true, "Post has been activated.", 200);
    }
    // Button Action for Admin if they Inactive any product
    public function InActive($id)
    {
        Product::find($id)->update(['status' => 0]);
        return $this->Response(true, "Post has been inactivated.", 200);
    }
}
