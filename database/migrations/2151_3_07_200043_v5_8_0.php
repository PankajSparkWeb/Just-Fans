<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class V580 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'media.enable_ffmpeg',
                    'display_name' => 'Enable FFmpeg',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "If disabled, FFmpeg won\'t convert any videos, only mp4 videos will be allowed for upload."
                        }',
                    'type' => 'checkbox',
                    'order' => 5,
                    'group' => 'Media',
                )
            )
        );
        // if ffmpeg & ffprobe path, enable the setting above
        if(getSetting('media.ffmpeg_path') && getSetting('media.ffprobe_path')){
            DB::table('settings')
                ->where('key', 'media.enable_ffmpeg')
                ->update(['value' => 1]);
        }

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'admin.send_notifications_on_contact',
                    'display_name' => 'Admin notifications for contact messages',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "If enabled, the admin users will receive an email with the contents of the contact message."
                        }',
                    'type' => 'checkbox',
                    'order' => 6,
                    'group' => 'Admin',
                )
            )
        );

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
                'media.enable_ffmpeg',
                'admin.send_notifications_on_contact',
            ])
            ->delete();
    }
}
