<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('creator_id');
            $table->integer('storage_id');
            $table->integer('preset_id');
            $table->string('hash_name');
            $table->string('vk_token_group')->default('none');
            $table->string('vk_token_user')->default('none');
            $table->string('vk_token_confirm')->default('none');
            $table->string('vk_secret_key')->default('none');
            $table->string('tg_token')->default('none');
            $table->string('tg_proxy')->default('none');
            $table->string('vb_token')->default('none');
            $table->string('vk_status')->default(0);
            $table->string('tg_status')->default(0);
            $table->string('vb_status')->default(0);
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
        Schema::dropIfExists('bots');
    }
}
