<?php
/**
 * It fires on plugin activation.

 * @package Freesoul Deactivate Plugins
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

add_role(
	'fdp_plugins_manager',
	__( 'FDP Manager', 'freesoul-deactivate-plugins' ),
	array(
		'read'                 => true,
		'view_admin_dashboard' => true,
		'activate_plugins'     => true,
		'deactivate_plugins'   => true,
		'view_fdp_settings'    => true,
	)
);
add_role(
	'fdp_plugins_viewer',
	__( 'FDP Viewer', 'freesoul-deactivate-plugins' ),
	array(
		'read'                 => true,
		'view_admin_dashboard' => true,
		'activate_plugins'     => false,
		'deactivate_plugins'   => false,
		'view_fdp_settings'    => true,
	)
);
if ( eos_dp_install_mu_plugin( true ) ) {
	eos_dp_update_option( 'eos_dp_version', EOS_DP_VERSION );
}

if ( function_exists( 'eos_dp_update_fdp_admin_menu' ) && function_exists( 'eos_dp_user_headers' ) ) {
	eos_dp_update_fdp_admin_menu(
		eos_dp_user_headers(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			),
			true
		)
	);
}

eos_dp_update_plugins_slugs_names();
