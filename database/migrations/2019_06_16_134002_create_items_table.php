<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('template_id')->nullable();
            $table->integer('checklist_id')->nullable();
            $table->string('description');
            $table->boolean('is_completed')->nullable()->default(false);
            $table->string('completed_at')->nullable();
            $table->string('due')->nullable();
            $table->integer('urgency')->nullable();
            $table->string('updated_by')->nullable();
            $table->integer('assignee_id')->nullable();
            $table->integer('task_id')->nullable();
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
        Schema::dropIfExists('items');
    }
}
