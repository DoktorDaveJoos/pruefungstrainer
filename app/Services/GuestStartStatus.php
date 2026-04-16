<?php

namespace App\Services;

enum GuestStartStatus: string
{
    case Available = 'available';
    case Resume = 'resume';
    case AlreadyDone = 'already_done';
}
