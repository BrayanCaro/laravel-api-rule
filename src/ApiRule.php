<?php

namespace BrayanCaro\LaravelApiRule;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

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

    public function __construct(array $options = [])
    {
        $this->setUp($options);
    }

    protected function setUp(array $options = [])
    {
        $this->rules = data_get($options, 'rules', []);
        $this->messages = data_get($options, 'messages', []);
        $this->customAttributes = data_get($options, 'customAttributes', []);
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
        $rules = $this->prefixKeyToRules($this->rules, $this->getPrefix());
        $messages = $this->prefixKeyToRules($this->messages, $this->getPrefix());
        $customAttributes = $this->prefixKeyToRules($this->customAttributes, $this->getPrefix());

        $this->validatorResponse = FacadesValidator::make($this->data, $rules, $messages, $customAttributes);
        $this->validatorResponse->fails();

        return $this->validatorResponse->passes();
    }

    protected function getPrefix(): string
    {
        return self::$base_attribute.".$this->attribute";
    }

    protected static function prefixKeyToRules(array $rules, string $prefix): array
    {
        return Collection::wrap($rules)->mapWithKeys(fn ($value, $key) => [
            (Str::startsWith($key, $prefix) ? $key : "$prefix.$key") => $value,
        ])->toArray();
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
