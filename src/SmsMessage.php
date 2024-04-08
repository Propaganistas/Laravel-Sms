<?php

namespace Propaganistas\LaravelSms;

use OutOfBoundsException;
use Propaganistas\LaravelSms\Facades\Sms;

class SmsMessage
{
    protected string $content;

    protected static $regularSizes = [
        0 => 0,
        1 => 160,
        2 => 306,
        3 => 459,
        4 => 612,
        5 => 765,
        6 => 918,
        7 => 1071,
        8 => 1224,
        9 => 1377,
    ];

    protected static $unicodeSizes = [
        0 => 0,
        1 => 70,
        2 => 134,
        3 => 201,
        4 => 268,
        5 => 335,
        6 => 402,
        7 => 469,
        8 => 536,
        9 => 603,
    ];

    public function __construct(string $content = '')
    {
        $this->content($content);
    }

    public function content($content)
    {
        $this->content = $content;

        return $this;
    }

    public function hasUnicode(): bool
    {
        return strlen($this->content) !== mb_strlen($this->content);
    }

    public function amount(): int
    {
        $length = mb_strlen($this->content);

        $sizes = $this->hasUnicode() ? static::$unicodeSizes : static::$regularSizes;

        foreach ($sizes as $amount => $size) {
            if ($length <= $size) {
                return $amount;
            }
        }

        throw new OutOfBoundsException('Sms message exceeds maximum allowed length.');
    }

    public function cost(?string $mailer = null): float
    {
        return $this->amount() * Sms::mailer($mailer)->getUnitPrice();
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
