<?php

namespace App\Http\Controllers\Voyager;

use App\Providers\EmailsServiceProvider;
use App\Providers\SettingsServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerSettingsController as BaseVoyagerSettingsController;

class VoyagerSettingsController extends BaseVoyagerSettingsController
{
    public function index()
    {
        // Check permission
        $this->authorize('browse', Voyager::model('Setting'));

        $data = Voyager::model('Setting')->orderBy('order', 'ASC')->get();

        $settings = [];
        $settings[__('voyager::settings.group_general')] = [];
        foreach ($data as $d) {
            if ($d->group == '' || $d->group == __('voyager::settings.group_general')) {
                $settings[__('voyager::settings.group_general')][] = $d;
            } else {
                $settings[$d->group][] = $d;
            }
        }
        if (count($settings[__('voyager::settings.group_general')]) == 0) {
            unset($settings[__('voyager::settings.group_general')]);
        }

        $groups_data = Voyager::model('Setting')->select('group')->distinct()->get();
        $groups = [];
        foreach ($groups_data as $group) {
            if ($group->group != '') {
                $groups[] = $group->group;
            }
        }

        $active = (request()->session()->has('setting_tab')) ? request()->session()->get('setting_tab') : old('setting_tab', key($settings));

        // Checking if storage settings are valid, if they got changed
        $storageErrorMessage = false;
        if(request()->session()->get('changedStorageDriver')) {
            // Checking if newly saved storage settings are valid
            try {
                Storage::files();
            } catch (\Exception $e) {
                SettingsServiceProvider::setDefaultStorageDriver('public');
                Voyager::model('Setting')->where('id', 95)->update(['value' => 'public']);
                $storageErrorMessage = $e->getMessage();
            }
        }

        // Checking if email settings are valid, if they got changed
        $emailsErrorMessage = false;
        if(request()->session()->get('changedSEmailDriver')) {
            // Checking if email settings are valid
            try {
                EmailsServiceProvider::sendGenericEmail(
                    [
                        'email' => 'smtp-test'.rand(1000,9999).'@mailinator.com',
                        'subject' => __('SMTP Testing'),
                        'title' => __('SMTP Testing'),
                        'content' => __('SMTP Testing'),
                        'button' => [
                            'text' => __('SMTP Testing'),
                            'url' => route('my.settings', ['type' => 'subscriptions']),
                        ],
                    ]
                );
            }catch (\Exception $e){
                SettingsServiceProvider::setDefaultStorageDriver('public');
                Voyager::model('Setting')->where('key','emails.driver')->update(['value' => 'log']);
                $emailsErrorMessage = $e->getMessage();
            }
        }

        return Voyager::view('voyager::settings.index', compact('settings', 'groups', 'active', 'emailsErrorMessage' , 'storageErrorMessage'));
    }

    public function update(Request $request)
    {
        // Check permission
        $this->authorize('edit', Voyager::model('Setting'));

        $settings = Voyager::model('Setting')->all();
        $changedStorageDriver = false;
        $changedSEmailDriver = false;
        foreach ($settings as $setting) {
            $content = $this->getContentBasedOnType($request, 'settings', (object) [
                'type'    => $setting->type,
                'field'   => str_replace('.', '_', $setting->key),
                'group'   => $setting->group,
            ], $setting->details);

            if ($setting->type == 'image' && $content == null) {
                continue;
            }

            if ($setting->type == 'file' && $content == null) {
                continue;
            }

            $key = preg_replace('/^'.Str::slug($setting->group).'./i', '', $setting->key);

            $setting->group = $request->input(str_replace('.', '_', $setting->key).'_group');
            $setting->key = implode('.', [Str::slug($setting->group), $key]);
            $setting->value = $content;
            if($setting->key == 'storage.driver' && getSetting('storage.driver') != $setting->value){
                $changedStorageDriver = true;
            }
            if($setting->key == 'emails.driver' && getSetting('emails.driver') != $setting->value){
                $changedSEmailDriver = true;
            }
            $setting->save();
        }


        request()->flashOnly('setting_tab');

        return back()->with([
            'message'    => __('voyager::settings.successfully_saved'),
            'alert-type' => 'success',
            'changedStorageDriver' => $changedStorageDriver,
            'changedSEmailDriver' => $changedSEmailDriver
        ]);
    }

}
