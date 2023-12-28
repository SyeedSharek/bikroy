<?php

namespace App\Jobs;

use App\Mail\NewsletterMail;
use App\Models\NewsletterSubscriber;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $newsletter;
    public $socialMedia;
    public $tries = 3;
    /**
     * Create a new job instance.
     */
    public function __construct($newsletter, $socialMedia)
    {
        $this->newsletter = $newsletter;
        $this->socialMedia = $socialMedia;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {


        $subscribers =  NewsletterSubscriber::where('is_subscribed', 1)->get();

        foreach ($subscribers as $subscriber) {
            Mail::to($subscriber->email)->send(new NewsletterMail($this->newsletter, $subscriber, $this->socialMedia));
        }
    }
    public function failed(Exception $e)
    {
        info('Failed to send newsletter: ' . $e->getMessage());
    }
}
