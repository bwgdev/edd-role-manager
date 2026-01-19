<?php
/**
 * Update Checker
 *
 * Configures GitHub auto-updates via Plugin Update Checker.
 *
 * @package EDD_Role_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Plugin Update Checker.
if ( file_exists( EDD_RM_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once EDD_RM_PLUGIN_DIR . 'vendor/autoload.php';
}

/**
 * Initialize the update checker.
 *
 * @return void
 */
function edd_rm_init_update_checker(): void {
	// Ensure the class exists (loaded via Composer autoload).
	if ( ! class_exists( 'YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory' ) ) {
		return;
	}

	$update_checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		'https://github.com/bwgdev/edd-role-manager',
		EDD_RM_PLUGIN_FILE,
		'edd-role-manager'
	);

	// Use releases as the source.
	$update_checker->getVcsApi()->enableReleaseAssets();
}
add_action( 'init', 'edd_rm_init_update_checker' );

/**
 * Add "Check for updates" link to plugin action links.
 *
 * @param string[] $links Array of plugin action links.
 * @return string[] Modified array of plugin action links.
 */
function edd_rm_add_update_check_link( array $links ): array {
	// Only add for users who can update plugins.
	if ( ! current_user_can( 'update_plugins' ) ) {
		return $links;
	}

	$update_url = wp_nonce_url(
		add_query_arg(
			array(
				'puc_check_for_updates' => 1,
				'puc_slug'              => 'edd-role-manager',
			),
			admin_url( 'plugins.php' )
		),
		'puc_check_for_updates'
	);

	$links['check_updates'] = sprintf(
		'<a href="%s">%s</a>',
		esc_url( $update_url ),
		esc_html__( 'Check for updates', 'edd-role-manager' )
	);

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( EDD_RM_PLUGIN_FILE ), 'edd_rm_add_update_check_link' );
