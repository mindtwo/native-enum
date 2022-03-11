# Native Laravel PHP 8.1 Enums 

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

[![mindtwo GmbH](https://github.com/mindtwo/native-enum/blob/master/assets/header.png?raw=true)](https://www.mindtwo.de/)

Inspired by the package `BenSampo/laravel-enum` this package is adapted to the new native enums in PHP 8.1.
Based on these new enums the native functions are extended to work easily with them in Laravel based systems.

Enojy the Package!

Everyone is welcome to contribute.

## Structure

If any of the following are applicable to your project, then the directory structure should follow industry best practices by being named the following.

```
bin/
src/
tests/
vendor/
```


## Install

Via Composer

``` bash
$ composer require mindtwo/native-enum
```

## Usage

``` php
enum UserRole: int implements LocalizedEnum
{
    use BaseEnum;
    
    case ADMIN = 10;
    case CUSTOMER = 50;
}
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
