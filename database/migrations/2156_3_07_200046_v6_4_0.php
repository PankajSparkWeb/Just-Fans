<?php

use App\Model\UserList;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

class V640 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Referrals
        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'referrals.auto_follow_the_user',
                    'display_name' => 'Auto-follow the user that have referred the newly register account',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description" : "If this option is enabled, the newly created accounts will auto-follow the user that have referred them."
                        }',
                    'type' => 'checkbox',
                    'order' => 1255,
                    'group' => 'Referrals',
                )
            )
        );

        // Referrals re-ordering
        DB::statement("UPDATE `settings` SET `order`='1245' WHERE  `key`='referrals.enabled';");
        DB::statement("UPDATE `settings` SET `order`='1250' WHERE  `key`='referrals.disable_for_non_verified';");
        DB::statement("UPDATE `settings` SET `order`='1255' WHERE  `key`='referrals.auto_follow_the_user';");
        DB::statement("UPDATE `settings` SET `order`='1260' WHERE  `key`='referrals.fee_percentage';");
        DB::statement("UPDATE `settings` SET `order`='1265' WHERE  `key`='referrals.apply_for_months';");
        DB::statement("UPDATE `settings` SET `order`='1270' WHERE  `key`='referrals.fee_limit';");
        DB::statement("UPDATE `settings` SET `order`='1275' WHERE  `key`='referrals.referrals_default_link_page';");

        // New locale setting
        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'site.use_browser_language_if_available',
                    'display_name' => 'Use preferred browser locale, if available',
                    'value' => 1,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description" : "If this option is enabled, if the user browser locale is available as a language, that will be used by default."
                    }',
                    'type' => 'checkbox',
                    'order' => 125,
                    'group' => 'Site',
                )
            )
        );

        // New
        if (Schema::hasTable('settings')) {

            // New settings
            \DB::table('settings')->insert(array(
                array(
                    'key' => 'storage.pushr_access_key',
                    'display_name' => 'Pushr Access Key',
                    'value' => '',
                    'type' => 'text',
                    'order' => 130,
                    'group' => 'Storage',
                )));

            \DB::table('settings')->insert(array(
                array(
                    'key' => 'storage.pushr_secret_key',
                    'display_name' => 'Pushr Secret Key',
                    'value' => '',
                    'type' => 'text',
                    'order' => 140,
                    'group' => 'Storage',
                )));

            \DB::table('settings')->insert(array(
                array(
                    'key' => 'storage.pushr_cdn_hostname',
                    'display_name' => 'Pushr CDN Hostname',
                    'value' => '',
                    'type' => 'text',
                    'order' => 180,
                    'group' => 'Storage',
                    'details' => '{
                        "description" : "This field must contain the https:// prefix."
                        }',
                )));

            \DB::table('settings')->insert(array(
                array(
                    'key' => 'storage.pushr_bucket_name',
                    'display_name' => 'Pushr Bucket',
                    'value' => '',
                    'type' => 'text',
                    'order' => 160,
                    'group' => 'Storage',
                )));

            \DB::table('settings')->insert(array(
                array(
                    'key' => 'storage.pushr_endpoint',
                    'display_name' => 'Pushr S3 Endpoint',
                    'value' => '',
                    'type' => 'text',
                    'order' => 170,
                    'group' => 'Storage',
                )));

            // Settings updates
            DB::table('settings')
                ->where('key', 'storage.driver')
                ->update(['details' => '{
"default" : "public",
"options" : {
"public": "Local",
"s3": "S3",
"wasabi": "Wasabi",
"do_spaces": "DigitalOcean Spaces",
"minio": "Minio",
"pushr": "Pushr"
}
}'
                ]);

        }

        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->boolean('is_pinned')->after('expire_date')->default(0);
            });
        }

        if (Schema::hasTable('taxes')) {
            Schema::table('taxes', function (Blueprint $table) {
                $table->boolean('hidden')->default(0);
            });
        }

        \DB::table('settings')->insert(array(
            array(
                'key' => 'payments.offline_payments_custom_message_box',
                'display_name' => 'Offline payments details box',
                'value' => '',
                'details' => '{"description":"This field can be used to add a custom info box, where users can be informed on any alternative offline payments you may accept."}',
                'type' => 'code_editor',
                'order' => 100,
                'group' => 'Payments',
            )));


        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'payments.offline_payments_make_notes_field_mandatory',
                    'display_name' => 'Make the notes field mandatory',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false
                        }',
                    'type' => 'checkbox',
                    'order' => 110,
                    'group' => 'Payments',
                )
            )
        );

        \DB::table('settings')->insert(array(
            array(
                'key' => 'payments.offline_payments_minimum_attachments_required',
                'display_name' => 'Minimum file attachments required',
                'value' => '1',
                'type' => 'text',
                'order' => 120,
                'group' => 'Payments',
            )));

        DB::statement("UPDATE `settings` SET `order`='30' WHERE  `key`='payments.allow_manual_payments';");
        DB::statement("UPDATE `settings` SET `order`='40' WHERE  `key`='payments.offline_payments_iban';");
        DB::statement("UPDATE `settings` SET `order`='50' WHERE  `key`='payments.offline_payments_swift';");
        DB::statement("UPDATE `settings` SET `order`='60' WHERE  `key`='payments.offline_payments_owner';");
        DB::statement("UPDATE `settings` SET `order`='70' WHERE  `key`='payments.offline_payments_bank_name';");
        DB::statement("UPDATE `settings` SET `order`='80' WHERE  `key`='payments.offline_payments_account_number';");
        DB::statement("UPDATE `settings` SET `order`='90' WHERE  `key`='payments.offline_payments_routing_number';");

        DB::table('settings')
            ->where('key', 'payments.offline_payments_iban')
            ->update([
                'details' => '{"description" : "If left empty, the bank transfer dialog, will be hidden, so custom providers can be used via \'Offline payments details box\'."}',
        ]);

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
                'referrals.auto_follow_the_user',
                'site.use_browser_language_if_available',
            ])
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'storage.pushr_access_key')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'storage.pushr_secret_key')
            ->delete();

//        // Delete old
        DB::table('settings')
            ->where('key', 'storage.pushr_cdn_hostname')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'storage.pushr_endpoint')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'storage.pushr_bucket_name')
            ->delete();

        // Settings updates
        DB::table('settings')
            ->where('key', 'storage.driver')
            ->update(['details' => '{
"default" : "public",
"options" : {
"public": "Local",
"s3": "S3",
"wasabi": "Wasabi",
"do_spaces": "DigitalOcean Spaces",
"minio": "Minio"
}
}'
            ]);

        Schema::table('posts', function($table) {
            $table->dropColumn('is_pinned');
        });

        Schema::table('taxes', function($table) {
            $table->dropColumn('hidden');
        });


        // Delete old
        DB::table('settings')
            ->where('key', 'payments.offline_payments_custom_message_box')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'payments.offline_payments_make_notes_field_mandatory')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'payments.offline_payments_minimum_attachments_required')
            ->delete();

    }
}
