<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {
    Http::fake([
        'dummy.com*' => Http::response([
            'data' => [
                'attribute1' => 100,
                'attribute2' => false,
                'obj' => [
                    'id' => 12,
                    'type' => 'TYPE1',
                ],
            ],
        ]),
    ]);
    $this->rule = dummyRule1();
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

it('passes a successful request')
    ->expect(fn () => Validator::make(['foo' => 'bar'], ['foo' => [$this->rule, 'required']]))
    ->passes()
    ->toBeTrue();
