<?php

namespace App\Observers;

use App\Models\School;
use App\Models\User;

class SchoolObserver
{
    public function updated(School $school): void
    {
        if (!$school->wasChanged('is_active')) {
            return;
        }

        $status = (int) $school->is_active === 1 ? 'active' : 'inactive';

        User::where('school_id', $school->id)->update([
            'status' => $status,
        ]);
    }
}
