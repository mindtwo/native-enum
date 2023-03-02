# Laravel Native PHP 8.1 Enums 

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]

[![mindtwo GmbH](https://github.com/mindtwo/native-enum/blob/master/assets/header.png?raw=true)](https://www.mindtwo.de/)

Enums are an invaluable tool for any developer working with PHP. They provide a way to easily and efficiently define a set of constants that can be used throughout a project. Enums also help to make code more maintainable and scalable. For example, they can help to define a set of values that must stay consistent throughout the project, like a list of permissions or statuses. This helps ensure that the same values are used in all the relevant parts of the code and that any changes are made in one place. Additionally, enums help to reduce the amount of manual testing needed since the values are already defined. Furthermore, enums provide a way for developers to quickly check for valid values, making it easier to spot errors in code and ensure that the application behaves as expected. Finally, enums are strongly typed, meaning that they can help to ensure that the correct type of data is used in each part of the application, which can help to improve overall code quality.

Inspired by the package [BenSampo/laravel-enum](https://github.com/BenSampo/laravel-enum) this one uses the new native enums classes in PHP. The native functions are extended to work easily ams seamlessly with them in Laravel-based systems.

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
  - [Validation](#validation)
  - [Localized enum](#localized-enum)

## Install
## Installing the PHP Composer Package

Laravel Native PHP 8.1 Enums is a powerful package that can help you construct and maintain robust and scalable Laravel-based systems. 

### Requirements
- Laravel 9 or higher
- PHP 8.1 or higher

### Composer install
Before you begin, you'll need to make sure you have the listed requirements and Composer installed on your system. You can find instructions for doing so [here](https://getcomposer.org/doc/00-intro.md). 

Once you have Composer installed, you can install "Laravel Native PHP 8.1 Enums" by running the following command from your project directory:

``` bash
composer require mindtwo/native-enum
```

After the installation is complete, you'll now be ready to start using native enums in your project. Have fun!

## Create enum
### Artisan command
You can use the following Artisan command to generate a new native enum class in your project:

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
UserRole::hasValue(50);
UserRole::hasName('ADMIN');
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

### Validation

#### Enum value

You may validate that an enum value passed to a controller is a valid value for a given enum by using the `EnumValue` rule.

```php
use mindtwo\NativeEnum\Rules\EnumValue;

public function store(Request $request)
{
    $this->validate($request, [
        'user_role' => ['required', new EnumValue(UserRole::class)],
    ]);
}
```

By default, type checking is set to strict, but you can bypass this by passing `false` to the optional second parameter of the EnumValue class.

```php
new EnumValue(UserRole::class, false) // Turn off strict type checking.
```

#### Enum name

You can also validate on names using the `EnumName` rule. This is useful if you're taking the enum name as a URL parameter for sorting or filtering for example.

```php
use mindtwo\NativeEnum\Rules\EnumKey;

public function store(Request $request)
{
    $this->validate($request, [
        'user_role' => ['required', new EnumName(UserRole::class)],
    ]);
}
```

#### Enum instance

Additionally you can validate that a parameter is an instance of a given enum.

```php
use mindtwo\NativeEnum\Rules\Enum;

public function store(Request $request)
{
    $this->validate($request, [
        'user_role' => ['required', new Enum(UserRole::class)],
    ]);
}
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

To get a translated name of a selected enum value:

```php
Enums\UserRole::ADMIN->name(); // Returns "Administrator"
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
- [archtechx/enums][link-archtechx-enums]
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
[link-archtechx-enums]: https://github.com/archtechx/enums
[link-contributors]: ../../contributors


[![Back to the top](https://www.mindtwo.de/downloads/doodles/github/repository-footer.png)](#)
