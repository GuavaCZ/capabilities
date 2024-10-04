<?php

namespace Tests\Fixtures\Models;

use Guava\Capabilities\Concerns\HasRolesAndCapabilities;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends \Illuminate\Foundation\Auth\User {
    use HasFactory;
    use HasRolesAndCapabilities;

    protected $fillable = [
        'email',
    ];

    public $timestamps = false;

}
