<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubCategory\SubcategoryStoreRequest;
use App\Models\Product;
use App\Models\SubCategory;
use App\Response\AllResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SubCategoryController extends Controller
{
    use AllResponse;

    // Sub Category Search
    public function sub_category_search(Request $request)
    {
        // Get the search term from the request
        $search = $request->name;

        // Search for subcategories where the name is similar to the search term
        // or where the associated category's name is similar to the search term
        $subcategories = SubCategory::where('name', 'like', "%$search%")
            ->orWhereHas('getCategory', function ($query) use ($search) {
                $query->where('category_name', 'like', "%$search%");
            })->with('getCategory')->get();

        // Check if any matching subcategories were found
        if (count($subcategories) > 0) {
            // Return a JSON response with the found subcategories
            return $this->PostsResponse($subcategories, 200);
        } else {
            // Return a JSON response indicating that no data was found
            return $this->Response(false, 'Data Not Found', 404);
        }
    }


    // Get All Sub Category

    public function subcategory_all()
    {
        $allSubcategories = SubCategory::all();

        // Check if any subcategories were found
        if ($allSubcategories->count() > 0) {
            // Return a JSON response with the found subcategories

            return $this->PostsResponse($allSubcategories, 200);
        } else {
            // Return a JSON response indicating that no subcategories were found
            return $this->Response(false, 'Subcategory not found', 404);
        }
        // If there is no authenticated admin user, no data is returned
    }


    //Sub Category Index
    public function subcategory_index()
    {
        // Check if the authenticated admin user has permission to list subcategories
        if (auth('admin')->user()->hasPermissionTo('subcategory list')) {
            // Retrieve subcategories ordered by name, with associated categories, and paginated
            $subcategories = SubCategory::latest()->with('getCategory')->paginate(10);

            // Check if any subcategories were found
            if ($subcategories->count() > 0) {
                // Return a JSON response with the paginated subcategories

                return $this->PostsResponse($subcategories, 200);
            } else {
                // Return a JSON response indicating that there are no records
                return $this->Response(true, 'No Record Here', 200);
            }
        } else {
            // Return a JSON response indicating forbidden access
            return $this->Response(false, 'Forbidden', 403);
        }
    }


    // Sub Category
    public function subcategory_store(SubcategoryStoreRequest $request)
    {
        // Check if the authenticated admin user has permission to create a subcategory
        if (auth('admin')->user()->hasPermissionTo('create subcategory')) {


            //return $this->Response(false, "Validation Error!", 400);


            // Create a new subcategory
            $subcategory = SubCategory::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
            ]);


            // Check if the subcategory was successfully created
            if ($subcategory) {
                return $this->Response(true, "SubCategory Successfully Saved", 200);
            } else {
                // Return a JSON response indicating that the insertion failed
                return $this->Response(false, "Insert Fail", 404);
            }
        }

        // Return a JSON response indicating forbidden access
        return $this->Response(false, "Forbidden", 403);
    }


    // Subcategory Edit View
    public function subcategory_edit($id)
    {
        // Find the subcategory by its ID
        $subcategories = SubCategory::find($id);

        // Check if the subcategory with the given ID was found
        if ($subcategories) {
            // Return a JSON response with the found subcategory
            return $this->PostsResponse($subcategories, 200);
        } else {

            // Return a JSON response indicating that no data was found
            return $this->Response(false, "Data Not Found", 404);
        }
    }

    // Sub Category Update

    public function subcategory_update(Request $request, $id)
    {
        // Check if the authenticated admin user has permission to edit a subcategory
        if (auth('admin')->user()->hasPermissionTo('edit subcategory')) {



            // Find the subcategory by its ID
            $subcategory = SubCategory::find($id);

            // Check if the subcategory with the given ID was found
            if ($subcategory) {
                // Update the subcategory with the new data
                $subcategory->category_id = $request->category_id;
                $subcategory->name = $request->name;
                $subcategory->update();

                // Return a JSON response indicating success
                return $this->Response(true, "Subcategory Successfully Updated", 200);
            } else {
                // Return a JSON response indicating that no subcategory was found
                return $this->Response(false, "No Subcategory Found", 404);
            }
        } else {
            // Return a JSON response indicating forbidden access
            return $this->Response(false, "Forbidden", 403);
        }
    }


    // Sub Category Delete

    public function subcategory_delete($id)
    {
        // Check if the authenticated admin user has permission to delete a subcategory
        if (auth('admin')->user()->hasPermissionTo('delete subcategory')) {
            // Find the subcategory by its ID
            $subcategory = SubCategory::find($id);
            $product = Product::where('subcategory_id',$subcategory->id)->get();
            if($product->count() > 0) {
                return $this->Response(false, 'Delete post under this sub-category', 400);
            }
            // Check if the subcategory with the given ID was found
            if ($subcategory) {
                // Delete the subcategory
                $subcategory->delete();

                // Return a JSON response indicating success
                return $this->Response(true, "Subcategory Deleted", 200);
            } else {
                // Return a JSON response indicating that no subcategory was found
                return $this->Response(false, "Subcategory Not Found", 404);
            }
        } else {
            // Return a JSON response indicating forbidden access
            return $this->Response(false, "Forbidden", 403);
        }
    }
}
