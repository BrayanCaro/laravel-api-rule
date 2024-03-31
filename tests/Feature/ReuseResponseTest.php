<?php

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

it('should save the response to be reuse later', function () {
    $dummyResponse = [
        'data' => [
            'attribute1' => 100,
            'attribute2' => false,
            'obj' => [
                'id' => 12,
                'type' => 'TYPE1',
            ],
        ],
    ];

    Http::fake([
        'dummy.com*' => $dummyResponse,
    ]);

    $target = [];
    $key = 'reuse';

    $validator = Validator::make(['foo' => 'bar'], [
        'foo' => [dummyRule1()->saveResponseOn($target, $key), 'required']
    ]);

    expect($validator)->passes()->toBeTrue();

    $response = data_get($target, $key);
    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->json())->toBe($dummyResponse);
});
