<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialConsumption extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'consumed_by',
        'quantity',
        'notes',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class)->withTrashed();
    }

    public function consumer()
    {
        return $this->belongsTo(User::class, 'consumed_by');
    }
}
