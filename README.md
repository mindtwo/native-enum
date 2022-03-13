# Laravel Native PHP 8.1 Enums 

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]

[![mindtwo GmbH](https://github.com/mindtwo/native-enum/blob/master/assets/header.png?raw=true)](https://www.mindtwo.de/)

Inspired by the package [BenSampo/laravel-enum](https://github.com/BenSampo/laravel-enum) this one is using the new native enums in PHP 8.1.
Based on these new enums the native functions are extended to work easily with them in Laravel based systems.

Everyone is welcome to contribute.

Enojy the Package!

## Overview
- [Install](Install)
  - [Requirements](#requirements)
  - [Composer install](#composer-install)
- [Create enum](#create-enum)
  - [Artisan command](#artisan-command)
  - [Manual enum creation](#manual-enum-creation)
- [Usage](#usage)
  - [Base usage](#base-usage)
  - [Get specific enum values](#get-specific-enum-values)
  - [Get specific enum names](#get-specific-enum-names) 
  - [Use static calls to get the primitive value](#use-static-calls-to-get-the-primitive-value) 
  - [Localized enum](#localized-enum)

## Install

### Requirements
Laravel 9 or higher
PHP 8.1 or higher

### Composer install

``` bash
$ composer require mindtwo/native-enum
```

## Create enum
### Artisan command
You can use the following Artisan command to generate a new native enum:

```php
// Default:
php artisan make:enum UserRole

// Localized:
php artisan make:enum UserRole --localized
```

### Manual enum creation
You can also create a new native enum manually. The structure should look like this:

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
UserRole::getRandomValue();
UserRole::getRandomName();
UserRole::getRandomInstance();
UserRole::asSelectArray();
UserRole::asArray();
UserRole::getValues();
UserRole::getNames();
```

### Get specific enum values
```php
UserRole::getValues('ADMIN');
UserRole::getValues(UserRole::ADMIN);
UserRole::getValues([UserRole::ADMIN]);
UserRole::getValues('CUSTOMER');
UserRole::getValues(UserRole::CUSTOMER);
UserRole::getValues([UserRole::CUSTOMER]);
```

### Get specific enum names
```php
UserRole::getNames(10);
UserRole::getNames(50);
UserRole::getNames([10,50]);
```

### Use static calls to get the primitive value
```php
UserRole::ADMIN(); // 10
UserRole::CUSTOMER(); // 50
```

### Localized enum

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
