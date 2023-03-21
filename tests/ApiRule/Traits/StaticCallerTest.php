<?php

use BrayanCaro\LaravelApiRule\ApiRule;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class DummyRule extends ApiRule
{
    protected function pullResponse($value): Response
    {
        return Http::get('dummy.com');
    }
}

it('is instance of child class (using make)')
    ->expect(fn () => DummyRule::make())
    ->toBeInstanceOf(DummyRule::class)->only();
