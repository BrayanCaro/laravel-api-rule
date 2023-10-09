<?php

namespace BrayanCaro\ApiRule;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

use function BrayanCaro\ApiRule\Utils\prependKeysWith;

/**
 * @phpstan-consistent-constructor
 */
abstract class ApiRule implements DataAwareRule, Rule
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Validator
     */
    protected $validatorResponse;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $customAttributes = [];

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var string
     */
    protected static $base_attribute = 'responses';

    /**
     * @var string
     */
    protected $attribute;

    abstract protected function pullResponse($value): Response;

    public static function make(): ApiRule
    {
        return new static;
    }

    public function setData($data): ApiRule
    {
        $this->data = $data;

        return $this;
    }

    public function setRules(array $rules): ApiRule
    {
        $this->rules = $rules;

        return $this;
    }

    public function setCustomAttributes(array $customAttributes): ApiRule
    {
        $this->customAttributes = $customAttributes;

        return $this;
    }

    public function setMessages(array $messages): ApiRule
    {
        $this->messages = $messages;

        return $this;
    }

    protected function getPrefix(): string
    {
        return self::$base_attribute.'.'.$this->attribute;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    protected function responseFailed(): bool
    {
        return $this->response->failed();
    }

    /**
     * Gets the errors of the response when it is considered as a failed response.
     *
     * @return array|string|null
     */
    protected function responseErrors()
    {
        return $this->response->json('errors');
    }

    /**
     * @return array|string|null
     */
    public function message()
    {
        if ($this->responseFailed()) {
            return $this->responseErrors();
        }

        return $this->validatorResponse->messages()->all();
    }

    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;
        $this->response = $this->pullResponse($value);
        if ($this->responseFailed()) {
            return false;
        }

        return $this->setupValidator($this->getPrefix())->passes();
    }

    /**
     * @param  string  $prefix The prefix for getting the response data using dot notation
     */
    public function setupValidator(string $prefix): \Illuminate\Contracts\Validation\Validator
    {
        $this->setResponseData($prefix);

        return $this->validatorResponse = $this->getValidatorResponse($prefix);
    }

    /**
     * @param  string  $prefix The prefix for getting the response data using dot notation
     */
    protected function setResponseData(string $prefix): void
    {
        Arr::set($this->data, $prefix, $this->response->json());
    }

    /**
     * @param  string  $prefix The prefix for getting the response data using dot notation
     */
    public function getValidatorResponse(string $prefix): \Illuminate\Contracts\Validation\Validator
    {
        $prefix = Str::finish($prefix, '.');

        return FacadesValidator::make(
            $this->data, // the data is already prefixed in setResponseData()
            prependKeysWith($this->rules, $prefix),
            prependKeysWith($this->messages, $prefix),
            prependKeysWith($this->customAttributes, $prefix),
        );
    }
}
