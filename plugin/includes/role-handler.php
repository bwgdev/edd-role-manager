<?php
/**
 * Role Handler
 *
 * Handles role assignment on purchase and expiration.
 *
 * @package EDD_Role_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// =============================================================================
// SETTINGS HELPERS
// =============================================================================

/**
 * Get plugin settings.
 *
 * @return array{qualifying_products: int[], grant_role: string, downgrade_role: string}
 */
function edd_rm_get_settings(): array {
	$defaults = array(
		'qualifying_products' => array(),
		'grant_role'          => 'subscriber',
		'downgrade_role'      => 'subscriber',
	);

	$settings = get_option( 'edd_rm_settings', $defaults );

	return wp_parse_args( $settings, $defaults );
}

/**
 * Get list of roles that are NOT allowed to be assigned.
 *
 * @return string[] Array of excluded role slugs.
 */
function edd_rm_get_excluded_roles(): array {
	return array( 'administrator', 'editor', 'author', 'contributor' );
}

/**
 * Check if a role is allowed to be assigned.
 *
 * @param string $role Role slug to check.
 * @return bool True if role is allowed.
 */
function edd_rm_is_role_allowed( string $role ): bool {
	$excluded = edd_rm_get_excluded_roles();
	return ! in_array( $role, $excluded, true );
}

// =============================================================================
// PURCHASE HANDLER
// =============================================================================

/**
 * Handle role assignment on purchase completion.
 *
 * @param int $payment_id The payment ID.
 * @return void
 */
function edd_rm_handle_purchase( int $payment_id ): void {
	$settings = edd_rm_get_settings();

	// No qualifying products configured.
	if ( empty( $settings['qualifying_products'] ) ) {
		return;
	}

	// Get user ID from payment.
	$user_id = edd_get_payment_user_id( $payment_id );

	if ( ! $user_id || $user_id <= 0 ) {
		return;
	}

	// Get cart items.
	$cart_items = edd_get_payment_meta_cart_details( $payment_id );

	if ( ! is_array( $cart_items ) ) {
		return;
	}

	// Check if any cart item is a qualifying product.
	$has_qualifying_product = false;

	foreach ( $cart_items as $item ) {
		$product_id = absint( $item['id'] ?? 0 );

		if ( in_array( $product_id, $settings['qualifying_products'], true ) ) {
			$has_qualifying_product = true;
			break;
		}
	}

	if ( ! $has_qualifying_product ) {
		return;
	}

	// Validate role before assignment (defense in depth).
	$grant_role = $settings['grant_role'];

	if ( ! edd_rm_is_role_allowed( $grant_role ) ) {
		return;
	}

	// Verify user exists.
	$user = get_userdata( $user_id );

	if ( ! $user ) {
		return;
	}

	// Assign the role.
	$wp_user = new WP_User( $user_id );
	$wp_user->set_role( $grant_role );
}
add_action( 'edd_complete_purchase', 'edd_rm_handle_purchase', 100, 1 );

// =============================================================================
// EXPIRATION HANDLER
// =============================================================================

/**
 * Handle role downgrade on subscription expiration.
 *
 * @param int              $sub_id       The subscription ID.
 * @param EDD_Subscription $subscription The subscription object.
 * @return void
 */
function edd_rm_handle_expiration( int $sub_id, $subscription ): void {
	$settings = edd_rm_get_settings();

	// No qualifying products configured.
	if ( empty( $settings['qualifying_products'] ) ) {
		return;
	}

	// Get user ID from subscription.
	$user_id = absint( $subscription->customer->user_id ?? 0 );

	if ( ! $user_id ) {
		return;
	}

	// Check if user has any OTHER active subscriptions to qualifying products.
	$has_other_qualifying_sub = edd_rm_user_has_qualifying_subscription( $user_id, $sub_id );

	if ( $has_other_qualifying_sub ) {
		return;
	}

	// Validate role before assignment (defense in depth).
	$downgrade_role = $settings['downgrade_role'];

	if ( ! edd_rm_is_role_allowed( $downgrade_role ) ) {
		return;
	}

	// Verify user exists.
	$user = get_userdata( $user_id );

	if ( ! $user ) {
		return;
	}

	// Assign the downgrade role.
	$wp_user = new WP_User( $user_id );
	$wp_user->set_role( $downgrade_role );
}
add_action( 'edd_subscription_expired', 'edd_rm_handle_expiration', 100, 2 );

/**
 * Check if user has any active subscriptions to qualifying products.
 *
 * @param int $user_id        The user ID to check.
 * @param int $exclude_sub_id Subscription ID to exclude from check (the one that just expired).
 * @return bool True if user has other qualifying active subscriptions.
 */
function edd_rm_user_has_qualifying_subscription( int $user_id, int $exclude_sub_id = 0 ): bool {
	$settings = edd_rm_get_settings();

	if ( empty( $settings['qualifying_products'] ) ) {
		return false;
	}

	// Get subscriber object.
	if ( ! class_exists( 'EDD_Recurring_Subscriber' ) ) {
		return false;
	}

	$subscriber = new EDD_Recurring_Subscriber( $user_id, true );

	if ( ! $subscriber->id ) {
		return false;
	}

	// Get active subscriptions.
	$subscriptions = $subscriber->get_subscriptions( 0, array( 'active' ) );

	if ( empty( $subscriptions ) ) {
		return false;
	}

	// Check if any active subscription is for a qualifying product.
	foreach ( $subscriptions as $sub ) {
		// Skip the subscription that triggered this check.
		if ( absint( $sub->id ) === $exclude_sub_id ) {
			continue;
		}

		$product_id = absint( $sub->product_id ?? 0 );

		if ( in_array( $product_id, $settings['qualifying_products'], true ) ) {
			return true;
		}
	}

	return false;
}
