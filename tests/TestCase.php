<?php

namespace BrayanCaro\ApiRule\Tests;

use BrayanCaro\ApiRule\Tests\Http\Requests\ReuseResponseRequest;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }

    protected function defineRoutes($router)
    {
        $router->post('/test/feature/responses/reuse-response', function (ReuseResponseRequest $request) {
            return [
                'foo' => 'response: ' . $request->fooResponse->json('data.foo'),
            ];
        });
    }
}
