<?php

namespace App\Enums;

enum SmsStatus: string
{
    case PENDING = 'pending';
    case SENT    = 'sent';
    case FAILED  = 'failed';
}
