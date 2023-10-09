<?php

namespace BrayanCaro\ApiRule\Traits;

use BrayanCaro\ApiRule\ApiRule;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;

/**
 * @deprecated The usase is not recommended since it just return the firs instance that you are looking for.
 * @see ApiRule::setAfterPull() To use a custom login for each particular rule, having access to its response.
 * @see ApiRule::saveResponseOn() Use as a shorcut to set the response to a particular property.
 */
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
