<?php

namespace App\Traits;

use App\Models\ActivityLog;

/**
 * Menambahkan audit trail otomatis pada model.
 *
 * Setiap pembuatan, perubahan, dan penghapusan data dicatat ke tabel
 * activity_logs beserta pengguna (causer) yang melakukannya. Model dapat
 * menyesuaikan nama event dan deskripsi dengan meng-override
 * activityEvent() / activityDescription().
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->recordActivity('created', $model->cleanActivityAttributes($model->getAttributes()));
        });

        static::updated(function ($model) {
            $changes = $model->cleanActivityAttributes($model->getChanges());

            if (empty($changes)) {
                return;
            }

            $old = [];
            foreach (array_keys($changes) as $key) {
                $old[$key] = $model->getOriginal($key);
            }

            $model->recordActivity(
                $model->activityEvent($changes),
                ['old' => $old, 'attributes' => $changes]
            );
        });

        static::deleted(function ($model) {
            $model->recordActivity('deleted', $model->cleanActivityAttributes($model->getAttributes()));
        });
    }

    /**
     * Tulis satu baris audit trail.
     */
    public function recordActivity(string $event, array $properties = [], ?string $description = null): ActivityLog
    {
        return ActivityLog::create([
            'log_name'     => $this->activityLogName(),
            'event'        => $event,
            'description'  => $description ?? $this->activityDescription($event, $properties),
            'causer_id'    => auth()->id(),
            'subject_type' => static::class,
            'subject_id'   => $this->getKey(),
            'properties'   => $properties,
        ]);
    }

    /**
     * Buang atribut yang tidak perlu dicatat (timestamp, dsb).
     */
    protected function cleanActivityAttributes(array $attributes): array
    {
        $except = array_merge(
            ['id', 'created_at', 'updated_at', 'remember_token', 'password'],
            $this->activityLogExcept ?? []
        );

        return collect($attributes)->except($except)->all();
    }

    protected function activityLogName(): string
    {
        return $this->activityLogName ?? $this->getTable();
    }

    /**
     * Tentukan nama event dari perubahan atribut. Override di model.
     */
    protected function activityEvent(array $changes): string
    {
        return 'updated';
    }

    /**
     * Deskripsi human-readable. Override di model untuk label khusus.
     */
    protected function activityDescription(string $event, array $properties): string
    {
        return match ($event) {
            'created' => 'Data dibuat',
            'updated' => 'Data diubah',
            'deleted' => 'Data dihapus',
            default   => ucfirst($event),
        };
    }
}
