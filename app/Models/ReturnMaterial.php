<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnMaterial extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Nama log untuk audit trail.
     */
    protected $activityLogName = 'return';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'return_materials';
    
    protected $fillable = [
        'return_number',
        'request_id',
        'returned_by',
        'quantity',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'handed_over_by',
        'handed_over_at',
        'received_by',
        'received_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'handed_over_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(RequestMaterial::class, 'request_id');
    }

    public function returner()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function handedOverBy()
    {
        return $this->belongsTo(User::class, 'handed_over_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Label alur serah terima untuk pengembalian yang sudah disetujui.
     */
    public function getHandoverStatusAttribute(): ?string
    {
        if ($this->status !== 'approved') {
            return null;
        }
        if ($this->received_at) {
            return 'received';
        }
        if ($this->handed_over_at) {
            return 'handed_over';
        }
        return 'awaiting_handover';
    }

    /**
     * Riwayat aktivitas (audit trail) untuk pengembalian ini.
     */
    public function activities()
    {
        return $this->morphMany(\App\Models\ActivityLog::class, 'subject')->latest();
    }

    /**
     * Tentukan event audit trail berdasarkan perubahan status.
     */
    protected function activityEvent(array $changes): string
    {
        if (array_key_exists('status', $changes)) {
            if ($changes['status'] === 'approved') {
                return 'approved';
            }
            if ($changes['status'] === 'rejected') {
                return 'rejected';
            }
        }

        if (array_key_exists('received_at', $changes) && $changes['received_at']) {
            return 'received';
        }

        if (array_key_exists('handed_over_at', $changes) && $changes['handed_over_at']) {
            return 'handed_over';
        }

        return 'updated';
    }

    /**
     * Deskripsi human-readable untuk timeline audit trail.
     */
    protected function activityDescription(string $event, array $properties): string
    {
        return match ($event) {
            'created'     => "Pengembalian #{$this->return_number} dibuat",
            'approved'    => 'Pengembalian disetujui',
            'rejected'    => 'Pengembalian ditolak',
            'handed_over' => 'Barang dikembalikan oleh produksi',
            'received'    => 'Barang diterima oleh store',
            'deleted'     => "Pengembalian #{$this->return_number} dihapus",
            default       => 'Data pengembalian diubah',
        };
    }

    /**
     * Label field untuk tampilan diff pada timeline.
     */
    public static function activityFieldLabels(): array
    {
        return [
            'status'         => 'Status',
            'quantity'       => 'Jumlah',
            'notes'          => 'Catatan',
            'approved_by'    => 'Diproses oleh',
            'approved_at'    => 'Tanggal diproses',
            'handed_over_by' => 'Dikembalikan oleh',
            'handed_over_at' => 'Tanggal pengembalian',
            'received_by'    => 'Diterima oleh',
            'received_at'    => 'Tanggal penerimaan',
        ];
    }
}