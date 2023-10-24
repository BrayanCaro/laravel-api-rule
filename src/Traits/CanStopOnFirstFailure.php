<?php

namespace BrayanCaro\ApiRule\Traits;

use Closure;
use Illuminate\Http\Client\Response;

trait CanStopOnFirstFailure
{
    /**
     * @var bool|Closure(Response $response): bool
     */
    protected $stopOnFirstFailure = false;

    /**
     * @param bool|Closure $stopOnFirstFailure
     * @return static
     */
    public function setStopOnFirstFailure($stopOnFirstFailure): self
    {
        $this->stopOnFirstFailure = $stopOnFirstFailure;
        return $this;
    }

    public function getStopOnFirstFailure(): bool
    {
        return value($this->stopOnFirstFailure, $this->response);
    }
}
