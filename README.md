# DevStrict - PHP_CodeSniffer Custom Ruleset

[![CI](https://github.com/vix-4800/phpcs-devstrict/workflows/CI/badge.svg)](https://github.com/vix-4800/phpcs-devstrict/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
![PHPStan](https://img.shields.io/badge/style-level%208-brightgreen.svg?&label=phpstan)
![PHP-CS-Fixer](https://img.shields.io/badge/fixer-enabled-brightgreen.svg?&label=php-cs-fixer)
![PHPUnit](https://img.shields.io/badge/tested-enabled-brightgreen.svg?&label=phpunit)
[![PHP Version](https://img.shields.io/badge/php-%5E7.4%20%7C%7C%20%5E8.0-blue)](https://www.php.net/)

A comprehensive set of strict PHP_CodeSniffer rules for general PHP, Laravel, and Yii2 projects to maintain high code
quality standards in your projects.

## Installation

### Requirements

- PHP 7.4 or higher
- Composer

### Install via Composer

```bash
composer require --dev devstrict/phpcs
```

## Usage

### Basic Configuration

Create a `phpcs.xml` file in your project root:

```xml
<?xml version="1.0"?>
<ruleset name="MyProject">
    <description>My project coding standard</description>

    <!-- Paths to check -->
    <file>src</file>
    <file>tests</file>

    <!-- Use DevStrict rules -->
    <rule ref="DevStrict/Common"/>

    <!-- For Laravel projects -->
    <!-- <rule ref="DevStrict/Laravel"/> -->

    <!-- For Yii2 projects -->
    <!-- <rule ref="DevStrict/Yii"/> -->
</ruleset>
```

## Rulesets

### DevStrict/Common

### DevStrict/Laravel

Laravel-specific rules:

### DevStrict/Yii

Rules for Yii2 framework:

### Guidelines

- All new sniffs must have tests
- Follow the existing code style
- Update documentation when necessary
- Ensure all checks pass (`composer check`)

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Additional Resources

- [PHP_CodeSniffer Documentation](https://github.com/squizlabs/PHP_CodeSniffer/wiki)
- [Creating Custom Sniffs](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Coding-Standard-Tutorial)
