# EDD Role Manager - Project Context

## What This Is

WordPress plugin that manages user roles based on Easy Digital Downloads subscription status. Assigns a configurable role on purchase, downgrades when subscriptions expire.

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
- Role validated against allowed list before `set_role()` call

## Settings Storage

Option name: `edd_rm_settings`

```php
array(
    'qualifying_products' => array( 123, 456 ),  // EDD product IDs
    'grant_role'          => 'member',           // Role on purchase
    'downgrade_role'      => 'subscriber',       // Role on expiration
)
```

## Hooks Used

| Hook | When | Action |
|------|------|--------|
| `edd_complete_purchase` | Payment confirmed | Grant role if qualifying product |
| `edd_subscription_expired` | Subscription period ends | Downgrade if no other qualifying subs |

## Excluded Roles

These roles cannot be selected in settings (prevents privilege escalation):
- administrator
- editor
- author
- contributor

## Development

```bash
# Install dependencies
composer install

# Run code analysis
./dev-tools/analyze.sh

# Or individually
composer phpcs
composer phpstan
```

## Release Process

1. Update version in `plugin/edd-role-manager.php`
2. Update CHANGELOG.md
3. Commit changes
4. Run GitHub Actions workflow (manual dispatch)
5. Workflow creates release with zip attachment

## Linear Project

https://linear.app/bwg/project/edd-role-manager-f0e161f6a5ee
