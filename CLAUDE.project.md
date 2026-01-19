# EDD Role Manager - Project Context

## What This Is

WordPress plugin that manages user roles based on Easy Digital Downloads purchase status. Supports both EDD Recurring subscriptions and EDD All Access passes. Adds a configurable role on purchase, removes it when all qualifying access expires.

## Key Files

| File | Purpose |
|------|---------|
| `plugin/edd-role-manager.php` | Main plugin file, bootstrap, constants |
| `plugin/admin/settings.php` | Admin settings page under Downloads menu |
| `plugin/includes/role-handler.php` | Purchase and expiration hooks |
| `plugin/includes/update-checker.php` | GitHub auto-updater config |

## Tech Stack

- PHP 8.0+
- WordPress 5.0+
- Easy Digital Downloads (required)
- EDD Recurring Payments (required)
- EDD All Access (optional, supported)
- Plugin Update Checker (via Composer)

## Code Standards

- Function prefix: `edd_rm_`
- Text domain: `edd-role-manager`
- PHPStan level 6
- WordPress Coding Standards (PHPCS)

### Security Requirements

- All admin functions check `current_user_can('manage_options')`
- Settings form uses nonces
- All input sanitized before use
- All output escaped before display
- Role validated against allowed list before assignment

## Settings Storage

Option name: `edd_rm_settings`

```php
array(
    'qualifying_products' => array( 123, 456 ),  // EDD product IDs (subscriptions or All Access)
    'grant_role'          => 'member',           // Role added on purchase, removed on expiration
)
```

## Hooks Used

| Hook | When | Action |
|------|------|--------|
| `edd_complete_purchase` | Payment confirmed | Add role if qualifying product |
| `edd_subscription_expired` | Subscription ends | Remove role if no other qualifying access |
| `edd_all_access_expired` | All Access pass ends | Remove role if no other qualifying access |

## Role Behavior

- **On purchase:** Role is ADDED (user keeps existing roles)
- **On expiration:** Role is REMOVED (only if no other qualifying subscriptions or All Access passes remain)
- Uses `WP_User::add_role()` and `WP_User::remove_role()`

## Excluded Roles

These roles cannot be selected in settings (prevents privilege escalation):
- administrator
- editor
- author
- contributor

## Development

```bash
# Install dependencies
cd plugin && composer install

# Run code analysis
./plugin/vendor/bin/phpcs --standard=WordPress plugin/includes plugin/admin
./plugin/vendor/bin/phpstan analyse --configuration=dev-tools/phpstan.neon
```

## Release Process

1. Update version in `plugin/edd-role-manager.php` (header + constant)
2. Update CHANGELOG.md
3. Commit changes
4. Tag: `git tag v1.x.x && git push origin main --tags`
5. Trigger GitHub Actions workflow: `gh workflow run release.yml -f version=1.x.x`

## Linear Project

https://linear.app/bwg/project/edd-role-manager-f0e161f6a5ee
