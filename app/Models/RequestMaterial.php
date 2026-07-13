<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestMaterial extends Model
{
    use HasFactory;
    
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
}