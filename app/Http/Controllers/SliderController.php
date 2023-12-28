<?php

namespace App\Http\Controllers;

use App\Http\Requests\Slider\SliderStoreRequest;
use App\Http\Requests\Slider\SliderUpdateRequest;
use App\Models\Slider;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use App\Response\AllResponse;
use Illuminate\Support\Facades\File;




class SliderController extends Controller
{
    use AllResponse;



    public function slider_index()
    {

        $sliders = Slider::all();

        if ($sliders->count() > 0) {

            //return $this->Response(true, "Validation Error!", 200);

            return $this->PostsResponse($sliders, 200);
        } else {
            return $this->PostsResponse($sliders, 200);
        }
    }

    public function slider_store(SliderStoreRequest $request)
    {
        if (auth('admin')->user()->hasPermissionTo('slider create')) {
            $imagePath = $this->uploadImage($request->file('image'));

            $slider = Slider::create([
                'title' => $request->title,
                'url' => $request->url,
                'description' => $request->description,
                'image' => $imagePath,
            ]);

            return $this->Response(true, "Slider added Successfully", 200);
        }
    }

    public function slider_update(SliderUpdateRequest $request, $id)
    {
        if (auth('admin')->user()->hasPermissionTo('slider update')) {
            $slider = Slider::find($id);

            if ($slider) {
                if ($request->hasFile('image')) {
                    $this->unLinkImage($slider->image);
                    $imagePath = $this->uploadImage($request->file('image'));
                } else {
                    $imagePath = $slider->image;
                }

                $requestData = [
                    'title' => $request->title,
                    'url' => $request->url,
                    'description' => $request->description,
                    'image' => $imagePath,
                ];

                $slider->update($requestData);

                return $this->Response(true, "Slider updated successfully.", 200);
            } else {
                return $this->Response(false, "Slider not found.", 404);
            }
        } else {
            return $this->Response(false, "Unauthorized", 403);
        }
    }





    public function slider_delete($id)
    {
        if (auth('admin')->user()->hasPermissionTo('slider delete')) {
            $slider = Slider::find($id);

            // Check if the product was found
            if ($slider != null) {
                // Unlink associated images
                $this->unLinkImage($slider->image);

                // Delete the product
                $slider->delete();

                // Return a response indicating that the product was deleted successfully
                return $this->Response(true, "Slider deleted successfully.", 200);
            }
        }
    }

    // In your controller
    public function uploadImage($image)
    {
        $uploadPath = 'uploads/slider/';

        if (!File::isDirectory($uploadPath)) {
            File::makeDirectory($uploadPath, 0777, true, true);
        }

        $imageName = uniqid() . '.' . $image->getClientOriginalExtension();

        Image::make($image)->resize(1440, 600)->save($uploadPath . $imageName);

        return $uploadPath . $imageName;
    }

    public function unLinkImage($imagePath)
    {
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}
