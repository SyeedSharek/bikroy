<?php

namespace App\Http\Controllers;

use App\Http\Requests\Brand\BrandStoreRequest;
use App\Http\Requests\Brand\BrandUpdateRequest;
use App\Models\Brand;
use App\Models\Product;
use App\Response\AllResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{

    use AllResponse;

    // Brand Search
    public function brand_search(Request $request)
    {
        $search = $request->name;
        $brands = Brand::where(function ($query) use ($search) {
            $query->where('name', 'like', "%$search%");
        })
            ->orWhereHas('getCat', function ($query) use ($search) {
                $query->where('category_name', 'like', "%$search%");
            })
            ->orWhereHas('getSubCat', function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            })
            ->with('getCat', 'getSubCat')
            ->get();
        if (count($brands) > 0) {
            return $this->PostsResponse($brands, 200);
        } else {
            return $this->Response(false, 'Data Not Found', 404);
        }
    }


    // Get All Brand

    public function brand_all()
    {
        $allBrands = Brand::orderBy('name', 'ASC')->get();
        if ($allBrands->count() > 0) {
            return $this->PostsResponse($allBrands, 200);
        } else {
            return $this->Response(false, 'Brand not found', 404);
        }
    }
    // All Brand List Show
    public function brand_index()
    {
        if (auth('admin')->user()->hasPermissionTo('brand list')) {
            $brands = Brand::with('getCat', 'getSubCat')->paginate(10);
            if ($brands->count() > 0) {
                return $this->PostsResponse($brands, 200);
            } else {
                return $this->Response(false, 'No Record Here', 404);
            }
        } else {
            return $this->Response(false, 'Forbidden', 403);
        }
    }


    // Brand Store
    public function brand_store(BrandStoreRequest $request)
    {
        if (auth('admin')->user()->hasPermissionTo('create brand')) {
            $brand = Brand::create([
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'name' => $request->name,
            ]);
            if ($brand) {
                return $this->Response(true, 'Brand Successfully Saved', 201);
            } else {
                return $this->Response(false, 'Insert Fail', 404);
            }
        } else {
            return $this->Response(false, 'Forbidden', 403);
        }
    }
    // Brand Edit View
    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        if ($brand) {
            return response()->json([
                'brand' => $brand,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Data Not Found',
            ], 404);
        }
    }
    // Brand Update
    public function brand_update(BrandUpdateRequest $request, $id)
    {
        if (auth('admin')->user()->hasPermissionTo('edit brand')) {
            $brand = Brand::find($id);
            if ($brand) {
                $brand->subcategory_id = $request->subcategory_id;
                $brand->name = $request->name;
                $brand->update();
                return $this->Response(true, 'Brand Successfully Updated', 200);
            } else {
                return $this->Response(false, 'No Brand Found', 404);
            }
        } else {
            return $this->Response(false, 'Forbidden', 403);
        }
    }
    // Brand Delete
    public function brand_delete($id)
    {
        if (auth('admin')->user()->hasPermissionTo('delete brand')) {
            $brand = Brand::find($id);
            $product = Product::where('brand_id', $brand->id)->get();
            if ($product->count() > 0) {
                return $this->Response(false, 'Delete post under this brand', 400);
            }
            if ($brand) {
                $brand->delete();
                return $this->Response(true, 'Brand Deleted', 200);
            } else {
                return $this->Response(false, 'Brand Not Found', 404);
            }
        } else {
            return $this->Response(false, 'Forbidden', 403);
        }
    }
}
