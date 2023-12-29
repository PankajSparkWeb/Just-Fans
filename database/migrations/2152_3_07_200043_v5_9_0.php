<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V590 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wallets')) {
            Schema::table('wallets', function (Blueprint $table) {
                $table->dropColumn('stripe_balance');
                $table->dropColumn('paypal_balance');
            });
        }

        // Updating `Site` settings tabs order for more logic
        DB::statement("UPDATE settings SET `order`=10 WHERE id=1;");
        DB::statement("UPDATE settings SET `order`=20 WHERE id=2;");
        DB::statement("UPDATE settings SET `order`=40 WHERE id=54;");
        DB::statement("UPDATE settings SET `order`=50 WHERE id=55;");
        DB::statement("UPDATE settings SET `order`=60 WHERE id=56;");
        DB::statement("UPDATE settings SET `order`=70 WHERE id=108;");
        DB::statement("UPDATE settings SET `order`=80 WHERE id=109;");
        DB::statement("UPDATE settings SET `order`=90 WHERE id=110;");
        DB::statement("UPDATE settings SET `order`=100 WHERE id=111;");
        DB::statement("UPDATE settings SET `order`=110 WHERE id=112;");
        DB::statement("UPDATE settings SET `order`=120 WHERE id=113;");
        DB::statement("UPDATE settings SET `order`=130 WHERE id=116;");
        DB::statement("UPDATE settings SET `order`=170 WHERE id=119;");
        DB::statement("UPDATE settings SET `order`=0 WHERE id=123;");
        DB::statement("UPDATE settings SET `order`=299 WHERE id=124;");
        DB::statement("UPDATE settings SET `order`=180 WHERE id=154;");
        DB::statement("UPDATE settings SET `order`=30 WHERE id=164;");
        DB::statement("UPDATE settings SET `order`=310 WHERE id=165;");
        DB::statement("UPDATE settings SET `order`=300 WHERE id=166;");
        DB::statement("UPDATE settings SET `order`=150 WHERE id=167;");
        DB::statement("UPDATE settings SET `order`=160 WHERE id=171;");
        DB::statement("UPDATE settings SET `order`=140 WHERE id=172;");
        DB::statement("UPDATE settings SET `order`=260 WHERE id=177;");
        DB::statement("UPDATE settings SET `order`=270 WHERE id=186;");
        DB::statement("UPDATE settings SET `order`=280 WHERE id=244;");
        DB::statement("UPDATE settings SET `order`=290 WHERE id=260;");
        DB::statement("UPDATE settings SET `order`=291 WHERE id=264;");
        DB::statement("UPDATE settings SET `order`=292 WHERE id=265;");
        DB::statement("UPDATE settings SET `order`=293 WHERE id=266;");
        DB::statement("UPDATE settings SET `order`=294 WHERE id=267;");

        // Updating `Feed` settings tabs order for more logic
        DB::statement("UPDATE settings SET `order`=1 WHERE id=12;");
        DB::statement("UPDATE settings SET `order`=20 WHERE id=15;");
        DB::statement("UPDATE settings SET `order`=10 WHERE id=16;");
        DB::statement("UPDATE settings SET `order`=30 WHERE id=114;");
        DB::statement("UPDATE settings SET `order`=70 WHERE id=146;");
        DB::statement("UPDATE settings SET `order`=80 WHERE id=151;");
        DB::statement("UPDATE settings SET `order`=40 WHERE id=155;");
        DB::statement("UPDATE settings SET `order`=90 WHERE id=178;");
        DB::statement("UPDATE settings SET `order`=100 WHERE id=229;");
        DB::statement("UPDATE settings SET `order`=50 WHERE id=261;");
        DB::statement("UPDATE settings SET `order`=60 WHERE id=262;");

        DB::table('settings')
            ->where('key', 'site.hide_identity_checks')
            ->update(['details' => '{
"on" : "On",
"off" : "Off",
"checked" : false,
"description" : "If enabled, the users ID check module link will be hidden from the menus."
}'
            ]);

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'site.hide_create_post_menu',
                    'display_name' => 'Hide create post menu',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "If enabled, the create post module link will be hidden from the menus. Useful if running the site on one-creator mode."
                        }',
                    'type' => 'checkbox',
                    'order' => 185,
                    'group' => 'Site',
                )
            )
        );

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'feed.hide_suggestions_slider',
                    'display_name' => 'Hide users suggestion slider',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "If enabled, the users suggestion slider will be hidden from the feed page."
                        }',
                    'type' => 'checkbox',
                    'order' => 35,
                    'group' => 'Feed',
                )
            )
        );

        Artisan::call('restoreWallets',['--dry-run'=>false, '--debug' => false]);

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
                'site.hide_create_post_menu',
                'feed.hide_suggestions_slider'
            ])
            ->delete();

        if (Schema::hasTable('wallets')) {
            Schema::table('wallets', function (Blueprint $table) {
                $table->float('paypal_balance')->nullable(true);
                $table->float('stripe_balance')->nullable(true);
            });
        }

    }
}
