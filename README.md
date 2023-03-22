# Easy API calls within Laravel rules. 

[![Latest Version on Packagist](https://img.shields.io/packagist/v/brayancaro/laravel-api-rule.svg?style=flat-square)](https://packagist.org/packages/brayancaro/laravel-api-rule)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/brayancaro/laravel-api-rule/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/brayancaro/laravel-api-rule/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/brayancaro/laravel-api-rule/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/brayancaro/laravel-api-rule/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/brayancaro/laravel-api-rule.svg?style=flat-square)](https://packagist.org/packages/brayancaro/laravel-api-rule)

Easy API calls within Laravel rules.

## Installation

You can install the package via composer:

```bash
composer require brayancaro/laravel-api-rule
```

## Usage

```php
# Create a rule
use BrayanCaro\ApiRule\ApiRule;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class DummyRule extends ApiRule
{
    protected function pullResponse($value): Response
    {
        return Http::get("dummy.com/$value");
    }
}
```
And use it as a normal rule:
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'some_attribute' => ['required', DummyRule::make()],
        # ...
    ]);
 
    # ...
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Brayan Martínez Santana](https://github.com/BrayanCaro)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
