<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestMaterial extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Nama log untuk audit trail.
     */
    protected $activityLogName = 'request';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'request_materials';
    
    protected $fillable = [
        'request_number',
        'requested_by',
        'material_id',
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

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
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
     * Label alur serah terima untuk permintaan yang sudah disetujui.
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
    
    public function material()
    {
        return $this->belongsTo(Material::class)->withTrashed();
    }
    
    public function returns()
    {
        return $this->hasMany(ReturnMaterial::class, 'request_id');
    }

    /**
     * Riwayat aktivitas (audit trail) untuk permintaan ini.
     */
    public function activities()
    {
        return $this->morphMany(\App\Models\ActivityLog::class, 'subject')->latest();
    }

    /**
     * Tentukan event audit trail berdasarkan perubahan status/serah terima.
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
            'created'     => "Permintaan #{$this->request_number} dibuat",
            'approved'    => 'Permintaan disetujui',
            'rejected'    => 'Permintaan ditolak',
            'handed_over' => 'Barang diserahkan oleh store',
            'received'    => 'Barang diterima oleh produksi',
            'deleted'     => "Permintaan #{$this->request_number} dihapus",
            default       => 'Data permintaan diubah',
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
            'material_id'    => 'Material',
            'approved_by'    => 'Disetujui oleh',
            'approved_at'    => 'Tanggal persetujuan',
            'handed_over_by' => 'Diserahkan oleh',
            'handed_over_at' => 'Tanggal penyerahan',
            'received_by'    => 'Diterima oleh',
            'received_at'    => 'Tanggal penerimaan',
        ];
    }
}