<?php
/**
 * Template Edit by Post Type.
 *
 * @package Freesoul Deactivate Plugins
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Callback for the Edit by Post Type settings page.
 */
function eos_dp_admin_by_post_type_callback() {
	if ( ! defined( 'FDP_PRO_ACTIVE' ) || ! FDP_PRO_ACTIVE ) {
		wp_die(
			esc_html__( 'This feature requires Freesoul Deactivate Plugins PRO.', 'freesoul-deactivate-plugins' ),
			esc_html__( 'PRO feature', 'freesoul-deactivate-plugins' ),
			array( 'response' => 403 )
		);
	}

	require_once EOS_DP_PLUGIN_DIR . '/admin/classes/class-fdp-backend-singles-by-post-type.php';
	new FDP_Backend_Singles_By_Post_Type_Page( 'eos_dp_admin_by_post_type' );
}
