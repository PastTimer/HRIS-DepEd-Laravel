<?php

namespace App\Observers;

use App\Models\Personnel;
use App\Models\User;

class PersonnelObserver
{
    public function updated(Personnel $personnel): void
    {
        if (!$personnel->wasChanged('is_active')) {
            return;
        }

        $status = (int) $personnel->is_active === 1 ? 'active' : 'inactive';

        User::where('personnel_id', $personnel->id)->update([
            'status' => $status,
        ]);
    }
}
