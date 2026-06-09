<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'user_id',
        'quantity_before',
        'adjustment_quantity',
        'quantity_after',
        'type',
        'notes'
    ];

    public function material()
    {
        return $this->belongsTo(Material::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}