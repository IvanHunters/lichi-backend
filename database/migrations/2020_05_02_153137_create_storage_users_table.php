<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStorageUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storage_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('creator_id');
            $table->string('host')->default('w999623p.beget.tech');
            $table->integer('port')->default('3306');
            $table->string('database');
            $table->string('username');
            $table->string('password');
            $table->integer('type')->default(1);
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
        Schema::dropIfExists('storage_users');
    }
}
