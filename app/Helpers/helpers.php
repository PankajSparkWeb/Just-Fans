<?php

use App\Providers\GenericHelperServiceProvider;
use App\Providers\InstallerServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

if (!function_exists('get_users_learned_posts_interests')) {
    function get_users_learned_posts_interests()
    {
        $interestPostCounts = [];
        $totalPostsInAllInterest = 0;
        if (Auth::check()) {
            $user = Auth::user();
            $learnedPosts = $user->learnedPost;
            // Count the total number of learned posts
            $totalLearnedPosts = $learnedPosts->count();
            // Access interests for each learned post
            foreach ($learnedPosts as $learnedPost) {
                $interests = $learnedPost->post->interests->pluck('name')->unique()->toArray();
                // Increment the count for each interest
                foreach ($interests as $interest) {
                    if (!isset($interestPostCounts[$interest])) {
                        $interestPostCounts[$interest] = 1;
                    } else {
                        $interestPostCounts[$interest]++;
                    }
                    $totalPostsInAllInterest++;
                }
            }
            
            // Calculate the percentage for each interest
            foreach ($interestPostCounts as $interest => $count) {
                $percentage = ($count / $totalPostsInAllInterest) * 100;
                //$percentage = ($count / $totalLearnedPosts) * 100;
                $interestPostCounts[$interest] = [
                    'total_posts' => $count,
                    'percentage' => number_format($percentage, 2) . '%',
                ];
            }
            // Order the interests by the total posts count in descending order
            arsort($interestPostCounts);
        }
        return $interestPostCounts;
    }
}

if (!function_exists('getSetting')) {
    function getSetting($key, $default = null)
    {
        try {
            $dbSetting = TCG\Voyager\Facades\Voyager::setting($key, $default);
        } catch (Exception $exception) {
            $dbSetting = null;
        }

        $configSetting = config('app.' . $key);
        if ($dbSetting) {
            // If voyager setting is file type, extract the value only
            if (is_string($dbSetting) && strpos($dbSetting, 'download_link')) {
                $file = json_decode($dbSetting);
                if ($file) {
                    $file = Storage::disk(config('filesystems.defaultFilesystemDriver'))->url(str_replace('\\', '/', $file[0]->download_link));
                }
                return $file;
            }

            return $dbSetting;
        }
        if ($configSetting) {
            return $configSetting;
        }

        return $default;
    }
}

function getLockCode()
{
    if (session()->get(InstallerServiceProvider::$lockCode) == env('APP_KEY')) {
        return true;
    } else {
        return false;
    }
}

function setLockCode($code)
{
    $sessData = [];
    $sessData[$code] = env('APP_KEY');
    session($sessData);
    return true;
}

function getUserAvatarAttribute($a)
{
    return GenericHelperServiceProvider::getStorageAvatarPath($a);
}

function getLicenseType()
{
    $licenseType = 'Unlicensed';
    if (file_exists(storage_path('app/installed'))) {
        $licenseV = json_decode(file_get_contents(storage_path('app/installed')));
        if (isset($licenseV->data) && isset($licenseV->data->license)) {
            $licenseType = $licenseV->data->license;
        }
    }
    return $licenseType;
}

function handledExec($command, $throw_exception = true)
{
    $result = exec('(' . $command . ')', $output, $return_code);
    if ($throw_exception) {
        if (($result === false) || ($return_code !== 0)) {
            throw new Exception('Error processing command: ' . $command . "\n\n" . implode("\n", $output) . "\n\n");
        }
    }
    return implode("\n", $output);
}

function checkMysqlndForPDO()
{
    $dbHost = env('DB_HOST');
    $dbUser = env('DB_USERNAME');
    $dbPass = env('DB_PASSWORD');
    $dbName = env('DB_DATABASE');

    $pdo = new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName, $dbUser, $dbPass);
    if (strpos($pdo->getAttribute(PDO::ATTR_CLIENT_VERSION), 'mysqlnd') !== false) {
        return true;
    }
    return false;
}

function checkForMysqlND()
{
    if (extension_loaded('mysqlnd')) {
        return true;
    }
    return false;
}
