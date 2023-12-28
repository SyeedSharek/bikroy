<?php

namespace App\Http\Controllers;

use App\Http\Requests\Location\LocationStoreRequest;
use App\Http\Requests\Location\LocationUpdateRequest;
use App\Models\Location;
use App\Models\Product;
use App\Response\AllResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    use AllResponse;

    // Location Search
    public function location_search(Request $request)
    {
        // Extract the search query from the request
        $search = $request->name;

        // Perform a search in the 'locations' table where the 'name' column is like the search query
        // Also, include related 'getArea' data where the 'name' column is like the search query
        $locations = Location::where('name', 'like', "%$search%")
            ->orWhereHas('getArea', function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            })
            ->with('getArea') // Eager load the 'getArea' relationship to avoid N+1 query problem
            ->get();

        // Check if any matching locations were found
        if (count($locations) > 0) {
            // Return a JSON response with the found locations
            return $this->PostsResponse($locations, 200);
        } else {
            // Return a JSON response indicating that no data was found
            return $this->Response(false, 'Data Not Found', 404);
        }
    }


    // Get All Location
    public function location_all()
    {
        // Retrieve all locations from the 'locations' table, ordered by name
        // Also, eager load the 'getArea' relationship to avoid N+1 query problem
        $allocation = Location::orderBy('name', 'ASC')->with('getArea')->get();

        // Check if any locations were found
        if ($allocation->count() > 0) {
            // Return a JSON response with the found locations
            return $this->PostsResponse($allocation, 200);
        } else {
            // Return a JSON response indicating that no locations were found
            return $this->Response(false, "All location doesn't exist", 404);
        }
    }


    //Location Index
    public function location_index()
    {
        // Check if the authenticated admin user has permission to view the location list
        if (auth('admin')->user()->hasPermissionTo('location list')) {

            // Retrieve paginated locations from the 'locations' table, ordered by name
            // Also, eager load the 'getArea' relationship to avoid N+1 query problem
            $locations = Location::orderBy('name')->with('getArea')->paginate(10);

            // Check if any locations were found
            if ($locations->count() > 0) {
                // Return a JSON response with the paginated locations
                return $this->PostsResponse($locations, 200);
            } else {
                // Return a JSON response indicating that no locations were found
                return $this->Response(false, 'No Record Here', 404);
            }
        } else {
            // Return a JSON response indicating that the admin user doesn't have permission to view the location list
            return $this->Response(false, 'Forbidden', 403);
        }
    }


    // Location Store
    public function location_store(LocationStoreRequest $request)
    {
        // Check if the authenticated admin user has permission to create a location
        if (auth('admin')->user()->hasPermissionTo('create location')) {




            // Create a new location using the provided data
            $location = Location::create([
                'name' => $request->name,
            ], 201);


            // Check if the location was successfully created
            if ($location) {
                // Return a JSON response indicating successful location creation
                return $this->response(true, 'Location Successfully Saved', 200);
            } else {
                // Return a JSON response indicating failure to insert the location
                return $this->response(false, 'Insert Fail', 401);
            }
        } else {
            // Return a JSON response indicating that the admin user doesn't have permission to create a location
            return $this->response(false, 'Forbidden', 403);
        }
    }




    // Location Edit View

    public function location_edit($id)
    {
        // Find the location by its ID
        $location = Location::find($id);

        // Check if the location was found
        if ($location) {
            // Return a JSON response with the found location for editing
            return response()->json([
                'location' => $location,
            ], 200);
        } else {
            // Return a JSON response indicating that the location data was not found
            return response()->json([
                'status' => false,
                'message' => 'Data Not Found',
            ], 404);
        }
    }


    // Location Update
    public function location_update(LocationUpdateRequest $request, $id)
    {
        // Check if the authenticated admin user has permission to edit a location
        if (auth('admin')->user()->hasPermissionTo('edit location')) {



            // Find the location by its ID
            $location = Location::find($id);

            // Check if the location was found
            if ($location) {
                // Update the location name with the provided data
                $location->name = $request->name;
                $location->update();

                // Return a JSON response indicating successful location update
                return $this->Response(true, 'Location Successfully Updated', 200);
            } else {
                // Return a JSON response indicating that no location was found for the given ID
                return $this->Response(false, 'No Location Found', 404);
            }
        } else {
            // Return a JSON response indicating that the admin user doesn't have permission to edit a location
            return $this->Response(false, 'Forbidden', 403);
        }
    }

    // Lacation Delete

    public function location_delete($id)
    {
        // Check if the authenticated admin user has permission to delete a location
        if (auth('admin')->user()->hasPermissionTo('delete location')) {

            // Find the location by its ID
            $location = Location::find($id);
            $product = Product::where('location_id', $location->id)->get();
            if ($product->count() > 0) {
                return $this->Response(false, 'Delete post under this location', 400);
            }
            // Check if the location was found
            if ($location) {
                // Delete the location
                $location->delete();

                // Return a JSON response indicating successful location deletion
                return $this->Response(true, 'Location Deleted', 200);
            } else {
                // Return a JSON response indicating that no location was found for the given ID
                return $this->Response(false, 'Location Not Found', 404);
            }
        } else {
            // Return a JSON response indicating that the admin user doesn't have permission to delete a location
            return $this->Response(false, 'Forbidden', 403);
        }
    }
}