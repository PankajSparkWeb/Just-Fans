<?php

namespace App\Http\Requests;

use App\Rules\MaxLengthMarkdown;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateUserProfileSettingsRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $rules = [
            'name' => 'required|max:191',
            'username' => 'required|string|alpha_dash|max:255|unique:users,username,'.Auth::user()->id,
            'location' => 'max:500',
        ];

        if(getSetting('profiles.max_profile_bio_length') && getSetting('profiles.max_profile_bio_length') !== 0){

            if(getSetting('profiles.allow_profile_bio_markdown')){
                $rules['bio'] = [new MaxLengthMarkdown];
            }
            else{
                $rules['bio'] = 'max:' . getSetting('profiles.max_profile_bio_length');
            }
        }

        return $rules;
    }
}
