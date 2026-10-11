<?php

namespace App\Services;

use Spatie\Activitylog\Models\Activity;

class ActivityLogger
{
    public static function log(string $description, $subject = null, array $properties = [], string $logName = 'default'): Activity
    {
        $activity = activity($logName)
            ->causedBy(auth()->user())
            ->withProperties(array_merge($properties, [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
            ]));

        if ($subject) $activity->performedOn($subject);

        return $activity->log($description);
    }
}