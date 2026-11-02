<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
// use Illuminate\Database\Eloquent\Model;
class Ticket extends Model
{
    protected $connection = 'mongodb';

protected $fillable = [
    'organization_id',
    'asset_id',
    'user_id',
    'title',
    'description',
    'priority',
    'status',
    'image_path',
];

protected $casts = [
    'organization_id' => 'integer',
    'asset_id' => 'integer',
    'user_id' => 'integer',
];
}
