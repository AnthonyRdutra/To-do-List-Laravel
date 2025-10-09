<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class TestData extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'tests';

    protected $fillable = [
        'title',
        'description',
        'created_at'
    ];
}
