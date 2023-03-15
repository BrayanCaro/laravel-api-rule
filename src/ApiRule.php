<?php

namespace BrayanCaro\LaravelApiRule;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Validation\Validator;

use function BrayanCaro\LaravelApiRule\Utils\prependKeysWith;

abstract class ApiRule implements Rule, DataAwareRule
{
    protected Response $response;

    protected Validator $validatorResponse;

    protected array $data;

    protected array $rules = [];

    protected array $customAttributes = [];

    protected array $messages = [];

    protected static string $base_attribute = 'responses';

    protected string $attribute;

    abstract protected function pullResponse($value): Response;

    public function make()
    {
        return new $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setRules(array $rules)
    {
        $this->rules = $rules;
        return $this;
    }

    public function setCustomAttributes(array $customAttributes)
    {
        $this->customAttributes = $customAttributes;
        return $this;
    }

    public function setMessages(array $messages)
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

    protected function responseFailded(): bool
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
        if ($this->responseFailded()) {
            return $this->responseErrors();
        }

        return $this->validatorResponse->messages()->all();
    }

    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;
        $this->response = $this->pullResponse($value);
        if ($this->responseFailded()) {
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
