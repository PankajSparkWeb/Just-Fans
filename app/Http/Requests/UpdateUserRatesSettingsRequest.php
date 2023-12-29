<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRatesSettingsRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    public static function getRules(){
        return $rules =  [
            'profile_access_price' => 'numeric|min:'.((int)getSetting('payments.minimum_subscription_price')).'|max:'.((int)getSetting('payments.maximum_subscription_price')),
            'profile_access_price_3_months' => 'numeric|min:'.((int)getSetting('payments.minimum_subscription_price')).'|max:'.((int)getSetting('payments.maximum_subscription_price')),
            'profile_access_price_6_months' => 'numeric|min:'.((int)getSetting('payments.minimum_subscription_price')).'|max:'.((int)getSetting('payments.maximum_subscription_price')),
            'profile_access_price_12_months' => 'numeric|min:'.((int)getSetting('payments.minimum_subscription_price')).'|max:'.((int)getSetting('payments.maximum_subscription_price')),
        ];;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return self::getRules();
    }
}
