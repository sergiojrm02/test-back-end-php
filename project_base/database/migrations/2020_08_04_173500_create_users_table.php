<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function (Blueprint $table) {
			$table->engine = 'InnoDB';
			$table->increments('id')->unsigned();
			$table->string('cpf', 20)->unique();
			$table->string('email', 100)->unique();
			$table->string('full_name', 200);
			$table->string('password', 255);
			$table->string('phone_number', 20);
            $table->boolean('active')->default(true);
			//$table->unique(['cpf', 'email']);
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
        Schema::disableForeignKeyConstraints();
		Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();
	}
}
