<?php

use Illuminate\Support\Facades\Http;

use function Pest\Laravel\postJson;

it('can prevent a failed response', function () {
    Http::fake([
        'fail.dummy.com' => Http::response([], 500),
    ]);

    postJson('/test/feature/responses/reuse-response', [
        'foo' => 'bar',
    ])->assertJsonValidationErrors([
        'foo' => 'This is a dummy error message',
    ]);
})->group('response');

it('can reuse a response', function () {
    Http::fake([
        'fail.dummy.com' => [
            'data' => [
                'foo' => 'bar',
            ],
        ],
    ]);

    postJson('/test/feature/responses/reuse-response', [
        'foo' => 'bar',
    ])->assertJson([
        'foo' => 'response: bar',
    ]);
})->group('response');
