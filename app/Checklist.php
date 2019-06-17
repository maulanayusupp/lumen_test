<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checklist extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'checklists';

    use SoftDeletes;
    protected $dates =['deleted_at'];

    // Belongs to template
    public function template() {
    	return $this->belongsTo(Template::class, 'template_id');
    }
}
