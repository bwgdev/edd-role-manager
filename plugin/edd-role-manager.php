<?php
/**
 * Plugin Name: Role Manager for Easy Digital Downloads
 * Plugin URI: https://github.com/bwgdev/edd-role-manager
 * Description: Automatically manages user roles based on EDD subscription status.
 * Version: 1.2.0
 * Requires at least: 5.0
 * Requires PHP: 8.0
 * Author: Boston Web Group
 * Author URI: https://bostonwebgroup.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: edd-role-manager
 *
 * @package EDD_Role_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// =============================================================================
// PLUGIN CONSTANTS
// =============================================================================

define( 'EDD_RM_VERSION', '1.2.0' );
define( 'EDD_RM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EDD_RM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'EDD_RM_PLUGIN_FILE', __FILE__ );

// =============================================================================
// DEPENDENCY CHECK
// =============================================================================

/**
 * Check if Easy Digital Downloads is active.
 *
 * @return bool True if EDD is active.
 */
function edd_rm_is_edd_active(): bool {
	return class_exists( 'Easy_Digital_Downloads' );
}

/**
 * Check if EDD Recurring Payments is active.
 *
 * @return bool True if EDD Recurring is active.
 */
function edd_rm_is_edd_recurring_active(): bool {
	return class_exists( 'EDD_Recurring' );
}

/**
 * Display admin notice if dependencies are missing.
 *
 * @return void
 */
function edd_rm_dependency_notice(): void {
	$missing = array();

	if ( ! edd_rm_is_edd_active() ) {
		$missing[] = 'Easy Digital Downloads';
	}

	if ( ! edd_rm_is_edd_recurring_active() ) {
		$missing[] = 'EDD Recurring Payments';
	}

	if ( empty( $missing ) ) {
		return;
	}

	$message = sprintf(
		/* translators: %s: comma-separated list of missing plugins */
		esc_html__( 'Role Manager for Easy Digital Downloads requires the following plugins to be installed and active: %s', 'edd-role-manager' ),
		implode( ', ', $missing )
	);

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html( $message )
	);
}

// =============================================================================
// PLUGIN INITIALIZATION
// =============================================================================

/**
 * Initialize the plugin after all plugins are loaded.
 *
 * @return void
 */
function edd_rm_init(): void {
	// Check dependencies.
	if ( ! edd_rm_is_edd_active() || ! edd_rm_is_edd_recurring_active() ) {
		add_action( 'admin_notices', 'edd_rm_dependency_notice' );
		return;
	}

	// Load plugin components.
	require_once EDD_RM_PLUGIN_DIR . 'includes/role-handler.php';
	require_once EDD_RM_PLUGIN_DIR . 'admin/settings.php';
	require_once EDD_RM_PLUGIN_DIR . 'includes/update-checker.php';
}
add_action( 'plugins_loaded', 'edd_rm_init' );

// =============================================================================
// ACTIVATION / DEACTIVATION
// =============================================================================

/**
 * Plugin activation hook.
 *
 * @return void
 */
function edd_rm_activate(): void {
	// Set default options if not exists.
	if ( false === get_option( 'edd_rm_settings' ) ) {
		$defaults = array(
			'qualifying_products' => array(),
			'grant_role'          => 'subscriber',
		);
		add_option( 'edd_rm_settings', $defaults );
	}
}
register_activation_hook( __FILE__, 'edd_rm_activate' );

/**
 * Plugin deactivation hook.
 *
 * @return void
 */
function edd_rm_deactivate(): void {
	// Nothing to clean up on deactivation.
	// Settings are preserved for reactivation.
}
register_deactivation_hook( __FILE__, 'edd_rm_deactivate' );
