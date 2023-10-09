<?php

namespace BrayanCaro\ApiRule\Traits;

use BrayanCaro\ApiRule\ApiRule;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;

trait ReuseResponse
{
    /**
     * @template T of ApiRule
     *
     * @param  class-string<T>  $className
     * @return T
     */
    public function getRule(string $className)
    {
        return Collection::wrap($this->validator->getRules())
            ->flatten()
            ->first(function ($rule) use ($className) {
                return $rule instanceof $className;
            });
    }

    public function getResponseFromRule(string $className): Response
    {
        return $this->getRule($className)->getResponse();
    }
}
