<?php
/**
 * Template Firing Order.

 * @package Freesoul Deactivate Plugins
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Callback for firing order settings page.
function eos_dp_firing_order_callback() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		?>
		<h2><?php esc_html_e( 'Sorry, you do not have permission to access this page.', 'freesoul-deactivate-plugins' ); ?></h2>
		<?php
		return;
	}

	$pro_basename = defined( 'EOS_DP_PRO_PLUGIN_BASE_NAME' ) ? EOS_DP_PRO_PLUGIN_BASE_NAME : 'freesoul-deactivate-plugins-pro/freesoul-deactivate-plugins-pro.php';
	$untouchables = array(
		EOS_DP_PLUGIN_BASE_NAME,
		$pro_basename,
		'query-monitor/query-monitor.php',
	);
	/**
	 * Plugins that cannot be dragged in the firing order UI.
	 *
	 * @param array $untouchables Plugin basenames.
	 */
	$untouchables = apply_filters( 'eos_dp_firing_order_untouchable_plugins', $untouchables );

	wp_nonce_field( 'eos_dp_firing_order_setts', 'eos_dp_firing_order_setts' );
	eos_dp_alert_plain_permalink();
	eos_dp_navigation();

	// Use the full active list, including FDP Free/PRO (eos_dp_active_plugins() excludes them on purpose).
	$plugins = isset( $GLOBALS['fdp_all_plugins'] ) && is_array( $GLOBALS['fdp_all_plugins'] )
		? array_values( array_unique( $GLOBALS['fdp_all_plugins'] ) )
		: array_values( array_unique( (array) get_option( 'active_plugins', array() ) ) );
	if ( function_exists( 'eos_dp_apply_firing_order' ) ) {
		$plugins = eos_dp_apply_firing_order( $plugins );
	}
	$plugins_by_dirs = eos_dp_get_plugins();
	?>
	<style id="fdp-firing-order-css">
	.eos-dp-firing-order.ui-sortable .eos-dp-plugin{margin:15px 0;padding:5px}
	.eos-dp-firing-order .eos-dp-not-touchable{opacity:0.75;cursor:default}
	.eos-dp-firing-order .eos-dp-not-touchable .dashicons-move{visibility:hidden}
	</style>
	<section id="eos-dp-by-firing_order-section" class="eos-dp-section">
		<h2><?php esc_html_e( 'You can change the plugin firing order by dragging and moving the plugins.', 'freesoul-deactivate-plugins' ); ?></h2>
		<p><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Plugins should use action hooks to run code in the desired order. Change the firing order if you really don\'t have other cleaner solutions.', 'freesoul-deactivate-plugins' ); ?></p>
		<p><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Newly activated plugins are appended automatically at the end of the firing order. Drag them only if you need a different position, then save.', 'freesoul-deactivate-plugins' ); ?></p>
		<div class="eos-dp-firing-order" style="margin-top:32px">
			<?php
			foreach ( $plugins as $plugin ) {
				$plugin = sanitize_text_field( $plugin );
				if ( '' === $plugin ) {
					continue;
				}
				$is_fdp_family = ( EOS_DP_PLUGIN_BASE_NAME === $plugin || $pro_basename === $plugin );
				$known_plugin  = $is_fdp_family || isset( $plugins_by_dirs[ $plugin ] ) || file_exists( WP_PLUGIN_DIR . '/' . $plugin );
				if ( ! $known_plugin ) {
					continue;
				}
				$details_url = add_query_arg(
					array(
						'tab'         => 'plugin-information',
						'plugin'      => dirname( $plugin ),
						'TB_iframe'   => true,
						'eos_dp'      => $plugin,
						'eos_dp_info' => 'true',
					),
					admin_url( 'plugin-install.php' )
				);
				$plugin_name = strtoupper( eos_dp_get_plugin_name_by_slug( $plugin ) );
				if ( '' === $plugin_name || $plugin === $plugin_name ) {
					$plugin_name = strtoupper( str_replace( array( '-', '_' ), ' ', dirname( $plugin ) ) );
				}
				$not_touchable = in_array( $plugin, $untouchables, true );
				?>
			<div class="eos-dp-plugin<?php echo $not_touchable ? ' eos-dp-not-touchable' : ''; ?>" data-path="<?php echo esc_attr( $plugin ); ?>">
				<span class="dashicons dashicons-move"></span>
				<span class="eos-dp-fo-plugin-wrp">
					<span class="dashicons dashicons-admin-plugins"></span>
					<span><a class="eos-dp-no-decoration" title="<?php
					// translators: %s is the plugin name.
					printf( esc_attr__( 'View details of %s', 'freesoul-deactivate-plugins' ), esc_attr( $plugin_name ) );
					?>" href="<?php echo esc_url( $details_url ); ?>" target="_blank"><?php echo esc_html( $plugin_name ); ?></a></span>
				</span>
			</div>
				<?php
			}
			?>
		</div>
		<?php eos_dp_save_button(); ?>
	</section>
	<?php
}
