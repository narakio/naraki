<?php namespace App\Emails;

use Illuminate\Queue\SerializesModels;

class Email
{
    use SerializesModels;
    protected $viewName;
    protected $view=[];
    protected $data;
    protected $domain;
    protected $files;
    protected $from;
    protected $fromName;
    protected $testing;


    /**
     *
     * @param array $data
     * @param array $files
     * @param bool $testing
     */
    public function __construct($data, $files = null, $testing = false)
    {
        $this->parseFiles($files);
        $this->data     = (object)$data;
        $this->from     = \Config::get('mail.from.address');
        $this->fromName = \Config::get('mail.from.name');
        $this->testing  = $testing;
    }

    /**
     * @return mixed
     */
    public function send()
    {
        $this->prepareViewData();
        $this->setDomain();
        $this->sendmail();
    }

    /**
     * @return void
     */
    protected function sendmail()
    {
        $currentInstance = $this;

        \Mail::send($this->viewName, $this->view, function ($message) use ($currentInstance) {
            return call_user_func([$currentInstance, 'message'], $message);
        });
    }

    /**
     * @param \Illuminate\Mail\Message $message
     */
    public function message($message)
    {
        $message->subject($this->view['subject']);
        $message->from($this->from, $this->fromName);
        $message->to($this->data->user->getAttribute('email'), $this->data->user->getFullname());
        $message->replyTo($this->from, $this->fromName);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return ['data' => $this->data, 'view' => $this->view];
    }

    /**
     * @return void
     */
    public function setDomain()
    {
        $this->domain = 'local';
    }

    /**
     * @param array $files
     */
    private function parseFiles($files)
    {
        if ( ! is_null($files)) {
            foreach ($files as $file) {
                $path          = storage_path() . '/uploads/' . str_random(5) . $file->getClientOriginalName();
                $this->files[] = (object)[
                    'path' => $path,
                    'as'   => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType()
                ];
                \File::move($file->getRealPath(), $path);
            }
        }
    }

}