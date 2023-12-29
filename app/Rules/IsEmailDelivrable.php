<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IsEmailDelivrable implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        try {
            $client = new \GuzzleHttp\Client();
            $apiRequest = $client->get('https://emailvalidation.abstractapi.com/v1/?api_key='.getSetting('security.email_abstract_api_key').'&email='. $value);
            $apiData = json_decode($apiRequest->getBody()->getContents());
            if($apiData->deliverability == 'UNDELIVERABLE'){
                return false;
            }
            return true;
        }
        catch (\Exception $exception){
            return false;
        }

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Email address in undeliverable.');
    }
}
