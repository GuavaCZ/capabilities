<?php

namespace Tests\Fixtures\Models;

use Guava\Capabilities\Concerns\HasRolesAndCapabilities;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    use HasRolesAndCapabilities;

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;
}
