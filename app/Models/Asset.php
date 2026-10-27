<?php
namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'category_id',
        'assigned_user_id',
        'code',
        'serial_number',
        'brand_id',
        'model',
        'location_id',
        'status',
        'purchase_date'
    ];

    protected static function booted(): void{
        static::addGlobalScope(new TenantScope);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function assignedUser(){
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function brand(){
        return $this->belongsTo(Brand::class);
    }

    public function location(){
        return $this->belongsTo(Location::class);
    }
}