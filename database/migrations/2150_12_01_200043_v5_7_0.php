<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class V570 extends Migration
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
                    'key' => 'payments.stripe_oxxo_provider_enabled',
                    'display_name' => 'Allow OXXO',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Allow OXXO payment provider through Stripe. This will be shown as different option in checkout"
                        }',
                    'type' => 'checkbox',
                    'order' => 100,
                    'group' => 'Payments',
                )
            )
        );

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'payments.min_ppv_content_price',
                    'display_name' => 'Min PPV content price',
                    'value' => '1',
                    'type' => 'text',
                    'order' => 100,
                    'group' => 'Payments',
                    'details' => '{
                        "description": "Applies to paid posts, messages and streams."
                        }',
                ),
                array (
                    'key' => 'payments.max_ppv_content_price',
                    'display_name' => 'Max PPV content price',
                    'value' => '500',
                    'type' => 'text',
                    'order' => 110,
                    'group' => 'Payments',
                    'details' => '{
                        "description": "Applies to paid posts, messages and streams."
                        }',
                ),

            )
        );


        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'social-media.reddit_url',
                    'display_name' => 'Reddit',
                    'value' => '',
                    'details' => NULL,
                    'type' => 'text',
                    'order' => 90,
                    'group' => 'Social media',
                )
            )
        );

        // Settings updates
        DB::table('settings')
            ->where('key', 'payments.deposit_max_amount')
            ->update(['details' => '{}']);

        // Settings updates
        DB::table('settings')
            ->where('key', 'payments.deposit_min_amount')
            ->update(['details' => '{}']);

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
                'payments.stripe_oxxo_provider_enabled',
                'payments.min_ppv_content_price',
                'payments.max_ppv_content_price',
                'social-media.reddit_url'
            ])
            ->delete();
    }
}
