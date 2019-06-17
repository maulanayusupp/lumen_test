<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'templates';

    use SoftDeletes;
    protected $dates =['deleted_at'];

    // Has many item
	public function items() {
    	return $this->hasMany(Item::class, 'template_id');
    }

    // Has many checklist
	public function checklists() {
    	return $this->hasMany(Checklist::class, 'template_id');
	}
}
