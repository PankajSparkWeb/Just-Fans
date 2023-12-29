<?php

namespace App\Providers;

use App\Model\Attachment;
use App\Model\PublicPage;
use App\Model\Wallet;
use App\User;
use GrahamCampbell\Markdown\Facades\Markdown;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Jenssegers\Agent\Agent;
use Mews\Purifier\Facades\Purifier;
use Ramsey\Uuid\Uuid;
use Cookie;

class GenericHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Check if user meets all ID verification steps.
     *
     * @return bool
     */
    public static function isUserVerified()
    {
        if (
            (Auth::user()->verification && Auth::user()->verification->status == 'verified') &&
            Auth::user()->birthdate &&
            Auth::user()->email_verified_at
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $contactUserID - Contacted users
     * @param $userID - User sending the message
     * @return bool
     */
    public static function hasUserBlocked($contactUserID, $userID){
        $contactUser = User::where('id',$contactUserID)->first();
        $blockedUsers = ListsHelperServiceProvider::getListMembers($contactUser->lists->firstWhere('type', 'blocked')->id);
        if(in_array($userID, $blockedUsers)){
            return true;
        };
        return false;
    }

    /**
     * Creates a default wallet for a user.
     * @param $user
     */
    public static function createUserWallet($user)
    {
        try {
            $userWallet = Wallet::query()->where('user_id', $user->id)->first();
            if ($userWallet == null) {
                // generate unique id for wallet
                do {
                    $id = Uuid::uuid4()->getHex();
                } while (Wallet::query()->where('id', $id)->first() != null);

                $balance = 0.0;
                if(getSetting('profiles.default_wallet_balance_on_register') && getSetting('profiles.default_wallet_balance_on_register') != 0){
                    $balance = getSetting('profiles.default_wallet_balance_on_register');
                }
                Wallet::create([
                    'id' => $id,
                    'user_id' => $user->id,
                    'total' => $balance,
                ]);
            }
        } catch (\Exception $exception) {
            Log::error('User wallet creation error: '.$exception->getMessage());
        }
    }

    /**
     * Static function that handles remote storage drivers
     *
     * @param $value
     * @return string
     */
    public static function getStorageAvatarPath($value){
        if($value && $value !== config('voyager.user.default_avatar', '/img/default-avatar.png')){
            if (getSetting('storage.driver') == 's3') {
                if (getSetting('storage.aws_cdn_enabled') && getSetting('storage.aws_cdn_presigned_urls_enabled')) {
                    $fileUrl = AttachmentServiceProvider::signAPrivateDistributionPolicy(
                        'https://'.getSetting('storage.cdn_domain_name').'/'.$value
                    );
                } elseif (getSetting('storage.aws_cdn_enabled')) {
                    $fileUrl = 'https://'.getSetting('storage.cdn_domain_name').'/'.$value;
                } else {
                    $fileUrl = 'https://'.getSetting('storage.aws_bucket_name').'.s3.'.getSetting('storage.aws_region').'.amazonaws.com/'.$value;
                }
                return $fileUrl;
            }
            elseif(getSetting('storage.driver') == 'wasabi' || getSetting('storage.driver') == 'do_spaces'){
                return Storage::url($value);
            }
            elseif(getSetting('storage.driver') == 'minio'){
                return rtrim(getSetting('storage.minio_endpoint'), '/').'/'.getSetting('storage.minio_bucket_name').'/'.$value;
            }
            elseif(getSetting('storage.driver') == 'pushr'){
                return rtrim(getSetting('storage.pushr_cdn_hostname'), '/').'/'.$value;
            }
            else{
                return Storage::disk('public')->url($value);
            }
        }else{
            return str_replace('storage/','',asset(config('voyager.user.default_avatar', '/img/default-avatar.png')));
        }
    }

    /**
     * Static function that handles remote storage drivers
     *
     * @param $value
     * @return string
     */
    public static function getStorageCoverPath($value){
        if($value){
            if (getSetting('storage.driver') == 's3') {
                if (getSetting('storage.aws_cdn_enabled') && getSetting('storage.aws_cdn_presigned_urls_enabled')) {
                    $fileUrl = AttachmentServiceProvider::signAPrivateDistributionPolicy(
                        'https://'.getSetting('storage.cdn_domain_name').'/'.$value
                    );
                } elseif (getSetting('storage.aws_cdn_enabled')) {
                    $fileUrl = 'https://'.getSetting('storage.cdn_domain_name').'/'.$value;
                } else {
                    $fileUrl = 'https://'.getSetting('storage.aws_bucket_name').'.s3.'.getSetting('storage.aws_region').'.amazonaws.com/'.$value;
                }
                return $fileUrl;
            }
            elseif(getSetting('storage.driver') == 'wasabi' || getSetting('storage.driver') == 'do_spaces'){
                return Storage::url($value);
            }
            elseif(getSetting('storage.driver') == 'minio'){
                return rtrim(getSetting('storage.minio_endpoint'), '/').'/'.getSetting('storage.minio_bucket_name').'/'.$value;
            }
            elseif(getSetting('storage.driver') == 'pushr'){
                return rtrim(getSetting('storage.pushr_cdn_hostname'), '/').'/'.$value;
            }
            else{
                return Storage::disk('public')->url($value);
            }
        }else{
            return asset(config('voyager.user.default_cover', '/img/default-cover.png'));
        }
    }

    /**
     * Helper to detect mobile usage
     * @return bool
     */
    public static function isMobileDevice(){
        $agent = new Agent();
        return $agent->isMobile();
    }

    /**
     * Returns true if email enforce is not enabled or if is set to true and user is verified
     * @return bool
     */
    public static function isEmailEnforcedAndValidated(){
        return ((Auth::check() && Auth::user()->email_verified_at) || (Auth::check() && !getSetting('site.enforce_email_validation')));
    }

    public static function parseProfileMarkdownBio($bio){
        if(getSetting('profiles.allow_profile_bio_markdown')){
            $parsedOutput = Purifier::clean(Markdown::convert($bio)->getContent());
            return $parsedOutput;
        }
        return $bio;
    }

    /**
     * Fetches list of all public pages to be show in footer
     * @return mixed
     */
    public static function getFooterPublicPages(){
        $pages = PublicPage::where('shown_in_footer', 1)->orderBy('page_order')->get();
        return $pages;
    }

    /**
     * Verifies if admin added a minimum posts limit for creators to earn money
     * @param $user
     * @return bool
     */
    public static function creatorCanEarnMoney($user) {
        if(intval(getSetting("compliance.minimum_posts_until_creator")) > 0 && count($user->posts) < intval(getSetting('compliance.minimum_posts_until_creator'))){
            return false;
        }
        if(getSetting('compliance.monthly_posts_before_inactive') && !$user->is_active_creator){
            return false;
        }
        return true;
    }

    /**
     * Returns the preferred user local
     * @return \Illuminate\Config\Repository|mixed|null
     */
    public static function getPreferredLanguage(){
        // Defaults
        if (! Session::has('locale')) {
            if (InstallerServiceProvider::checkIfInstalled()) {
                return getSetting('site.default_site_language');
            } else {
                return Config::get('app.locale');
            }
        }
        // If user has locale setting, use that one
        if (isset(Auth::user()->settings['locale'])) {
           return Auth::user()->settings['locale'];
        }
        return getSetting('site.default_site_language');
    }

    /**
     * Fetches the default OGMeta image to be used (except for profile)
     * @return \Illuminate\Config\Repository|mixed|string|null
     */
    public static function getOGMetaImage(){
        if(getSetting('site.default_og_image')){
            return getSetting('site.default_og_image');
        }
        return asset('img/logo-black.png');
    }

    /**
     * Gets site direction. If rtl cookie not set, defaults to site setting
     * @return \Illuminate\Config\Repository|mixed|null
     */
    public static function getSiteDirection(){
        if(is_null(Cookie::get('app_rtl'))){
            return getSetting('site.default_site_direction');
        }
        return Cookie::get('app_rtl');
    }

}
