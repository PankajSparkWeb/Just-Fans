<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use TCG\Voyager\Models\Setting;

class SettingDBFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixSettings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a .sql template to fix broken setting entries';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Checks for PHP or JS errors and generates a report.
     *
     * @return mixed
     */
    public function handle()
    {
        $settings = Setting::all();
        $fixFileName = storage_path() . '/settings_fix.sql';
        $query = '';
        foreach ($settings as $setting){
            $query .= "UPDATE settings SET `key` = '{$setting->key}', `group` = '{$setting->group}' WHERE id = {$setting->id}; \r\n";
        }
        file_put_contents($fixFileName, $query);
        echo '[*]['.date('H:i:s')."] Settings table fix create under $fixFileName\r\n";
        return 0;
    }

}
