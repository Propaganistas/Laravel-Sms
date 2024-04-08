<?php

namespace Propaganistas\LaravelSms\Drivers;

use Illuminate\Support\Collection;
use Illuminate\Support\Testing\Fakes\Fake;

class ArrayDriver extends SmsDriver implements Fake
{
    public function __construct(
        protected array $config = [],
        protected Collection $messages = new Collection
    ) {
        parent::__construct($config);
    }

    protected function performSend(): void
    {
        $this->messages->push([
            'recipient' => $this->recipient,
            'message' => $this->message,
        ]);
    }

    public function messages(): Collection
    {
        return $this->messages;
    }

    public function flush(): Collection
    {
        return $this->messages = new Collection;
    }
}
