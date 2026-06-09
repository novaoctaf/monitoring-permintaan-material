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
    ];
    
    protected $casts = [
        'approved_at' => 'datetime',
    ];
    
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
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