<?php

namespace App\Http\Controllers;

use App\Http\Requests\Newsletter\SubscriptedRequest;
use App\Models\NewsletterSubscriber;
use App\Response\AllResponse;

class SubscriptedController extends Controller
{
    use AllResponse;
    public function newsletterSubscripted(SubscriptedRequest $request)
    {
        NewsletterSubscriber::create($request->validated());
        return $this->Response(true, "Congratulations", 200);
    }
}
