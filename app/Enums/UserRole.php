<?php

namespace App\Enums;

enum UserRole: string
{
    case NONE = 'none';
    case ADMIN = 'admin';
    case RECEIVER = 'receiver';
}