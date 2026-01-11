# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.1.0-beta] - Unreleased

### Added
- Support for PHP 8.3 and 8.4
- Support for Laravel 11 and 12
- Multi-PHP Docker setup with configurable `PHP_VERSION` build argument
- GitHub Actions CI workflow testing all PHP/Laravel combinations (9 total)
- Development section in README with local testing instructions
- CI matrix documentation in README

### Changed
- Simplified Dockerfile for easier multi-version testing
- Updated docker-compose.yaml to support `PHP_VERSION` environment variable

### Breaking Changes
- **Minimum PHP version is now 8.2** (dropped PHP 8.1 support)
- **Minimum Laravel version is now 10.0** (dropped Laravel 9 support)
- Docker service renamed from `app` to `php`

### Fixed
- PHP 8.2 dynamic property deprecation in DebugHealthCheckTest
- PHP 8.4 implicit nullable parameter deprecations in source files
- Migrated phpunit.xml to latest schema
- Replaced deprecated `@test` annotations with `#[Test]` attributes (PHPUnit 12 compatibility)

## [4.0.0] - 2025-01-10

### Changed
- Updated to PHP ^8.1
- Updated to Laravel ^9.0|^10.0|^11.0

## [3.5.1] and earlier

See [GitHub Releases](https://github.com/generationtux/php-healthz/releases) for previous versions.
