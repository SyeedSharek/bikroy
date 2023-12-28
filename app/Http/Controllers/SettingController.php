<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\SettingsStoreRequest;
use App\Http\Requests\Settings\SettingsUpdateRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use App\Response\AllResponse;


class SettingController extends Controller
{
    use AllResponse;
    public function setting_index()
    {

        $settings = Setting::all();

        if ($settings->count() > 0) {
            return $this->PostsResponse($settings, 200);
        } else {

            return $this->Response(false, "No Recode Here", 404);
        }
    }
    public function setting_update(SettingsUpdateRequest $request)
    {
        if (auth('admin')->user()->hasPermissionTo('edit setting')) {



            $settings = Setting::first();

            if ($settings) {
                $validatedData = $request->validated();

                if ($request->hasFile('image')) {
                    $this->unLinkImage($settings->image);
                    $imagePath = $this->uploadImage($request->file('image'));
                    $validatedData['image'] = $imagePath;
                }
                $settings->update($validatedData);
                return $this->Response(true, "Settings updated successfully.", 200);
            } else {
                return $this->Response(false, "Unauthorized", 403);
            }
        }
    }



    public function uploadImage($image)
    {
        $uploadPath = 'uploads/settings/';
        if (!File::isDirectory($uploadPath)) {
            File::makeDirectory($uploadPath, 0777, true, true);
        }
        $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
        Image::make($image)->resize(70, 70)->save($uploadPath . $imageName);
        return $uploadPath . $imageName;
    }

    public function unLinkImage($imagePath)
    {
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}
