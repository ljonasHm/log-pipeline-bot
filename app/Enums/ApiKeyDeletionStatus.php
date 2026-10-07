<?php

namespace App\Enums;

enum ApiKeyDeletionStatus: string
{
    case DELETED = 'deleted';
    case NOT_FOUND = 'not_found';
    case AMBIGUOUS = 'ambiguous';
}
