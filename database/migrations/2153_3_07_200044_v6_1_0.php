<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V610 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Moved to profile page
        DB::table('settings')
            ->where('key', 'site.default_profile_type_on_register')
            ->update([
                'group'=>'Profiles',
                'key' => 'profiles.default_profile_type_on_register',
                'details' => '{
                            "default" : "paid",
                            "options" : {
                                "paid": "Paid profile",
                                "free": "Free profile",
                                "open": "Open profile"
                            },
                            "description": "Paid = Subscription locked content, Free = Follow locked, Open = Content is available to un-registered users. PPV content is locked for all scenarios."
                            }',
                'order' => 20,

            ]);


        DB::table('settings')
            ->where('key', 'site.default_user_privacy_setting_on_register')
            ->update([
                'group'=>'Profiles',
                'key' => 'profiles.default_user_privacy_setting_on_register',
                'details' => '{
                    "default" : "public",
                    "options" : {
                    "public": "Public profile",
                    "private": "Private profile"
                    },
                    "description": "If private, the profiles will be hidden from search results."
                }',
                'order' => 30,

            ]);

        DB::table('settings')
            ->where('key', 'site.allow_users_enabling_open_profiles')
            ->update([
                'group'=>'Profiles',
                'key' => 'profiles.allow_users_enabling_open_profiles',
                'details' => '{
                        "true" : "Off",
                        "false" : "Off",
                        "checked" : false,
                        "description": "If enabled, users will be able to make their profiles open so anyone can their (non PPV) content."
                    }',
                'order' => 10,

            ]);

        DB::table('settings')
            ->where('key', 'site.allow_profile_bio_markdown')
            ->update([
                'group'=>'Profiles',
                'key' => 'profiles.allow_profile_bio_markdown',
                'order' => 40,
            ]);


        DB::table('settings')
            ->where('key', 'site.allow_profile_bio_markdown_links')
            ->update([
                'group'=>'Profiles',
                'key' => 'profiles.allow_profile_bio_markdown_links',
                'order' => 50,
            ]);

        DB::table('settings')
            ->where('key', 'site.disable_profile_bio_excerpt')
            ->update([
                'group'=>'Profiles',
                'key' => 'profiles.disable_profile_bio_excerpt',
                'order' => 60,
            ]);

        DB::table('settings')
            ->where('key', 'site.max_profile_bio_length')
            ->update([
                'group'=>'Profiles',
                'key' => 'profiles.max_profile_bio_length',
                'order' => 70,
            ]);

        DB::table('settings')
            ->where('key', 'site.allow_gender_pronouns')
            ->update([
                'group'=>'Profiles',
                'key' => 'profiles.allow_gender_pronouns',
                'order' => 80,
            ]);

        DB::table('settings')
            ->where('key', 'site.allow_profile_qr_code')
            ->update([
                'group'=>'Profiles',
                'key' => 'profiles.allow_profile_qr_code',
                'order' => 90,
            ]);

        DB::table('settings')
            ->where('key', 'site.default_wallet_balance_on_register')
            ->update([
                'group'=>'Profiles',
                'key' => 'profiles.default_wallet_balance_on_register',
                'order' => 33,
            ]);

        DB::table('settings')
            ->where('key', 'feed.default_users_to_follow')
            ->update([
                'group'=>'Profiles',
                'key' => 'profiles.default_users_to_follow',
                'order' => 36,
            ]);

        // Ordering updates
        DB::table('settings')
            ->where('key', 'feed.feed_posts_per_page')
            ->update([
                'order'=>1,
            ]);

        DB::table('settings')
            ->where('key', 'feed.min_post_description')
            ->update([
                'order'=>5,
            ]);

        // Naming updates
        DB::table('settings')
            ->where('key', 'feed.suggestions_skip_empty_profiles')
            ->update([
                'display_name'=>'Skip empty profiles out of the suggestions box',
            ]);

        DB::table('settings')
            ->where('key', 'feed.suggestions_skip_unverified_profiles')
            ->update([
                'display_name'=>'Skip unverified profiles out of the suggestions box',
            ]);

        DB::table('settings')
            ->where('key', 'feed.suggestions_use_featured_users_list')
            ->update([
                'display_name'=>'Use featured users for the suggestions box',
                ]);

        DB::table('settings')
            ->where('key', 'feed.suggestions_use_featured_users_list')
            ->update([
                'display_name'=>'Use featured users for the suggestions box',
            ]);

        DB::table('settings')
            ->where('key', 'feed.hide_suggestions_slider')
            ->update([
                'display_name'=>'Hide the users suggestion box',
            ]);

        DB::table('settings')
            ->where('key', 'feed.allow_gallery_zoom')
            ->update([
                'details'=>'{
                    "description": "If enabled, high-res photos will feature a zoom in/out option when previewing posts media."
                }',
            ]);

        DB::table('settings')
            ->where('key', 'feed.disable_right_click')
            ->update([
                'display_name'=>'Disable right click on posts',
                'details'=>'{
                    "description": "If enabled, right click on posts media elements will be disabled, alongside view source shortcut."
                }',
            ]);


        // Feed settings order
        DB::statement("UPDATE `settings` SET `order`='6' WHERE  `id`=114;");
        DB::statement("UPDATE `settings` SET `order`='7' WHERE  `id`=151;");
        DB::statement("UPDATE `settings` SET `order`='9' WHERE  `id`=355;");
        DB::statement("UPDATE `settings` SET `order`='80' WHERE  `id`=16;");
        DB::statement("UPDATE `settings` SET `order`='90' WHERE  `id`=15;");

        // New OG:meta image setting
        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'site.default_og_image',
                    'display_name' => 'Default site og:image',
                    'value' => '',
                    'details' => '{"description" : "The image to be used when sharing the site over social media sites."}',
                    'type' => 'file',
                    'order' => 65,
                    'group' => 'Site',
                )
            )
        );

        DB::table('settings')->insert(array(
            0 => array(
                'key' => 'profiles.enable_new_post_notification_setting',
                'display_name' => 'Enable new post notification setting',
                'value' => '0',
                'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : true,
                        "description": "If enabled, subscribers will be allowed to manage their (new) posts email notifications, while creators can choose to notify them or not, when creating new posts."
                    }',
                'type' => 'checkbox',
                'order' => 100,
                'group' => 'Profiles',

            )
        ));

        DB::table('settings')->insert(array(
            0 => array(
                'key' => 'profiles.default_new_post_notification_setting',
                'display_name' => 'Default new post notification setting on user register',
                'value' => '0',
                'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : true,
                        "description": "The default value for whether the user should receive emails when new content has been posted."
                    }',
                'type' => 'checkbox',
                'order' => 110,
                'group' => 'Profiles',

            )
        ));


        // New abstract api security settings
        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'security.enforce_email_valid_check',
                    'display_name' => 'Enforce email deliverability check on register',
                    'value' => NULL,
                    'details' => '{
