<?php

namespace Tarik02\LaravelTelegram\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class WebhookRequest
 * @package Tarik02\LaravelTelegram\Http\Requests
 */
class WebhookRequest extends FormRequest
{
    public function rules()
    {
        return [
            'update_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
