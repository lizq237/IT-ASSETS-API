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
    if (auth()->check()) {
        $user = auth()->user();

        if ($user->organization_id) {
            $builder->where(
                $model->getTable() . '.organization_id',
                $user->organization_id
            );
        } elseif ($user->role !== 'super_admin') {
            $builder->whereRaw('1 = 0');
        }
    }
}
}
