<?php

namespace App\Listeners;

use App\Events\SentMailEvent;
use App\Mail\SentMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SentMailListner implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SentMailEvent $event): void
    {
        if($event->user == Null){
            abort(403);
        }
        Mail::to($event->user->email)->send(
            (new SentMail())->afterCommit()
        );
    }
}
