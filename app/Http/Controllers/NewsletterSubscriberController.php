<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Response\AllResponse;
use Illuminate\Http\Request;

class NewsletterSubscriberController extends Controller
{
    use AllResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(NewsletterSubscriber $newsletterSubscriber)
    {
        $subscribers = $newsletterSubscriber->latest()->select('id', 'email', 'is_subscribed', 'created_at')->paginate(10);
        if ($subscribers->count() > 0) {
            return $this->PostsResponse($subscribers, 200);
        }
        return $this->PostsResponse($subscribers, 404);
    }
}
