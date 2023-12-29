<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = 'Email title';

    public $showEmailHeader = true;

    public $mailTitle = 'Introduction';

    public $mailContent = 'Email content';

    public $mailQuote = '';

    public $button = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data = [])
    {
        if (isset($data['subject'])) {
            $this->subject = $data['subject'];
        }

        if (isset($data['showEmailHeader'])) {
            $this->showEmailHeader = $data['showEmailHeader'];
        }
        if (isset($data['mailTitle'])) {
            $this->mailTitle = $data['mailTitle'];
        }

        if (isset($data['mailContent'])) {
            $this->mailContent = $data['mailContent'];
        }
        $this->button = isset($data['button']) ? $data['button'] : [];
        if(isset($data['mailQuote'])){
            $this->mailQuote = $data['mailQuote'];
        }
        if(isset($data['replyTo'])){
            $this->replyTo( $data['replyTo'],'');
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.generic-email')->subject($this->subject);
    }
}
