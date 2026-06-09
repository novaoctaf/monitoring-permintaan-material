<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnMaterial extends Model
{
    use HasFactory;
    
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
    ];
    
    protected $casts = [
        'approved_at' => 'datetime',
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
}