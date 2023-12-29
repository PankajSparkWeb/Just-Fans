<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V620 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        DB::table('settings')
            ->where('key', 'feed.min_post_description')
            ->update([
                'order' => 3,
            ]);


        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'feed.post_box_max_height',
                    'display_name' => 'Post box max media height',
                    'value' => '',
                    'details' => '{"description" : "Pixel value of maximum posts media height. EG: 450. If value is present, images and videos within post boxes will be cropped/adjusted to that max value when not in full screen."}',
                    'type' => 'text',
                    'order' => 4,
                    'group' => 'Feed',
                )
            )
        );

        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->timestamp('release_date')->after('status')->nullable();
                $table->timestamp('expire_date')->after('release_date')->nullable();
            });
        }

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'feed.allow_post_scheduling',
                    'display_name' => 'Allow post scheduling',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Having this set to ON, will allow users to create posts with release & expiry dates."
                        }',
                    'type' => 'checkbox',
                    'order' => 5,
                    'group' => 'Feed',
                )
            )
        );

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'payments.ccbill_skip_subaccount_from_cancellations',
                    'display_name' => 'CCBill skip subaccount from cancellations',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Only use this if CCBill instructed you to do so. This is only required for rare CCBill accounts, users were required to skip providing their sub account during cancellation requests."
                        }',
                    'type' => 'checkbox',
                    'order' => 35,
                    'group' => 'Payments',
                )
            )
        );

        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('stream_id')->after('user_message_id')->nullable();
            $table->foreign('stream_id')->references('id')->on('streams')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')
            ->whereIn('key', [
                'feed.post_box_max_height',
                'feed.allow_post_scheduling',
                'payments.ccbill_skip_subaccount_from_cancellations'
            ])
            ->delete();

        Schema::table('posts', function($table) {
            $table->dropColumn('release_date');
            $table->dropColumn('expire_date');
        });


        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign('notifications_stream_id_foreign');
            $table->dropColumn('stream_id');
        });

    }
}
