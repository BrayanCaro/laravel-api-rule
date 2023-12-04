<?php

use BrayanCaro\ApiRule\ApiRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

beforeEach(function () {
    Http::partialMock()->shouldReceive('get')->andThrow(ConnectionException::class);
    $this->rule = new class extends ApiRule {
        protected function pullResponse($value): Response
        {
            return Http::get('fail.dummy.com');
        }
    };
});

it('should fail when a timeout exception was present')
    ->expect(fn() => Validator::make(['foo' => 'bar'], ['foo' => $this->rule]))
    ->not->toThrow(ConnectionException::class)
    ->passes()
    ->toBeFalse();
