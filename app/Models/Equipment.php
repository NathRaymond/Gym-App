<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipment';
    protected $fillable = [
        'name','code','description','quantity','condition','purchased_at','purchase_price',
        'last_maintenance','next_maintenance','status',
    ];
    protected $casts = ['purchased_at' => 'date', 'last_maintenance' => 'date', 'next_maintenance' => 'date', 'purchase_price' => 'decimal:2'];
}