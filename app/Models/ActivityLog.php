<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'log_name',
        'event',
        'description',
        'causer_id',
        'subject_type',
        'subject_id',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * The user who performed the activity.
     */
    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /**
     * The model the activity was performed on.
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Tabler badge colour for the event type.
     */
    public function getEventColorAttribute(): string
    {
        return match ($this->event) {
            'created'     => 'green',
            'approved'    => 'green',
            'handed_over' => 'cyan',
            'received'    => 'teal',
            'rejected'    => 'red',
            'deleted'     => 'red',
            'updated'     => 'yellow',
            default       => 'blue',
        };
    }

    /**
     * Tabler icon for the event type.
     */
    public function getEventIconAttribute(): string
    {
        return match ($this->event) {
            'created'     => 'ti-plus',
            'approved'    => 'ti-check',
            'handed_over' => 'ti-truck-delivery',
            'received'    => 'ti-package-import',
            'rejected'    => 'ti-x',
            'deleted'     => 'ti-trash',
            'updated'     => 'ti-pencil',
            default       => 'ti-point',
        };
    }
}
