<?php

namespace BrayanCaro\ApiRule;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Validation\Validator;

use function BrayanCaro\ApiRule\Utils\prependKeysWith;

/**
 * @phpstan-consistent-constructor
 */
abstract class ApiRule implements Rule, DataAwareRule
{
    public function __construct()
    {
    }

    protected Response $response;

    protected Validator $validatorResponse;

    protected array $data;

    protected array $rules = [];

    protected array $customAttributes = [];

    protected array $messages = [];

    protected static string $base_attribute = 'responses';

    protected string $attribute;

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
        return self::$base_attribute . ".$this->attribute";
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    protected function responseFailed(): bool
    {
        return $this->response->failed();
    }

    protected function setResponseData()
    {
        $response = $this->response->json();
        Arr::set($this->data, $this->getPrefix(), $response);
    }

    /**
     * Gets the errors of the response when it is considered as a failed response.
     */
    protected function responseErrors()
    {
        return $this->response->json('errors');
    }

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
        $this->setResponseData();
        $this->validatorResponse = FacadesValidator::make(
            $this->data, # the data is already prefixed in setResponseData()
            prependKeysWith($this->rules, $this->getPrefix() . '.'),
            prependKeysWith($this->messages, $this->getPrefix() . '.'),
            prependKeysWith($this->customAttributes, $this->getPrefix() . '.'),
        );
        $this->validatorResponse->fails();
        return $this->validatorResponse->passes();
    }
}
