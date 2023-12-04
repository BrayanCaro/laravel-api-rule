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
                    'description' => 'CUSTOM_QUERY',
                ],
            ],
        ]),
    ]);
});

dataset('expectedValidRules', [
    'simple validation' => [[
        'data' => 'required|array',
    ]],
    'multiple fileds validation' => [[
        'data' => 'required|array',
        'data.attribute1' => 'required|numeric|between:70,101',
        'data.attribute2' => 'required|declined',
        'data.obj.type' => 'required|in:TYPE1,TYPE2,TYPE3',
    ]],
    'multiple fileds validation (explicit prefix)' => [[
        'responses.dummy_query.data' => 'required|array',
        'responses.dummy_query.data.attribute1' => 'required|numeric|between:70,101',
        'responses.dummy_query.data.attribute2' => 'required|declined',
        'responses.dummy_query.data.obj.type' => 'required|in:TYPE1,TYPE2,TYPE3',
    ]],
    'validate the response with input data' => [[
        'responses.dummy_query.data.obj.description' => 'required|string|same:dummy_query',
    ]],
]);

it('passes Validation with successful response')
    ->expect(fn ($responseRules) => Validator::make(['dummy_query' => 'CUSTOM_QUERY'], ['dummy_query' => dummyRule1()->setRules($responseRules)]))
    ->passes()
    ->toBeTrue()
    ->with('expectedValidRules');
