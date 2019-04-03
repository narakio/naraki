<?php namespace App\Listeners;

use Naraki\Mail\Emails\Frontend\Contact;
use App\Events\PersonSentContactRequest as ContactRequestEvent;
use Naraki\Mail\Jobs\SendMail;
use Naraki\System\Facades\System;
use Naraki\System\Models\SystemEvent;

class PersonSentContactRequest extends Listener
{
    /**
     *
     * @param \App\Events\PersonSentContactRequest $event
     * @return void
     */
    public function handle(ContactRequestEvent $event)
    {
        $data = [
            'contact_email' => $event->getContactEmail(),
            'contact_subject' => $event->getContactSubject(),
            'message_body' => $event->getMessageBody()
        ];
        $this->dispatch(
            new SendMail(
                new Contact($data)
            )
        );
        System::log()->log(SystemEvent::CONTACT_FORM_MESSAGE, $data);
    }

}