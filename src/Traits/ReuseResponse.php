<?php

namespace BrayanCaro\LaravelApiRule\Traits;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;

trait ResponseAccess
{
    /**
     * @template T of \BrayanCaro\LaravelApiRule\ApiRule
     * @param class-string<T> $className
     * @return T
     */
    public function getRule(string $className)
    {
        $rule = Collection::wrap($this->validator->getRules())
            ->flatten()
            ->first(fn ($rule) => $rule instanceof $className);

        return $rule;
    }

    public function getResponseFromRule(string $className): Response
    {
        return $this->getRule($className)->getResponse();
    }
}
