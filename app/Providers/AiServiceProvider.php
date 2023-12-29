<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    private const OPEN_AI_COMPLETION_MODEL = 'text-davinci-003';
    private const OPEN_AI_BASE_URL = 'https://api.openai.com/v1';
    private const OPEN_AI_COMPLETION_PATH = '/completions';

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    public static function generateCompletionRequest(string $key) {
        $suggestion = "";
        if(getSetting('ai.open_ai_enabled')) {
            $httpClient = new Client();
            $chatGptRequest = $httpClient->request('POST', self::OPEN_AI_BASE_URL.self::OPEN_AI_COMPLETION_PATH, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer '.getSetting('ai.open_ai_api_key'),
                    ],
                    'body' => json_encode(array_merge_recursive([
                        'model' => self::OPEN_AI_COMPLETION_MODEL,
                        'prompt' => $key,
                        'temperature' => self::getChatGptTemperatureValue(),
                        'max_tokens' => self::getChatGptMaxTokensValue()
                    ]))
                ]
            );
            $response = json_decode($chatGptRequest->getBody(), true);
            if(isset($response['choices']) && isset($response['choices'][0])) {
                $suggestion = trim($response['choices'][0]['text']);
            }
        }
        return $suggestion;
    }

    /**
     * @return int
     */
    public static function getChatGptTemperatureValue() {
        $temperature = 1;
        if(getSetting('ai.open_ai_temperature')){
            $settingValue = intval(getSetting('ai.open_ai_temperature'));
            // make sure this is a valid value or leave the default
            if($settingValue >= 0 && $settingValue <= 2) {
                $temperature = $settingValue;
            }
        }

        return $temperature;
    }

    /**
     * @return int
     */
    public static function getChatGptMaxTokensValue() {
        $maxTokens = 100;
        if(getSetting('ai.open_ai_completion_max_tokens')){
            $settingValue = intval(getSetting('ai.open_ai_completion_max_tokens'));
            // make sure this is a valid value or leave the default
            if($settingValue > 0 && $settingValue <= 2048) {
                $maxTokens = $settingValue;
            }
        }

        return $maxTokens;
    }
}
