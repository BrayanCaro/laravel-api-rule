<?php

use BrayanCaro\ApiRule\ApiRule;
use BrayanCaro\ApiRule\Tests\TestCase;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

uses(TestCase::class)->in(__DIR__);

function dummyRule1(): ApiRule
{
    return new class extends ApiRule
    {
        protected function pullResponse($value): Response
        {
            return Http::get('dummy.com');
        }
    };
}
