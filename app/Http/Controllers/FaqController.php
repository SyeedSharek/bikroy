<?php

namespace App\Http\Controllers;

use App\Http\Requests\Faq\FaqStoreRequest;
use App\Http\Requests\Faq\FaqUpdateRequest;
use App\Models\Faq;
use Illuminate\Http\Request;
use App\Response\AllResponse;


class FaqController extends Controller
{
    use AllResponse;

    public function __construct()
    {
        $this->middleware('jwt:admin', ['except' => ['faq_index']]);
    }

    // Show Details
    public function faq_index()
    {

        $faqs = Faq::all();

            if ($faqs) {

                return $this->PostsResponse($faqs, 200);
            } else {

                return $this->Response(false, 'No Record Here', 200);
            }

    }

    // Create FAQ

    public function faq_store(FaqStoreRequest $request)
    {

        if (auth('admin')->user()->hasPermissionTo('manage faq')) {
             Faq::create($request->validated());



                return $this->Response(true, 'FAQ Successfully Saved', 200);

        } else {

            return $this->Response(false, 'Forbidden', 403);
        }
    }

    public function faq_edit($id)
    {
        // Find the area by its ID
        $faq = Faq::find($id);

        // Check if the area with the given ID was found
        if ($faq) {
            // Return a JSON response with the found area
            return $this->PostsResponse($faq, 200);
        } else {
            // Return a JSON response indicating that no data was found

            return $this->Response(false, 'Data Not Found', 404);
        }

    }

    public function faq_update(FaqUpdateRequest $request, $id)
    {
         // Check if the authenticated admin user has permission to edit an area
         if (auth('admin')->user()->hasPermissionTo('manage faq')) {



            // Find the area by its ID
            $faq = Faq::find($id)->update($request->validated());
            return $this->Response(true, 'FAQ Updated', 201);
        } else {
            // Return a JSON response indicating forbidden access

            return $this->Response(false, 'Forbidden', 403);
        }
    }
    public function faq_destroy($id)
    {

        if (auth('admin')->user()->hasPermissionTo('manage faq')) {
            // Find the area by its ID
            $faq = Faq::find($id)->delete();
                // Return a JSON response indicating success
                return $this->Response(true, 'FAQ Deleted', 200);

        } else {
            // Return a JSON response indicating forbidden access
            return $this->Response(false, 'Forbidden', 403);
        }

    }
}
