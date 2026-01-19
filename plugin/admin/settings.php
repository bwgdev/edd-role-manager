<?php
/**
 * Admin Settings Page
 *
 * Settings page under Downloads menu.
 *
 * @package EDD_Role_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// =============================================================================
// ADMIN MENU
// =============================================================================

/**
 * Add settings page to EDD menu.
 *
 * @return void
 */
function edd_rm_add_menu_page(): void {
	add_submenu_page(
		'edit.php?post_type=download',
		__( 'Role Manager', 'edd-role-manager' ),
		__( 'Role Manager', 'edd-role-manager' ),
		'manage_options',
		'edd-role-manager',
		'edd_rm_render_settings_page'
	);
}
add_action( 'admin_menu', 'edd_rm_add_menu_page', 100 );

// =============================================================================
// SETTINGS REGISTRATION
// =============================================================================

/**
 * Register plugin settings.
 *
 * @return void
 */
function edd_rm_register_settings(): void {
	register_setting(
		'edd_rm_settings_group',
		'edd_rm_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'edd_rm_sanitize_settings',
			'default'           => array(
				'qualifying_products' => array(),
				'grant_role'          => 'subscriber',
				'downgrade_role'      => 'subscriber',
			),
		)
	);
}
add_action( 'admin_init', 'edd_rm_register_settings' );

// =============================================================================
// SANITIZATION
// =============================================================================

/**
 * Sanitize settings on save.
 *
 * @param mixed $input The input to sanitize.
 * @return array{qualifying_products: int[], grant_role: string, downgrade_role: string}
 */
function edd_rm_sanitize_settings( $input ): array {
	$sanitized = array(
		'qualifying_products' => array(),
		'grant_role'          => 'subscriber',
		'downgrade_role'      => 'subscriber',
	);

	if ( ! is_array( $input ) ) {
		return $sanitized;
	}

	// Sanitize qualifying products.
	if ( isset( $input['qualifying_products'] ) && is_array( $input['qualifying_products'] ) ) {
		$product_ids = array_map( 'absint', $input['qualifying_products'] );
		// Validate each product exists and is a subscription product.
		$sanitized['qualifying_products'] = array_filter(
			$product_ids,
			'edd_rm_is_valid_subscription_product'
		);
	}

	// Sanitize and validate grant role.
	if ( isset( $input['grant_role'] ) ) {
		$grant_role = sanitize_text_field( $input['grant_role'] );
		if ( edd_rm_is_role_allowed( $grant_role ) && edd_rm_role_exists( $grant_role ) ) {
			$sanitized['grant_role'] = $grant_role;
		}
	}

	// Sanitize and validate downgrade role.
	if ( isset( $input['downgrade_role'] ) ) {
		$downgrade_role = sanitize_text_field( $input['downgrade_role'] );
		if ( edd_rm_is_role_allowed( $downgrade_role ) && edd_rm_role_exists( $downgrade_role ) ) {
			$sanitized['downgrade_role'] = $downgrade_role;
		}
	}

	return $sanitized;
}

/**
 * Check if a product ID is a valid EDD subscription product.
 *
 * @param int $product_id Product ID to check.
 * @return bool True if valid subscription product.
 */
function edd_rm_is_valid_subscription_product( int $product_id ): bool {
	if ( $product_id <= 0 ) {
		return false;
	}

	// Check if product exists.
	$product = get_post( $product_id );
	if ( ! $product || 'download' !== $product->post_type ) {
		return false;
	}

	// Check if it's a recurring product.
	if ( function_exists( 'edd_recurring' ) ) {
		$is_recurring = get_post_meta( $product_id, 'edd_recurring', true );
		return ! empty( $is_recurring ) && 'yes' === $is_recurring;
	}

	return true;
}

/**
 * Check if a WordPress role exists.
 *
 * @param string $role Role slug to check.
 * @return bool True if role exists.
 */
function edd_rm_role_exists( string $role ): bool {
	$wp_roles = wp_roles();
	return isset( $wp_roles->roles[ $role ] );
}

// =============================================================================
// HELPERS
// =============================================================================

/**
 * Get subscription products for the multi-select.
 *
 * @return array<int, string> Array of product ID => product title.
 */
