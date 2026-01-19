# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
