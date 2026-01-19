# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-01-19

### Added
- Support for EDD All Access products as qualifying products
- Role granted on purchase of qualifying All Access passes
- Role downgraded when All Access pass expires (if no other qualifying access remains)
- Combined "Qualifying Products" selector shows both subscription and All Access products

### Changed
- Renamed internal functions to reflect broader scope (subscriptions + All Access)
- Updated admin UI text to reference both subscriptions and All Access passes

## [1.0.2] - 2026-01-19

### Fixed
- Fixed release zip structure to include wrapper folder for proper WordPress plugin installation

## [1.0.1] - 2026-01-19

### Fixed
- Fixed detection of recurring products with variable pricing (was looking for wrong meta key)
- Products with per-price-option recurring settings now properly appear in Qualifying Products list

## [1.0.0] - 2026-01-19

### Added
- Admin settings page under Downloads → Role Manager
- Multi-select for qualifying subscription products
- Configurable grant role for active subscribers
- Configurable downgrade role when subscriptions expire
- Automatic role assignment on `edd_complete_purchase`
- Automatic role downgrade on `edd_subscription_expired`
- Smart check for other active qualifying subscriptions before downgrading
- Security: Excluded elevated roles (administrator, editor, author, contributor) from assignment
- Security: Defense-in-depth validation on settings save AND use
- GitHub auto-updater via plugin-update-checker
- PHPStan level 6 and WordPress Coding Standards compliance
