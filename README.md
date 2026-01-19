# Role Manager for Easy Digital Downloads

Automatically manages WordPress user roles based on Easy Digital Downloads subscription status.

## Description

When users purchase qualifying subscription products, they receive a designated role (e.g., "member"). When all qualifying subscriptions expire, they're downgraded to a default role (e.g., "subscriber").

This is useful for sites using role-based content restriction plugins like Content Control.

## Requirements

- WordPress 5.0+
- PHP 8.0+
- Easy Digital Downloads
- EDD Recurring Payments

## Installation

1. Download the latest release from [GitHub Releases](https://github.com/bwgdev/edd-role-manager/releases)
2. Upload to your WordPress plugins directory
3. Activate the plugin
4. Configure at Downloads → Role Manager

## Configuration

### Settings

- **Qualifying Products**: Select which EDD subscription products should grant the elevated role
- **Role to Grant**: The role assigned when a qualifying subscription is purchased
- **Downgrade Role**: The role assigned when no qualifying subscriptions remain active

### Security

The plugin prevents accidental privilege escalation by excluding administrative roles (administrator, editor, author, contributor) from the role dropdowns.

## How It Works

### On Purchase

When a customer completes a purchase containing a qualifying subscription product:
1. The plugin checks if they have a WordPress user account
2. Assigns the configured "grant" role

### On Subscription Expiration

When a subscription expires (not just cancellation - expiration of the paid period):
1. The plugin checks if the user has any OTHER active subscriptions to qualifying products
2. If no qualifying subscriptions remain, assigns the configured "downgrade" role

## Hooks Reference

The plugin uses these EDD hooks:

- `edd_complete_purchase` - Fires when payment is confirmed
- `edd_subscription_expired` - Fires when a subscription period ends

## Updates

The plugin checks GitHub releases for updates and will notify you in the WordPress admin when a new version is available.

## License

GPL-2.0-or-later

## Credits

Developed by [Boston Web Group](https://bostonwebgroup.com)
