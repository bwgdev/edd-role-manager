<?php
/**
 * PHPStan Stubs
 *
 * Type stubs for EDD classes and functions not covered by phpstan-wordpress.
 *
 * @package EDD_Role_Manager
 */

// Plugin constants - define first before ABSPATH check.
if ( ! defined( 'EDD_RM_VERSION' ) ) {
	define( 'EDD_RM_VERSION', '1.0.0' );
}
if ( ! defined( 'EDD_RM_PLUGIN_DIR' ) ) {
	define( 'EDD_RM_PLUGIN_DIR', '' );
}
if ( ! defined( 'EDD_RM_PLUGIN_URL' ) ) {
	define( 'EDD_RM_PLUGIN_URL', '' );
}
if ( ! defined( 'EDD_RM_PLUGIN_FILE' ) ) {
	define( 'EDD_RM_PLUGIN_FILE', '' );
}

// Prevent further stub definitions if running in WordPress context.
if ( defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Easy Digital Downloads main class stub.
 */
class Easy_Digital_Downloads {}

/**
 * EDD Recurring main class stub.
 */
class EDD_Recurring {}

/**
 * EDD Subscription class stub.
 */
class EDD_Subscription {
	/** @var int */
	public $id;

	/** @var int */
	public $product_id;

	/** @var object{user_id: int} */
	public $customer;
}

/**
 * EDD Recurring Subscriber class stub.
 */
class EDD_Recurring_Subscriber {
	/** @var int */
	public $id;

	/**
	 * Constructor.
	 *
	 * @param int  $user_id User ID.
	 * @param bool $by_user_id Whether to look up by user ID.
	 */
	public function __construct( int $user_id, bool $by_user_id = false ) {}

	/**
	 * Get subscriptions.
	 *
	 * @param int      $number Number to retrieve (0 for all).
	 * @param string[] $status Status filter.
	 * @return EDD_Subscription[]
	 */
	public function get_subscriptions( int $number = 0, array $status = array() ): array {
		return array();
	}
}

/**
 * Get payment user ID.
 *
 * @param int $payment_id Payment ID.
 * @return int|false User ID or false.
 */
function edd_get_payment_user_id( int $payment_id ) {}

/**
 * Get payment cart details.
 *
 * @param int $payment_id Payment ID.
 * @return array<int, array{id: int, name: string, quantity: int, price: float}>|false
 */
function edd_get_payment_meta_cart_details( int $payment_id ) {}

/**
 * Get EDD Recurring instance.
 *
 * @return EDD_Recurring|null
 */
function edd_recurring() {}
