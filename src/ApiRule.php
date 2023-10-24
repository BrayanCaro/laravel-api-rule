<?php

namespace BrayanCaro\ApiRule;

use BrayanCaro\ApiRule\Traits\HasReplacers;
use BrayanCaro\ApiRule\Traits\CanStopOnFirstFailure;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Support\Str;

use function BrayanCaro\ApiRule\Utils\prependKeysWith;

/**
 * @phpstan-consistent-constructor
 */
abstract class ApiRule implements DataAwareRule, Rule
{
    use HasReplacers;
    use CanStopOnFirstFailure;

    public function __construct()
    {
    }

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
     * @var array|Closure(Response $response): array
     */
    protected $rules = [];

    /**
     * @var array|Closure(Response $response): array
     */
    protected $customAttributes = [];

    /**
     * @var array|Closure(Response $response): array
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

    /**
     * @var bool
     */
    protected $reportOnTimeout = true;

    /**
     * @var null|Closure
     */
    protected $afterPull = null;

    /**
     * @var bool
     */
    protected $throwExceptionOnTimeout = false;

    abstract protected function pullResponse($value): Response;

    /**
     * @return static
     */
    public static function make(): ApiRule
    {
        return new static;
    }

    public function setData($data): ApiRule
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param array|Closure(Response $response): array $rules
     * @return static
     */
    public function setRules($rules): ApiRule
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @param array|Closure(Response $response): array $customAttributes
     * @return static
     */
    public function setCustomAttributes($customAttributes): ApiRule
    {
        $this->customAttributes = $customAttributes;

        return $this;
    }

    /**
     * @param array|Closure(Response $response): array $messages
     * @return static
     */
    public function setMessages($messages): ApiRule
    {
        $this->messages = $messages;

        return $this;
    }

    protected function getPrefix(): string
    {
        return self::$base_attribute . '.' . $this->attribute;
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
     * Default error message when the rule couldn't find a proper error message
     * in the response, only needed if the error message is null, or is
     * not well-formed (is not a string or a non-empty array of strings).
     */
    protected function defaultErrorMessage(): ?string {
        return null;
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
     * @return string[]|string|null
     */
    protected function safeResponseErrors()
    {
        $errors = Collection::wrap($this->responseErrors())
            ->filter(function ($item) {
                return is_string($item);
            });

        if ($errors->isNotEmpty()) {
            return $errors->toArray();
        }

        return $this->defaultErrorMessage();
    }

    /**
     * @return string[]|string|null
     */
    public function message()
    {
        if ($this->responseFailed()) {
            return $this->safeResponseErrors();
        }

        return $this->validatorResponse->errors()->all();
    }

    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;

        $this->response = $this->throwExceptionOnTimeout ? $this->pullResponse($value) : $this->safePullResponse($value);

        $this->afterPullHook();

        if ($this->responseFailed()) {
            return false;
        }

        return !$this->setupValidator($this->getPrefix())->fails();
    }

    /**
     * @param string $prefix The prefix for getting the response data using dot notation
     */
    public function setupValidator(string $prefix): Validator
    {
        $this->setResponseData($prefix);

        return $this->validatorResponse = $this->getValidatorResponse($prefix);
    }

    /**
     * @param string $prefix The prefix for getting the response data using dot notation
     */
    protected function setResponseData(string $prefix): void
    {
        Arr::set($this->data, $prefix, $this->response->json());
    }

    /**
     * @param string $prefix The prefix for getting the response data using dot notation
     */
    public function getValidatorResponse(string $prefix): Validator
    {
        $prefix = Str::finish($prefix, '.');

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = FacadesValidator::make(
            $this->data, // the data is already prefixed in setResponseData()
            prependKeysWith($this->getRules(), $prefix),
            prependKeysWith($this->getMessages(), $prefix),
            prependKeysWith($this->getCustomAttributes(), $prefix),
        );
        $validator->addReplacers($this->replacers);
        $validator->stopOnFirstFailure($this->getStopOnFirstFailure());
        return $validator;
    }

    public function safePullResponse($value): Response
    {
        return rescue(function () use ($value) {
            return $this->pullResponse($value);
        }, self::getDefaultTimeoutResponse(), $this->reportOnTimeout);
    }

    /**
     * @return static
     */
    public function setReportOnTimeout(bool $reportOnTimeout): ApiRule
    {
        $this->reportOnTimeout = $reportOnTimeout;
        return $this;
    }

    /**
     * @return static
     */
    public function setThrowExceptionOnTimeout(bool $throwExceptionOnTimeout): ApiRule
    {
        $this->throwExceptionOnTimeout = $throwExceptionOnTimeout;
        return $this;
    }

    /**
     * @return Response
     */
    public static function getDefaultTimeoutResponse(): Response
    {
        return new Response(new \GuzzleHttp\Psr7\Response(\Illuminate\Http\Response::HTTP_REQUEST_TIMEOUT));
    }

    public function afterPullHook(): void
    {
        $hook = $this->afterPull;
        if ($hook instanceof Closure) {
            $hook($this->response);
        }
    }

    /**
     * @param string|array $path
     * @return static
     */
    public function saveResponseOn(&$target, $path): ApiRule
    {
        $this->setAfterPull(function () use (&$target, $path) {
            data_set($target, $path, $this->response);
        });

        return $this;
    }

    /**
     * @return static
     */
    public function setAfterPull(?Closure $afterPull): ApiRule
    {
        $this->afterPull = $afterPull;
        return $this;
    }

    public function getRules(): array
    {
        return value($this->rules, $this->response);
    }

    public function getCustomAttributes(): array
    {
        return value($this->customAttributes, $this->response);
    }

    public function getMessages(): array
    {
        return value($this->messages, $this->response);
    }
}
