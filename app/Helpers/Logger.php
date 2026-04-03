<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class Logger
{
    public static function log($description, $subject = null, $logName = 'default', $properties = [])
    {
        $causerType = null;
        $causerId = null;

        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $causerType = get_class($user);
            $causerId = $user->id;
        } elseif (Auth::guard('citizen')->check()) {
            $user = Auth::guard('citizen')->user();
            $causerType = get_class($user);
            $causerId = $user->id;
        }

        ActivityLog::create([
            'causer_type' => $causerType,
            'causer_id' => $causerId,
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'properties' => $properties,
            'ip_address' => Request::ip(),
        ]);
    }
}
