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

    protected array $rules;

    protected array $customAttributes;

    protected array $messages;

    protected static string $base_attribute = 'responses';

    protected string $attribute;

    public function make()
    {
        return new $this;
    }

    public function setRules(array $rules)
    {
        $this->rules = prependKeysWith($rules, $this->getPrefix());
        return $this;
    }

    public function setCustomAttributes(array $customAttributes)
    {
        $this->customAttributes = prependKeysWith($customAttributes, $this->getPrefix());
        return $this;
    }

    public function setMessages(array $messages)
    {
        $this->messages = prependKeysWith($messages, $this->getPrefix());
        return $this;
    }

    abstract protected function pullResponse($value): Response;

    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;

        $this->response = $this->pullResponse($value);
        if ($this->response->failed()) {
            return false;
        }

        $response = $this->response->json();
        Arr::set($this->data, $this->getPrefix(), $response);
        $this->validatorResponse = FacadesValidator::make($this->data, $this->rules, $this->messages, $this->customAttributes);
        $this->validatorResponse->fails();

        return $this->validatorResponse->passes();
    }

    protected function getPrefix(): string
    {
        return self::$base_attribute . ".$this->attribute";
    }

    public function message()
    {
        if ($this->response->failed()) {
            return $this->response->json('error');
        }

        return $this->validatorResponse->messages()->first();
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }
}
