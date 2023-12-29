<?php

namespace App\Console\Commands;

use App\Model\Transaction;
use App\Model\Wallet;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PayPal\Api\Transactions;
use Symfony\Component\Process\Process;
use TCG\Voyager\Models\Setting;

class UsersWalletsRestore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restoreWallets {--id=*} {--dry-run=true} {--debug=true}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-generates user wallets entries based on transactions history';

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
        $customIDs = $this->option('id');
        $additionalSQL = '';
        if($customIDs){
            $additionalSQL = ' WHERE moneyr.user_id IN ('.implode($customIDs).')';
        }

        // Fetch summaries of users that have had at least one incoming transaction
        $query = "
            SELECT moneyr.user_id, moneyReceived, moneyOut, moneyGiven FROM
            (
                SELECT recipient_user_id AS user_id, SUM(amount) AS moneyReceived FROM transactions t
                WHERE  t.status = 'approved'
                AND t.type NOT IN ('withdrawal')
                GROUP BY recipient_user_id
            ) AS moneyr
            LEFT JOIN
            (
                SELECT user_id, SUM(amount) AS moneyOut FROM withdrawals t
                WHERE  t.status = 'approved'
                GROUP BY user_id
            ) AS moneyo
            ON moneyr.user_id = moneyo.user_id
            LEFT JOIN
            (
                SELECT sender_user_id AS user_id, SUM(amount) AS moneyGiven FROM transactions t
                WHERE  t.status = 'approved'
                AND t.payment_provider = 'credit'
                GROUP BY sender_user_id
            ) AS moneyp ON moneyp.user_id = moneyr.user_id
            {$additionalSQL}
            ;";
        $usersToAdjust = DB::select($query);
        $userIDsToAdjust = collect($usersToAdjust)->pluck('user_id')->toArray();

        // Parsing transactions of each user, in order to get his incomes - transactions
        $users = User::whereIn('id', $userIDsToAdjust)->get();
        foreach($users as $user){
            $moneyReceivedTransactions = Transaction::where('recipient_user_id', $user->id)->where('status','approved')->whereNotIn('type',['withdrawal'])->get();
            $moneyReceivedNoTaxes = 0;
            foreach($moneyReceivedTransactions as $transaction){
                $taxes = json_decode($transaction->taxes);
                $moneyReceivedNoTaxes += (float)$transaction->amount - ($taxes ? (float)$taxes->taxesTotalAmount : 0);
            }
            foreach($usersToAdjust as $k => $user){
                if($user->user_id == $transaction->recipient_user_id){
                    $usersToAdjust[$k]->moneyReceivedNoTaxes = $moneyReceivedNoTaxes;
                }
            }

        }
        if($this->option('debug') == true) dump('---Wallets---');

        // Parsing and updating wallets
        $diffWallets = 0;
        foreach($usersToAdjust as $user){
            $updatedWalletValue = (float)$user->moneyReceivedNoTaxes - (float)$user->moneyOut - (float)$user->moneyGiven;
            $wallet = Wallet::where('user_id',$user->user_id)->select(['total'])->first();
            if($wallet){
                $currentWalletTotal = $wallet->total;
                if($this->option('debug') == true) dump("user_id:" . $user->user_id . ' | Old = '.$currentWalletTotal.' | New = '  .$updatedWalletValue);
                if($this->option('dry-run') == 'false'){
                    Wallet::where('user_id', $user->user_id)->update(['total' => $updatedWalletValue]);
                }
                if((string)$currentWalletTotal != (string)$updatedWalletValue) $diffWallets++;
            }
        }

        if($this->option('debug') == true) dump('---Stats---');
        if($this->option('debug') == true) dump("Total number of updated wallets: ". ($this->option('dry-run') == 'false' ? count($usersToAdjust) : '0'));
        if($this->option('debug') == true) dump("Total number of missmatched wallets: ".$diffWallets);
        if($this->option('debug') == true) dump('-----------');

        return 0;
    }

}
