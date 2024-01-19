<?php

namespace App\Providers;

use App\Model\Attachment;
use App\Model\PaymentRequest;
use App\Model\Post;
use App\Model\Subscription;
use App\Model\PostComment;
use App\Model\Transaction;
use App\Model\UserMessage;
use App\Model\UserVerify;
use App\Model\Withdrawal;
use App\Observers\AttachmentsObserver;
use App\Observers\PaymentRequestsObserver;
use App\Observers\PostApprovalObserver;
use App\Observers\CommentHistoryObserver;
use App\Observers\SubscriptionsObserver;
use App\Observers\TransactionsObserver;
use App\Observers\UserMessagesObserver;
use App\Observers\UsersObserver;
use App\Observers\UserVerifyObserver;
use App\Observers\WithdrawalsObserver;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (! InstallerServiceProvider::checkIfInstalled()) {
            return false;
        }
        UserVerify::observe(UserVerifyObserver::class);
        PostComment::observe(CommentHistoryObserver::class);
        Withdrawal::observe(WithdrawalsObserver::class);
        PaymentRequest::observe(PaymentRequestsObserver::class);
        UserMessage::observe(UserMessagesObserver::class);
        Attachment::observe(AttachmentsObserver::class);
        Transaction::observe(TransactionsObserver::class);
        Post::observe(PostApprovalObserver::class);
        Subscription::observe(SubscriptionsObserver::class);
        User::observe(UsersObserver::class);
        if(getSetting('security.enforce_app_ssl')){
            \URL::forceScheme('https');
        }
        Schema::defaultStringLength(191); // TODO: Maybe move it as the first line
        if(!InstallerServiceProvider::glck()){
            dd(base64_decode('SW52YWxpZCBzY3JpcHQgc2lnbmF0dXJl'));
        }
        // Overriding timezone, if provided
        if(getSetting('site.timezone')){
            config(['app.timezone' => getSetting('site.timezone')]);
            date_default_timezone_set(getSetting('site.timezone'));
        }
        Paginator::useBootstrap();
    }
}
