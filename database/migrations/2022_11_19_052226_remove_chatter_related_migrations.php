<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('chatter_post');
        Schema::dropIfExists('chatter_user_discussion');
        Schema::dropIfExists('chatter_discussion');
        Schema::dropIfExists('chatter_categories');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('chatter_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->nullable();
            $table->integer('order')->default(1);
            $table->string('name');
            $table->string('color', 20);
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('chatter_discussion', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('chatter_category_id')->unsigned()->default('1');
            $table->string('title');
            $table->integer('user_id')->unsigned();
            $table->boolean('sticky')->default(false);
            $table->integer('views')->unsigned()->default('0');
            $table->boolean('answered')->default(0);
            $table->timestamps();
            $table->string('slug')->unique();
            $table->string('color', 20)->nullable()->default('#232629');
            $table->softDeletes();
            $table->timestamp('last_reply_at')->useCurrent();
        });

        Schema::create('chatter_post', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('chatter_discussion_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->text('body');
            $table->timestamps();
            $table->boolean('markdown')->default(0);
            $table->boolean('locked')->default(0);
            $table->softDeletes();
        });

        Schema::create('chatter_user_discussion', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('discussion_id')->unsigned()->index();
            $table->foreign('discussion_id')->references('id')->on('chatter_discussion')->onDelete('cascade');
            $table->primary(['user_id', 'discussion_id']);
        });


        Schema::table('chatter_discussion', function (Blueprint $table) {
            $table->foreign('chatter_category_id')->references('id')->on('chatter_categories')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        Schema::table('chatter_post', function (Blueprint $table) {
            $table->foreign('chatter_discussion_id')->references('id')->on('chatter_discussion')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }
};
