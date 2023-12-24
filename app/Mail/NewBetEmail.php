<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewBetEmail extends Mailable
{
    use Queueable, SerializesModels;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build(): NewBetEmail
    {
        return $this->from(config('mail.from.address'))
        ->subject('New Bet')
        ->view('emails.new_bet')
        ->with(['data' => $this->data]);
    }
}
