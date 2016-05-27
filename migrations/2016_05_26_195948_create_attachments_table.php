<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('attachments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('model');
			$table->string('model_id');
			$table->string('path');
			$table->string('filename');
			$table->string('extension');
			$table->string('mime_type');
			$table->integer('size');
			$table->text('parameters');
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
		Schema::drop('attachments');
    }
}
