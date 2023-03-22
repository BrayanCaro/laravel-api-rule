<?php

namespace BrayanCaro\ApiRule\Traits;

use BrayanCaro\ApiRule\ApiRule;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;

trait ReuseResponse
{
    /**
     * @template T of ApiRule
     * @param class-string<T> $className
     * @return T
     */
    public function getRule(string $className)
    {
        return Collection::wrap($this->validator->getRules())
            ->flatten()
            ->first(fn ($rule) => $rule instanceof $className);
    }

    public function getResponseFromRule(string $className): Response
    {
        return $this->getRule($className)->getResponse();
    }
}
