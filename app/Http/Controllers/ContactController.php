<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\ContactStoreRequest;
use App\Models\Contact;
use App\Response\AllResponse;


use Illuminate\Http\Request;

class ContactController extends Controller
{
    use AllResponse;

    public function contact_index()
    {


        if (auth('admin')->user()->hasPermissionTo('contact view')) {
            $contacts = Contact::where('status', 1)->latest()->paginate(10);
            if ($contacts->count() > 0) {
                if ($contacts) {

                    return $this->PostsResponse($contacts, 200);
                } else {

                    return $this->Response(false, 'No Record Here', 404);
                }
            }
        } else {
            return $this->Response(false, 'Forbidden', 403);
        }
    }

    public function contact_store(ContactStoreRequest $request)
    {
        $contact = Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => str_replace('-', '', $request->phone),
            'message' => $request->message,
        ]);
        if ($contact) {
            return $this->Response(true, 'Contact Successfully Saved', 200);
        } else {
            return $this->Response(false, 'Insert Fail', 404);
        }
    }
    public function contact_update(Request $request, $id)
    {
        $status = Contact::find($id)->update([
            'status' => 0,
        ]);
        return $this->Response(true, 'Status Changed', 200);
    }
}
