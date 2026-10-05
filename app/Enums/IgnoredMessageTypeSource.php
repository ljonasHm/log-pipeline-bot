<?php

namespace App\Enums;

enum IgnoredMessageTypeSource: string
{
    case USER = 'user';
    case ADMIN = 'admin';
}
