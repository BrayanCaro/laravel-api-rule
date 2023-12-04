<?php

namespace BrayanCaro\ApiRule\Tests\Http\Requests;

use BrayanCaro\ApiRule\ApiRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * @property-read ?Response $fooResponse
 */
class ReuseResponseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'foo' => [
                'required',
                (new class extends ApiRule
                {
                    protected function pullResponse($value): Response
                    {
                        return Http::get('fail.dummy.com');
                    }

                    protected function defaultErrorMessage(): string
                    {
                        return 'This is a dummy error message';
                    }
                })->saveResponseOn($this, 'fooResponse'),
            ],
        ];
    }
}
