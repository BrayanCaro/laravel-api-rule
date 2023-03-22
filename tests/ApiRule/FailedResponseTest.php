<?php

use BrayanCaro\ApiRule\ApiRule;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {
    Http::fake([
        'fail.dummy.com*' => Http::response([
            'data' => [
                'custom_error_code' => 100,
                'message' => 'DUMMY MSG',
                'errors' => [
                    'attribute' => 'FOO',
                    'message' => 'FOO is required',
                ],
            ],
        ], 400),
    ]);
    $this->rule = new class extends ApiRule
    {
        protected function pullResponse($value): Response
        {
            return Http::get('fail.dummy.com');
        }
    };
});

it('passes missing data')
    ->expect(fn () => Validator::make(['foo' => ''], ['foo' => $this->rule]))
    ->passes()
    ->toBeTrue();

it('fails missing data, but required')
    ->expect(fn ($value) => Validator::make(['foo' => $value], ['foo' => [$this->rule, 'required']]))
    ->fails()
    ->toBeTrue()
    ->with([
        '',
        null,
    ]);

it('fails a failed request')
    ->expect(fn () => Validator::make(['foo' => 'bar'], ['foo' => [$this->rule, 'required']]))
    ->fails()
    ->toBeTrue();
