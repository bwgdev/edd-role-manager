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

/**
 * Get variable prices for a download.
 *
 * @param int $download_id Download ID.
 * @return array<int, array{amount: string, name: string, recurring?: string}>
 */
function edd_get_variable_prices( int $download_id ): array {
	return array();
}

/**
 * Get EDD customer by field.
 *
 * @param string $field Field to search by (user_id, email, etc).
 * @param mixed  $value Value to search for.
 * @return object|false Customer object or false.
 */
function edd_get_customer_by( string $field, $value ) {}

// =============================================================================
// EDD ALL ACCESS STUBS
// =============================================================================

/**
 * EDD All Access main class stub.
 */
class EDD_All_Access {
	/**
	 * Get singleton instance.
	 *
	 * @return EDD_All_Access
	 */
	public static function instance(): EDD_All_Access {
		return new self();
	}
}

/**
 * EDD All Access Pass class stub.
 */
class EDD_All_Access_Pass {
	/** @var int */
	public $id;

	/** @var int */
	public $payment_id;

	/** @var int */
	public $download_id;

	/** @var int */
	public $price_id;

	/** @var string */
	public $status;

	/** @var object|null */
	public $payment;
}

/**
 * Get EDD All Access instance.
 *
 * @return EDD_All_Access
 */
function edd_all_access(): EDD_All_Access {
	return EDD_All_Access::instance();
}

/**
 * Check if user has an All Access pass.
 *
 * @param int    $user_id             User ID.
 * @param int    $download_id         Download ID to check.
 * @param int    $price_id            Price ID (0 for any).
 * @param string $required_pass_status Required status ('active', etc).
 * @return bool True if user has pass.
 */
function edd_all_access_user_has_pass( int $user_id, int $download_id, int $price_id = 0, string $required_pass_status = 'active' ): bool {
	return false;
}

/**
 * Get customer's All Access passes.
 *
 * @param object $customer EDD Customer object.
 * @return EDD_All_Access_Pass[]
 */
function edd_all_access_get_customer_passes( $customer ): array {
	return array();
}
