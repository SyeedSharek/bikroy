<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\CategoryStoreRequest;
use App\Http\Requests\Category\CategoryUpdateRequest;
use App\Models\Category;
use App\Models\Product;
use App\Response\AllResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    use AllResponse;


    // Category Search
    public function category_search(Request $request)
    {
        $search = $request->category_name;
        $categories = Category::where(function ($query) use ($search) {
            $query->where('category_name', 'like', "%$search%");
        })->get();
        if (count($categories) > 0) {
            return $this->PostsResponse($categories, 200);
        } else {
            return $this->Response(false, "Category Not Found", 404);
        }
    }
    // all categories list for all users
    public function category_all()
    {
        $allCategory = Category::orderBy('category_name')->with('getSubCategory.getBrand')->get();
        if ($allCategory->count() > 0) {
            return $this->PostsResponse($allCategory, 200);
        } else {
            return $this->Response(false, "All location doesn\'t exist", 404);
        }
    }
    // all category list for admin only
    public function category_index()
    {
        if (auth('admin')->user()->hasPermissionTo('category list')) {
            $categories = Category::latest()->paginate(10);
            if ($categories->count() > 0) {
                return $this->PostsResponse($categories, 200);
            } else {
                return $this->Response(false, 'No Record Here', 404);
            }
        } else {
            return $this->Response(false, "Forbidden", 403);
        }
    }
    // Store Category
    public function category_store(CategoryStoreRequest $request)
    {
        if (auth('admin')->user()->hasPermissionTo('create category')) {
            $category = new Category;
            $category->category_name = $request->category_name;
            $category->category_description = $request->category_description;
            $uploadPath = 'uploads/categories/';
            if ($request->hasfile('category_image')) {
                if (!File::isDirectory($uploadPath)) {
                    File::makeDirectory($uploadPath, 0777, true, true);
                }
                $file = $request->file('category_image');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                Image::make($file)->resize(300, 300)->save($uploadPath . $filename);
                $category->category_image = $uploadPath . $filename;
            }
            $category->save();
            return $this->Response(true, "Category Successfully Saved", 201);
        } else {
            return $this->Response(false, "Forbidden", 403);
        }
    }
    // Edit Category
    public function category_edit($id)
    {
        $categories = Category::find($id);
        if ($categories) {
            return response()->json([
                'categories' => $categories,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Category Not Found',
            ], 404);
        }
    }
    // Category Update
    public function category_update(CategoryUpdateRequest $request, $id)
    {
        if (auth('admin')->user()->hasPermissionTo('edit category')) {
            $category = Category::find($id);
            if (!$category) {
                return $this->Response(false, "Category Not Found", 404);
            }
            $category->category_name = $request->category_name;
            $category->category_description = $request->category_description;
            if ($request->hasFile('category_image')) {
                if (file_exists($category->category_image)) {
                    unlink($category->category_image);
                }
                $file = $request->file('category_image');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                Image::make($file)->resize(300, 300)->save('uploads/categories/' . $filename);
                $category->category_image = 'uploads/categories/' . $filename;
            }
            $category->update();
            return $this->Response(true, "Category updated successfully", 201);
        } else {
            return $this->Response(false, "Forbidden", 403);
        }
    }
    // Category Delete
    public function category_delete($id)
    {
        if (auth('admin')->user()->hasPermissionTo('delete category')) {
            $category = Category::find($id);
            $product = Product::where('category_id', $category->id)->get();
            if ($product->count() > 0) {
                return $this->Response(false, 'Delete post under this category', 400);
            }
            if (isset($category)) {
                if (file_exists($category->category_image)) {
                    unlink($category->category_image);
                }
                $category->delete();
                return $this->Response(true, "Category Delete successfully", 201);
            }
            return $this->Response(false, "Category Not Found", 404);
        } else {
            return $this->Response(false, "Forbidden", 403);
        }
    }
}
