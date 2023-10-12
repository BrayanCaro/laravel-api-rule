<?php

use BrayanCaro\ApiRule\ApiRule;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {
    Http::fake([
        'fail.dummy.com*' => Http::response([
            // Some apis return the errors wrapped into an array
            'errors' => [
                [
                    'foo',
                    'bar'
                ],
                [
                    'baz'
                ]
            ],
        ], 400),
    ]);
    $this->rule = new class extends ApiRule {
        protected function pullResponse($value): Response
        {
            return Http::get('fail.dummy.com');
        }
    };
});

it('fails a failed request', function () {
    $validator = Validator::make(['foo' => 'bar'], ['foo' => [$this->rule, 'required']]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->all())->not->toBeEmpty();
});