"on" : "On",
"off" : "Off",
"checked" : false,
"description": "If enabled, the emails used for signing up, are checked to see if they\'re valid ones."
}',
                    'type' => 'checkbox',
                    'order' => 83,
                    'group' => 'Security',

                ),
            )
        );

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'security.email_abstract_api_key',
                    'display_name' => 'Email Validation API key (Abstract API)',
                    'value' => '',
                    'type' => 'text',
                    'order' => 84,
                    'group' => 'Security'
                )
            )
        );

        // AI Settings
        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'ai.open_ai_enabled',
                    'display_name' => 'OpenAI Enabled',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Allow creators to use OpenAI to suggest a post or a profile description"
                        }',
                    'type' => 'checkbox',
                    'order' => 10,
                    'group' => 'AI',
                ),
                array (
                    'key' => 'ai.open_ai_api_key',
                    'display_name' => 'OpenAI Api Key',
                    'value' => '',
                    'details' => NULL,
                    'type' => 'text',
                    'order' => 20,
                    'group' => 'AI',
                ),
                array (
                    'key' => 'ai.open_ai_completion_max_tokens',
                    'display_name' => 'OpenAI Max Tokens',
                    'value' => '100',
                    'details' => '{
                        "description": "Dictates how long the suggestion should be. E.g. 1000 tokens is about 750 words. (shouldn`t exceed 2048 tokens)"
                    }',
                    'type' => 'text',
                    'order' => 30,
                    'group' => 'AI',

                ),
                array (
                    'key' => 'ai.open_ai_completion_temperature',
                    'display_name' => 'OpenAI Temperature',
                    'value' => '1',
                    'details' => '{
                        "description": "What sampling temperature to use, between 0 and 2. Higher values like 0.8 will make the output more random, while lower values like 0.2 will make it more focused and deterministic."
                    }',
                    'type' => 'text',
                    'order' => 40,
                    'group' => 'AI',

                )
            )
        );

        Schema::table('creator_offers', function($table) {
            $table->float('old_profile_access_price')->default(5)->nullable()->change();
            $table->float('old_profile_access_price_6_months')->default(5)->nullable()->change();
            $table->float('old_profile_access_price_12_months')->default(5)->nullable()->change();
            $table->float('old_profile_access_price_3_months')->after('old_profile_access_price')->default(5)->nullable();
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
            ->where('key', 'profiles.default_profile_type_on_register')
            ->update([
                'group'=>'Site',
                'key' => 'site.default_profile_type_on_register'
            ]);

        DB::table('settings')
            ->where('key', 'profiles.default_user_privacy_setting_on_register')
            ->update([
                'group'=>'Site',
                'key' => 'site.default_user_privacy_setting_on_register'
            ]);

        DB::table('settings')
            ->where('key', 'profiles.allow_users_enabling_open_profiles')
            ->update([
                'group'=>'Site',
                'key' => 'site.allow_users_enabling_open_profiles'
            ]);

        DB::table('settings')
            ->where('key', 'profiles.allow_profile_bio_markdown')
            ->update([
                'group'=>'Site',
                'key' => 'site.allow_profile_bio_markdown'
            ]);

        DB::table('settings')
            ->where('key', 'profiles.allow_profile_bio_markdown_links')
            ->update([
                'group'=>'Site',
                'key' => 'site.allow_profile_bio_markdown_links'
            ]);

        DB::table('settings')
            ->where('key', 'profiles.disable_profile_bio_excerpt')
            ->update([
                'group'=>'Site',
                'key' => 'site.disable_profile_bio_excerpt'
            ]);

        DB::table('settings')
            ->where('key', 'profiles.max_profile_bio_length')
            ->update([
                'group'=>'Site',
                'key' => 'site.max_profile_bio_length'
            ]);

        DB::table('settings')
            ->where('key', 'profiles.allow_gender_pronouns')
            ->update([
                'group'=>'Site',
                'key' => 'site.allow_gender_pronouns'
            ]);

        DB::table('settings')
            ->where('key', 'profiles.allow_profile_qr_code')
            ->update([
                'group'=>'Site',
                'key' => 'site.allow_profile_qr_code'
            ]);

        DB::table('settings')
            ->where('key', 'profiles.default_wallet_balance_on_register')
            ->update([
                'group'=>'Site',
                'key' => 'site.default_wallet_balance_on_register'
            ]);

        DB::table('settings')
            ->where('key', 'profiles.default_users_to_follow')
            ->update([
                'group'=>'Feed',
                'key' => 'feed.default_users_to_follow'
            ]);

        DB::table('settings')
            ->whereIn('key', [
                'site.default_og_image',
                'profiles.enable_new_post_notification_setting',
                'profiles.default_new_post_notification_setting',
                'security.enforce_email_valid_check',
                'security.email_abstract_api_key',
                'ai.open_ai_enabled',
                'ai.open_ai_api_key',
                'ai.open_ai_completion_max_tokens',
                'ai.open_ai_completion_temperature',
            ])
            ->delete();

        Schema::table('creator_offers', function($table) {
            $table->dropColumn('old_profile_access_price_3_months');
        });

    }
}
