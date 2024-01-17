<?php

// database/migrations/xxxx_xx_xx_create_user_interest_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserInterestTable extends Migration
{
    public function up()
    {
        Schema::create('user_interest', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('newinterest_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('newinterest_id')->references('id')->on('newinterests')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_interest');
    }
}
 
