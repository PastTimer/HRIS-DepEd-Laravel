<?php

namespace App\Observers;

use App\Models\Personnel;
use App\Models\School;
use App\Models\User;

class UserObserver
{
    public function saving(User $user): void
    {
        if ($user->personnel_id) {
            $personnel = Personnel::find($user->personnel_id);
            if ($personnel) {
                $user->school_id = null;
                $user->status = (int) $personnel->is_active === 1 ? 'active' : 'inactive';
            }

            return;
        }

        if ($user->school_id) {
            $school = School::find($user->school_id);
            if ($school) {
                $user->status = (int) $school->is_active === 1 ? 'active' : 'inactive';
            }
        }
    }

    public function updated(User $user): void
    {
        if (!$user->wasChanged('status')) {
            return;
        }

        if (!in_array($user->status, ['active', 'inactive'], true)) {
            return;
        }

        $isActive = $user->status === 'active' ? 1 : 0;

        if ($user->personnel_id) {
            Personnel::where('id', $user->personnel_id)->update(['is_active' => $isActive]);

            return;
        }

        if ($user->school_id) {
            School::where('id', $user->school_id)->update(['is_active' => $isActive]);
        }
    }
}