function edd_rm_get_subscription_products(): array {
	$products = array();

	$args = array(
		'post_type'      => 'download',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'     => 'edd_recurring',
				'value'   => 'yes',
				'compare' => '=',
			),
		),
	);

	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			$products[ $post->ID ] = $post->post_title;
		}
	}

	wp_reset_postdata();

	return $products;
}

/**
 * Get assignable roles (excludes admin/editor/author/contributor).
 *
 * @return array<string, string> Array of role slug => role display name.
 */
function edd_rm_get_assignable_roles(): array {
	$excluded = edd_rm_get_excluded_roles();
	$wp_roles = wp_roles();
	$roles    = array();

	foreach ( $wp_roles->get_names() as $slug => $name ) {
		if ( ! in_array( $slug, $excluded, true ) ) {
			$roles[ $slug ] = translate_user_role( $name );
		}
	}

	return $roles;
}

// =============================================================================
// SETTINGS PAGE RENDER
// =============================================================================

/**
 * Render the settings page.
 *
 * @return void
 */
function edd_rm_render_settings_page(): void {
	// Capability check.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'edd-role-manager' ) );
	}

	$settings              = edd_rm_get_settings();
	$subscription_products = edd_rm_get_subscription_products();
	$assignable_roles      = edd_rm_get_assignable_roles();

	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<?php if ( empty( $subscription_products ) ) : ?>
			<div class="notice notice-warning">
				<p>
					<?php esc_html_e( 'No subscription products found. Please create at least one EDD product with recurring payments enabled.', 'edd-role-manager' ); ?>
				</p>
			</div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'edd_rm_settings_group' ); ?>

			<table class="form-table" role="presentation">
				<tbody>
					<!-- Qualifying Products -->
					<tr>
						<th scope="row">
							<label for="edd_rm_qualifying_products">
								<?php esc_html_e( 'Qualifying Products', 'edd-role-manager' ); ?>
							</label>
						</th>
						<td>
							<select
								name="edd_rm_settings[qualifying_products][]"
								id="edd_rm_qualifying_products"
								multiple="multiple"
								class="regular-text"
								style="min-width: 350px; min-height: 150px;"
							>
								<?php foreach ( $subscription_products as $id => $title ) : ?>
									<option
										value="<?php echo esc_attr( $id ); ?>"
										<?php echo in_array( $id, $settings['qualifying_products'], true ) ? 'selected' : ''; ?>
									>
										<?php echo esc_html( $title ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Select subscription products that grant the elevated role. Hold Ctrl/Cmd to select multiple.', 'edd-role-manager' ); ?>
							</p>
						</td>
					</tr>

					<!-- Role to Grant -->
					<tr>
						<th scope="row">
							<label for="edd_rm_grant_role">
								<?php esc_html_e( 'Role to Grant', 'edd-role-manager' ); ?>
							</label>
						</th>
						<td>
							<select
								name="edd_rm_settings[grant_role]"
								id="edd_rm_grant_role"
								class="regular-text"
							>
								<?php foreach ( $assignable_roles as $slug => $name ) : ?>
									<option
										value="<?php echo esc_attr( $slug ); ?>"
										<?php selected( $settings['grant_role'], $slug ); ?>
									>
										<?php echo esc_html( $name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Role assigned when a user purchases a qualifying subscription.', 'edd-role-manager' ); ?>
							</p>
						</td>
					</tr>

					<!-- Downgrade Role -->
					<tr>
						<th scope="row">
							<label for="edd_rm_downgrade_role">
								<?php esc_html_e( 'Downgrade Role', 'edd-role-manager' ); ?>
							</label>
						</th>
						<td>
							<select
								name="edd_rm_settings[downgrade_role]"
								id="edd_rm_downgrade_role"
								class="regular-text"
							>
								<?php foreach ( $assignable_roles as $slug => $name ) : ?>
									<option
										value="<?php echo esc_attr( $slug ); ?>"
										<?php selected( $settings['downgrade_role'], $slug ); ?>
									>
										<?php echo esc_html( $name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Role assigned when a user has no remaining active qualifying subscriptions.', 'edd-role-manager' ); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
