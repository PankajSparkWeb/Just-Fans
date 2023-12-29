<?php

namespace App\Observers;

use App\Helpers\PaymentHelper;
use App\Model\Subscription;
use Illuminate\Support\Facades\Log;

class SubscriptionsObserver
{
    /**
     * Listen to the Subscription deleting event.
     *
     * @param Subscription $subscription
     * @return void
     */
    public function deleting(Subscription $subscription)
    {
        try{
            $paymentHelper = new PaymentHelper();
            $cancelSubscription = $paymentHelper->cancelSubscription($subscription);
            if(!$cancelSubscription) {
                Log::error("Failed cancelling subscription for id: ".$subscription->id);
            }
        } catch (\Exception $exception) {
            Log::error("Failed cancelling subscription for id: ".$subscription->id." error: ".$exception->getMessage());
        }
    }

    /**
     * Listen to the Subscription created event.
     *
     * @param Subscription $subscription
     * @return void
     */
    public function created(Subscription $subscription) {
        //
    }

    /**
     * Listen to the Subscription updating event.
     *
     * @param Subscription $subscription
     * @return void
     */
    public function updating(Subscription $subscription) {
        //
    }
}
