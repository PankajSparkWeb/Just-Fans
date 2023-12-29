<?php

use App\Model\UserList;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class V630 extends Migration
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
                    'key' => 'referrals.disable_for_non_verified',
                    'display_name' => 'Disable referral system for non ID-checked users',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description" : "If this option is enabled, the referral system will only be available to ID-verified users."
                        }',
                    'type' => 'checkbox',
                    'order' => 1241,
                    'group' => 'Referrals',
                )
            )
        );

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'profiles.hide_non_verified_users_from_search',
                    'display_name' => 'Hide non ID-verified users from the search page',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description" : "If this option is enabled, non ID-verified users will not be shown on the search page."
                        }',
                    'type' => 'checkbox',
                    'order' => 120,
                    'group' => 'Profiles',
                )
            )
        );

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'profiles.disable_website_link_on_profile',
                    'display_name' => 'Disable website link from profile pages',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description" : "If this option is enabled, website links won\'t be shown in the profile section."
                        }',
                    'type' => 'checkbox',
                    'order' => 130,
                    'group' => 'Profiles',
                )
            )
        );


        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'feed.enable_post_description_excerpts',
                    'display_name' => 'Enable post description excerpts',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description" : "If this option is enabled, a \'Show more\' label is shown for posts with descriptions larger than a line."
                        }',
                    'type' => 'checkbox',
                    'order' => 5,
                    'group' => 'Feed',
                )
            )
        );

        UserList::where('type', 'followers')->update(['type' => 'following', 'name' => 'Following']);

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
                'referrals.disable_for_non_verified',
                'profiles.hide_non_verified_users_from_search',
                'profiles.disable_website_link_on_profile',
                'feed.enable_post_description_excerpts',
            ])
            ->delete();
        UserList::where('type', 'following')->update(['type' => 'followers', 'name' => 'Followers']);

    }
}
