<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostInterestTable extends Migration
{
    public function up()
    {
        Schema::create('post_interest', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('newinterest_id');
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('newinterest_id')->references('id')->on('newinterests')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_interest');
    }
}
