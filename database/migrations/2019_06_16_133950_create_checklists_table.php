<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('template_id')->nullable(); // required
            $table->string('object_domain'); // required
            $table->string('object_id'); // required
            $table->string('description'); // required
            $table->boolean('is_completed')->nullable()->default(false);
            $table->string('completed_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('due')->nullable();
            $table->integer('urgency')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checklists');
    }
}
