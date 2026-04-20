<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationSponsorTable extends Migration{
    public function up(){
        Schema::create('notification_sponsor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id');
            $table->unsignedBigInteger('sponsor_id');
            $table->timestamps();

            $table->index('notification_id');
            $table->index('sponsor_id');

            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
            $table->foreign('sponsor_id')->references('id')->on('sponsors')->onDelete('cascade');
        });
    }

    public function down(){
        Schema::dropIfExists('notification_sponsor');
    }
}
