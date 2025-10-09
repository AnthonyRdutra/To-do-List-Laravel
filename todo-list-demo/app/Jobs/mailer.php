<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log; 

class mailer implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;


    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): void
    {
        Log::info('Job executado para: ' . $this->user->email);
        Mail::send('emails.welcome', ['user' => $this->user], function ($message) {
            $message->to($this->user->email, $this->user->name)->subject('bem vindo ao sticky notes');
        });
    }
}
