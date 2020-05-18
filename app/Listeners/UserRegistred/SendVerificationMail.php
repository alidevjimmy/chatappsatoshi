<?php

namespace App\Listeners\UserRegistred;

use App\Events\UserRegistred;
use App\Mail\UserVerificationMailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendVerificationMail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserRegistred  $event
     * @return void
     */
    public function handle(UserRegistred $event)
    {
        Mail::to($event->user['email'])->queue(new UserVerificationMailable($event->user));
    }
}
