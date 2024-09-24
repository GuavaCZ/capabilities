<?php

namespace Tests\Fixtures\Models;

use Guava\Capabilities\Concerns\HasRolesAndCapabilities;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model {
    use HasFactory;
    use HasRolesAndCapabilities;

    protected $fillable = [
        'email',
    ];

    public $timestamps = false;

}
