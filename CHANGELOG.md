# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Initial project structure
- DevStrict/Common ruleset with base sniffs
- DevStrict/Laravel ruleset for Laravel projects
- DevStrict/Yii ruleset for Yii2 projects
- PHPStan configuration (level 8) with strict rules
- PHP-CS-Fixer configuration with comprehensive rules
- CaptainHook Git hooks setup
  - pre-commit: PHP linting, PHP-CS-Fixer check, PHPCS
  - commit-msg: Conventional commits validation
  - pre-push: PHPStan analysis, PHPUnit tests
- GitHub Actions CI/CD workflows
  - Coding standards check
  - Static analysis
  - Tests on PHP 7.4, 8.0, 8.1, 8.2, 8.3
- GitHub Actions release workflow
- Comprehensive documentation
  - README.md with usage examples
  - LICENSE (MIT)

### Changed

- Updated composer.json with development dependencies
- Enhanced project description and keywords

### Fixed

- N/A

## [0.1.0] - TBD

### Added

- Initial release

[Unreleased]: https://github.com/vix-4800/phpcs-devstrict/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/vix-4800/phpcs-devstrict/releases/tag/v0.1.0
