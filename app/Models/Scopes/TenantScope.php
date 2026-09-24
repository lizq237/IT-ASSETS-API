<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void

    {
        //validamos que el usario tenga una sesion activa y que tenga un organization_id, si es asi filtramos por el organization_id del usuario
        if (auth()->check() && Auth()->user()->organization_id) {
            // Este scope pega a la consulta un where organization_id = organization_id del usuario logueado
            $builder->where($model->getTable() . '.organization_id', auth()->user()->organization_id);
        }
    }
}
