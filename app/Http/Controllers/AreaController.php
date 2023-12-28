<?php

namespace App\Http\Controllers;

use App\Http\Requests\Area\AreaStoreRequest;
use App\Http\Requests\Area\AreaUpdateRequest;
use App\Models\Area;
use App\Models\Product;
use App\Response\AllResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AreaController extends Controller
{

    use AllResponse;
    // Area Search

    public function area_search(Request $request)
    {
        // Get the search term from the request
        $search = $request->name;

        // Search for areas where the area name is similar to the search term
        // or where the associated location's name is similar to the search term
        $areas = Area::where('name', 'like', "%$search%")
            ->orWhereHas('getLocation', function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            })->with('getLocation')->get();

        // Check if any matching areas were found
        if (count($areas) > 0) {
            // Return a JSON response with the found areas
            return $this->PostsResponse($areas, 200);
        } else {
            // Return a JSON response indicating that no data was found
            return $this->Response(false, 'Data Not Found', 404);
        }
    }

    // Get All Brand

    public function area_all()
    {
        $allAreas = Area::with('getLocation')->orderBy('name', 'ASC')->get();

        // Check if any Areas were found
        if ($allAreas->count() > 0) {
            // Return a JSON response with the found Areas

            return $this->PostsResponse($allAreas, 200);
        } else {
            // Return a JSON response indicating that no Areas were found
            return $this->Response(false, 'Area not found', 404);
        }

    }

    // Area Index

    public function area_index()
    {
        // Check if the authenticated admin user has permission to list areas
        if (auth('admin')->user()->hasPermissionTo('area list')) {
            // Retrieve areas ordered by name, with associated locations, and paginated
            $areas = Area::orderBy('name')->with('getLocation')->paginate(10);

            // Check if any areas were found
            if ($areas->count() > 0) {
                // Return a JSON response with the paginated areas
                return $this->PostsResponse($areas, 200);
            } else {
                // Return a JSON response indicating that there are no records
                return $this->Response(false, 'No Record Here', 200);
            }
        } else {
            // Return a JSON response indicating forbidden access
            return $this->Response(false, 'Forbidden', 403);
        }
    }

    // Area Store
    public function area_store(AreaStoreRequest $request)
    {
        // Check if the authenticated admin user has permission to create an area
        if (auth('admin')->user()->hasPermissionTo('create area')) {


            // Create a new area
            $area = Area::create([
                'location_id' => $request->location_id,
                'name' => $request->name,
            ]);


            // Check if the area was successfully created
            if ($area) {

                return $this->Response(true, 'Area Successfully Saved', 200);
            } else {
                // Return a JSON response indicating that the insertion failed
                return $this->Response(false, 'Insert Fail', 404);
            }
        } else {
            // Return a JSON response indicating forbidden access\
            return $this->Response(false, 'Forbidden', 403);
        }
    }

    // Area Edit View
    public function area_edit($id)
    {
        // Find the area by its ID
        $areas = Area::find($id);

        // Check if the area with the given ID was found
        if ($areas) {
            // Return a JSON response with the found area
            return $this->PostsResponse($areas, 200);
        } else {
            // Return a JSON response indicating that no data was found

            return $this->Response(false, 'Data Not Found', 404);
        }
    }


    // Area Update
    public function area_update(AreaUpdateRequest $request, $id)
    {
        // Check if the authenticated admin user has permission to edit an area
        if (auth('admin')->user()->hasPermissionTo('edit area')) {



            // Find the area by its ID
            $area = Area::find($id);

            // Check if the area with the given ID was found
            if ($area) {
                // Update the area with the new data
                $area->location_id = $request->location_id;
                $area->name = $request->name;
                $area->update();

                // Return a JSON response indicating success
                return $this->Response(true, 'Area Successfully Updated', 200);
            } else {
                // Return a JSON response indicating that no area was found
                return $this->Response(false, 'No Area Found', 400);
            }
        } else {
            // Return a JSON response indicating forbidden access

            return $this->Response(false, 'Forbidden', 403);
        }
    }

    // Area Delete

    public function area_delete($id)
    {
        // Check if the authenticated admin user has permission to delete an area
        if (auth('admin')->user()->hasPermissionTo('delete area')) {
            // Find the area by its ID
            $area = Area::find($id);
            $product = Product::where('area_id', $area->id)->get();
            if ($product->count() > 0) {
                return $this->Response(false, 'Delete post under this area', 400);
            }
            // Check if the area with the given ID was found
            if ($area) {
                // Delete the area
                $area->delete();

                // Return a JSON response indicating success
                return $this->Response(true, 'Area Deleted', 200);
            } else {
                // Return a JSON response indicating that no area was found
                return $this->Response(false, 'Area Not Found', 404);
            }
        } else {
            // Return a JSON response indicating forbidden access
            return $this->Response(false, 'Forbidden', 403);
        }
    }
}