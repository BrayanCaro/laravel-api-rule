<?php

namespace BrayanCaro\ApiRule\Traits;

use Illuminate\Validation\Validator;

trait HasReplacers
{
    /**
     * @var array $replacers
     */
    protected $replacers = [];

    /**
     * @param array<string,callable(string $message, string $attribute, string $rule, array $parameters, Validator $valiador): string | string> $replacers
     * @return static
     */
    public function setReplacers(array $replacers): self
    {
        $this->replacers = $replacers;
        return $this;
    }
}
