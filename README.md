# Native Laravel PHP 8.1 Enums 

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

[![mindtwo GmbH](https://github.com/mindtwo/native-enum/blob/master/assets/header.png?raw=true)](https://www.mindtwo.de/)

Inspired by the package [BenSampo/laravel-enum](https://github.com/BenSampo/laravel-enum) this package is adapted to the new native enums in PHP 8.1.
Based on these new enums the native functions are extended to work easily with them in Laravel based systems.

Everyone is welcome to contribute.

Enojy the Package!

## Install

### Requirements
Laravel 9 or higher
PHP 8.1 or higher

### Composer install

``` bash
$ composer require mindtwo/native-enum
```


## Enum creation

```php
namespace App\Enums;

enum UserRole: int
{
    use BaseEnum;
    
    case ADMIN = 10;
    case CUSTOMER = 50;
}
```

## Usage

### Base usage
```php
TestEnum::getRandomValue();
TestEnum::getRandomName();
TestEnum::getRandomInstance();
TestEnum::asSelectArray();
TestEnum::asArray();
TestEnum::getValues();
TestEnum::getNames();
```

### Get specific enum values
```php
TestEnum::getValues('ADMIN');
TestEnum::getValues(UserRole::ADMIN);
TestEnum::getValues([UserRole::ADMIN]);
TestEnum::getValues('CUSTOMER');
TestEnum::getValues(UserRole::CUSTOMER);
TestEnum::getValues([UserRole::CUSTOMER]);
```

### Get specific enum names
```php
TestEnum::getNames(10);
TestEnum::getNames(50);
TestEnum::getNames([10,50]);
```

### Localized enums

The Enum has to implement the `LocalizedEnum` interface:

```php
namespace App\Enums;

enum UserRole: int implements LocalizedEnum
{
    use BaseEnum;
    
    case ADMIN = 10;
    case CUSTOMER = 50;
}
```

Translation files can be placed here `lang/en/enums.php` like:

```php
use \App\Enums;

return [

    Enums\UserRole::class => [
        Enums\UserRole::ADMIN->name => 'Administrator',
        Enums\UserRole::CUSTOMER->name => 'Customer',
    ],

];

```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email info@mindtwo.de instead of using the issue tracker.

## Credits

- [mindtwo GmbH][link-author]
- [BenSampo/laravel-enum][link-laravel-enum]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/mindtwo/native-enum.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/mindtwo/native-enum/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/mindtwo/native-enum.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/mindtwo/native-enum.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/mindtwo/native-enum.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/mindtwo/native-enum
[link-travis]: https://app.travis-ci.com/github/mindtwo/native-enum
[link-scrutinizer]: https://scrutinizer-ci.com/g/mindtwo/native-enum/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/mindtwo/native-enum
[link-downloads]: https://packagist.org/packages/mindtwo/native-enum
[link-author]: https://github.com/mindtwo
[link-laravel-enum]: https://github.com/BenSampo/laravel-enum
[link-contributors]: ../../contributors


[![Back to the top](https://www.mindtwo.de/downloads/doodles/github/repository-footer.png)](#)
