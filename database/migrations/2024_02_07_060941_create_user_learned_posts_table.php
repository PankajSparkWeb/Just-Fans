<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserlearnedPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
  // In a migration file (e.g., create_userlearned_posts_table.php)
public function up()
{
    Schema::create('userlearned_posts', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('post_id');
        // Add other columns as needed
        $table->timestamps();
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
    });
}


public function down()
{
    Schema::dropIfExists('userlearned_posts');
}

}