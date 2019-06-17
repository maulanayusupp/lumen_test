<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class History extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'histories';

    use SoftDeletes;
    protected $dates =['deleted_at'];
}
