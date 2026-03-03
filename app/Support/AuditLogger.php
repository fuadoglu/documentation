<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    /**
     * Write a structured activity log entry with request metadata.
     *
     * @param  array<string, mixed>  $extraProperties
     */
    public static function event(Request $request, ?Model $subject, string $event, string $description, array $extraProperties = []): void
    {
        $properties = array_merge([
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'path' => $request->path(),
            'method' => $request->method(),
        ], $extraProperties);

        $logger = activity()
            ->causedBy($request->user())
            ->event($event)
            ->withProperties($properties);

        if ($subject) {
            $logger->performedOn($subject);
        }

        $logger->log($description);
    }
}
