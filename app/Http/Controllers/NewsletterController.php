<?php

namespace App\Http\Controllers;

use App\Http\Requests\Newsletter\NewsletterRequest;
use App\Jobs\SendNewsletterJob;
use App\Models\Newsletter;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Models\SocialMedia;
use App\Response\AllResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Stripe\Tax\Settings;

class NewsletterController extends Controller
{
    use AllResponse;
    public function index(Newsletter $newsletter)
    {
        $allNewsletter = $newsletter->latest()->paginate(10);
        if ($allNewsletter->count() > 0) {
            return $this->PostsResponse($allNewsletter, 200);
        }
        return $this->PostsResponse($allNewsletter, 404);
    }
    public function store(NewsletterRequest $request)
    {
        $image = $this->uploadImage($request->file('image'));
        Newsletter::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $image
        ]);
        return $this->Response(true, 'Newsletter created', 201);
    }
    public function update($id, NewsletterRequest $request)
    {
        $newsletter = Newsletter::find($id);
        if ($request->hasFile('image')) {
            $this->unlinkImage($newsletter);
            $image = $this->uploadImage($request->file('image'));
            $newsletter->update([
                'title' => $request->title,
                'description' => $request->description,
                'image' => $image
            ]);
            return $this->Response(true, 'Newsletter updated', 201);
        }
        $newsletter->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);
        return $this->Response(true, 'Newsletter updated', 201);
    }
    public function delete($id)
    {
        $newsletter = Newsletter::find($id);
        if (!$newsletter) {
            $this->unlinkImage($newsletter);
            $newsletter->delete();
            return $this->Response(true, 'Newsletter deleted', 200);
        }
        return $this->Response(true, 'Newsletter deleted', 200);
    }
    public function publish($id)
    {

        $newsletter = Newsletter::find($id)->first();
        $socialMedia = Setting::select('facebook', 'instagram', 'twitter', 'wtsapp')->first();
        if ($newsletter->publish_date == null) {
            $newsletter->update(['publish_date' => now()]);
            SendNewsletterJob::dispatch(['newsletter' => $newsletter, 'social_media' => $socialMedia]);
            return $this->Response(true, 'newsletter published', 200);
        }
        return $this->Response(false, 'newsletter all ready published', 400);
    }
    private function uploadImage($file)
    {
        $uploadPath = 'uploads/newsletter/';
        if (!File::isDirectory($uploadPath)) {
            File::makeDirectory($uploadPath, 0777, true, true);
        }
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '.' . $extension;
        Image::make($file)->resize(600, 400)->save($uploadPath  . $filename);
        return $uploadPath . $filename;
    }
    private function unlinkImage($file)
    {
        if (file_exists($file->image)) {
            unlink($file->image);
        }
    }
    public function search(Request $request)
    {
        $data = Newsletter::filter($request->title)->get();
        if ($data->count() < 0) {
            return $this->Response(false, 'data not found', 404);
        }
        return $this->PostsResponse($data, 200);
    }
}
