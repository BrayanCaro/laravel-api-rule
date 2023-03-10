<?php

use BrayanCaro\LaravelApiRule\ApiRule;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {

    $this->rule = new class extends ApiRule
    {
        protected function pullResponse($value): Response
        {
            Http::fake([
                '*' => Http::response(),
            ]);
            return Http::get('dummy-url');
        }
    };
});

it('passed a successfull request')
    ->expect(fn () => Validator::make(['foo' => 'bar'], ['foo' => [$this->rule, 'required']]))
    ->passes()
    ->toBeTrue();
