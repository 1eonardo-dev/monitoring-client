# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Configurable API path (`path`, default `/api/v1/events`).

### Changed

- README examples use positional arguments for PHP 7.4 compatibility.

## [1.0.0] - 2026-09-20

### Added

- Core `MonitoringClient` speaking the Monitoring Platform schema directly
  against `POST /api/v1/events`.
- Stream-based HTTP transport (`StreamTransport`) with a pluggable
  `Transport` interface (no `curl` dependency).
- `Event` and `Config` value objects.
- Laravel adapter: auto-discovered service provider, `Monitoring` facade and
  publishable config (`config/monitoring.php`).
- Yii2 adapter: `MonitoringComponent`.
- Unit test suite (PHPUnit) covering the core client and DTOs.
