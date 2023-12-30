<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ErrorEmail extends Mailable
{
    use Queueable, SerializesModels;

    private string $error;

    public function __construct(string $error)
    {
        $this->error = $error;
    }

    public function build(): ErrorEmail
    {
        return $this->from(config('mail.from.address'))
            ->subject('Error')
            ->view('error')
            ->with(['error' => $this->error]);
    }
}
