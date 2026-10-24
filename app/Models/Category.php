<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;

class Category extends Model{
    protected $fillable = [
        'name',
        'description',
        'organization_id',
    ];

    //proteccion de datos por tenant, para que un usuario solo vea las categorias de su organizacion
    protected static function booted(){
        static::addGlobalScope(new TenantScope);
    }

    public function assets(){
        return $this->hasMany(Asset::class);
    }
}
