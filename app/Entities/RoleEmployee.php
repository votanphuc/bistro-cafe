<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class RoleEmployee extends Model implements Transformable
{
    use TransformableTrait;

    protected $fillable = [];
    protected $table = 'role_employee';

}