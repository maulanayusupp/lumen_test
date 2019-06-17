<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'items';

    use SoftDeletes;
    protected $dates =['deleted_at'];

    // Belongs to template
    public function template() {
    	return $this->belongsTo(Template::class, 'template_id');
    }

    // Belongs to checklist
    public function checklist() {
    	return $this->belongsTo(Checklist::class, 'checklist_id');
	}
}
