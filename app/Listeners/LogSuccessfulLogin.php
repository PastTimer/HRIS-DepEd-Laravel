<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct(public Request $request)
    {
        // We inject the Request here so we can grab the IP Address and Browser!
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        ActivityLog::create([
            'user_id' => $event->user->id,
            'username' => $event->user->username,
            'action_type' => 'LOGIN',
            'module' => 'Authentication',
            'description' => 'User logged in successfully.',
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ]);
    }
}