<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
			
			/*------------Extra fields----------*/
			$table->integer('section_id');
			$table->integer('role_id');
			$table->boolean('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('role_sections');
    }
}
