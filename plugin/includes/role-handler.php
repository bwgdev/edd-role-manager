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
 * @return array{qualifying_products: int[], grant_role: string}
 */
function edd_rm_get_settings(): array {
	$defaults = array(
		'qualifying_products' => array(),
		'grant_role'          => 'subscriber',
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

	// Add the role (keeps existing roles).
	$wp_user = new WP_User( $user_id );
	$wp_user->add_role( $grant_role );
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
function edd_rm_handle_subscription_expiration( int $sub_id, $subscription ): void {
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

	// Check if user has any OTHER active qualifying access (subscriptions or All Access passes).
	if ( edd_rm_user_has_qualifying_access( $user_id, $sub_id, 0 ) ) {
		return;
	}

	// Remove the granted role.
	edd_rm_remove_granted_role( $user_id );
}
add_action( 'edd_subscription_expired', 'edd_rm_handle_subscription_expiration', 100, 2 );

/**
 * Handle role downgrade on All Access pass expiration.
 *
 * @param EDD_All_Access_Pass  $pass The All Access pass object.
 * @param array<string, mixed> $args Additional arguments (unused, passed by hook).
 * @return void
 */
function edd_rm_handle_all_access_expiration( $pass, array $args ): void {
	$settings = edd_rm_get_settings();

	// No qualifying products configured.
	if ( empty( $settings['qualifying_products'] ) ) {
		return;
	}

	// Get user ID from payment.
	$payment_id = $pass->payment_id ?? 0;
	if ( ! $payment_id ) {
		return;
	}

	$user_id = edd_get_payment_user_id( $payment_id );
	if ( ! $user_id || $user_id <= 0 ) {
		return;
	}

	// Check if the expired pass was for a qualifying product.
	$product_id = absint( $pass->download_id ?? 0 );
	if ( ! in_array( $product_id, $settings['qualifying_products'], true ) ) {
		return;
	}

	// Check if user has any OTHER active qualifying access (subscriptions or All Access passes).
	if ( edd_rm_user_has_qualifying_access( $user_id, 0, $pass->id ?? 0 ) ) {
		return;
	}

	// Remove the granted role.
	edd_rm_remove_granted_role( $user_id );
}
add_action( 'edd_all_access_expired', 'edd_rm_handle_all_access_expiration', 100, 2 );

/**
 * Remove the granted role from a user.
 *
 * @param int $user_id The user ID to remove role from.
 * @return void
 */
function edd_rm_remove_granted_role( int $user_id ): void {
	$settings = edd_rm_get_settings();

	// Get the role to remove.
	$grant_role = $settings['grant_role'];

	if ( ! edd_rm_is_role_allowed( $grant_role ) ) {
		return;
	}

	// Verify user exists.
	$user = get_userdata( $user_id );

	if ( ! $user ) {
		return;
	}

	// Remove the granted role (keeps other roles).
	$wp_user = new WP_User( $user_id );
	$wp_user->remove_role( $grant_role );
}

/**
 * Check if user has any active qualifying access (subscriptions or All Access passes).
 *
 * @param int $user_id         The user ID to check.
 * @param int $exclude_sub_id  Subscription ID to exclude from check (the one that just expired).
 * @param int $exclude_pass_id All Access pass ID to exclude from check (the one that just expired).
 * @return bool True if user has other qualifying active access.
 */
function edd_rm_user_has_qualifying_access( int $user_id, int $exclude_sub_id = 0, int $exclude_pass_id = 0 ): bool {
	$settings = edd_rm_get_settings();

	if ( empty( $settings['qualifying_products'] ) ) {
		return false;
	}

	// Check for active subscriptions to qualifying products.
	if ( edd_rm_user_has_qualifying_subscription( $user_id, $exclude_sub_id ) ) {
		return true;
	}

	// Check for active All Access passes to qualifying products.
	if ( edd_rm_user_has_qualifying_all_access( $user_id, $exclude_pass_id ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user has any active subscriptions to qualifying products.
 *
 * @param int $user_id        The user ID to check.
 * @param int $exclude_sub_id Subscription ID to exclude from check (the one that just expired).
 * @return bool True if user has qualifying active subscriptions.
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

/**
 * Check if user has any active All Access passes to qualifying products.
 *
 * @param int $user_id         The user ID to check.
 * @param int $exclude_pass_id Pass ID to exclude from check (the one that just expired).
 * @return bool True if user has qualifying active All Access passes.
 */
function edd_rm_user_has_qualifying_all_access( int $user_id, int $exclude_pass_id = 0 ): bool {
	$settings = edd_rm_get_settings();

	if ( empty( $settings['qualifying_products'] ) ) {
		return false;
	}

	// Check if EDD All Access is active.
	if ( ! function_exists( 'edd_all_access_user_has_pass' ) ) {
		return false;
	}

	// Check each qualifying product to see if user has an active All Access pass for it.
	foreach ( $settings['qualifying_products'] as $product_id ) {
		// Use EDD All Access function to check if user has active pass for this product.
		if ( edd_all_access_user_has_pass( $user_id, $product_id, 0, 'active' ) ) {
			// If we're excluding a specific pass, we need to verify this isn't that pass.
			if ( $exclude_pass_id > 0 ) {
				// Get customer passes to check if this is the excluded one.
				$passes = edd_rm_get_user_passes_for_product( $user_id, $product_id, $exclude_pass_id );
				if ( ! empty( $passes ) ) {
					return true;
				}
			} else {
				return true;
			}
		}
	}

	return false;
}

/**
 * Get user's active All Access passes for a specific product, excluding a specific pass.
 *
 * @param int $user_id         The user ID.
 * @param int $product_id      The product ID to check.
 * @param int $exclude_pass_id Pass ID to exclude.
 * @return EDD_All_Access_Pass[] Array of active passes (excluding the specified one).
 */
function edd_rm_get_user_passes_for_product( int $user_id, int $product_id, int $exclude_pass_id ): array {
	if ( ! function_exists( 'edd_all_access_get_customer_passes' ) ) {
		return array();
	}

	// Get EDD customer ID from user ID.
	$customer = edd_get_customer_by( 'user_id', $user_id );
	if ( ! $customer ) {
		return array();
	}

	$passes = edd_all_access_get_customer_passes( $customer );
	if ( empty( $passes ) || ! is_array( $passes ) ) {
		return array();
	}

	$matching_passes = array();

	foreach ( $passes as $pass ) {
		// Skip the excluded pass.
		if ( absint( $pass->id ?? 0 ) === $exclude_pass_id ) {
			continue;
		}

		// Check if this pass is for the product and is active.
		$pass_product_id = absint( $pass->download_id ?? 0 );
		$pass_status     = $pass->status ?? '';

		if ( $pass_product_id === $product_id && 'active' === $pass_status ) {
			$matching_passes[] = $pass;
		}
	}

	return $matching_passes;
}
