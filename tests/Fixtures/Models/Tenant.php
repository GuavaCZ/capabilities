<?php

namespace Tests\Fixtures\Models;

use Guava\Capabilities\Concerns\HasRolesAndCapabilities;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;

}
