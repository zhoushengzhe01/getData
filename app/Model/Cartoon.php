<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cartoon extends Model
{
    protected $table = 'cartoons';

    protected $connection = 'mysql-data';
    
}
