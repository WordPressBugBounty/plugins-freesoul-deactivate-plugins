<?php
/**
 * Class for Edit by Post Type settings.
 *
 * @package Freesoul Deactivate Plugins
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Class FDP Edit By Post Type
 *
 * @package  Freesoul Deactivate Plugins\Classes
 */
class FDP_Backend_Singles_By_Post_Type_Page extends Eos_Fdp_Matrix_Page {

	/**
	 * Output before section.
	 *
	 * @param string $page_slug Page slug.
	 * @since  2.6.8
	 */
	public function before_section( $page_slug ) {
		$this->section_id = 'eos-dp-by-admin-post-type-section';
		?>
		<p class="description" style="margin:16px 0;max-width:960px">
			<?php esc_html_e( 'Disable plugins on admin edit screens (post.php?post=XX&action=edit) based on the post type. Each row applies to all edit screens for that post type.', 'freesoul-deactivate-plugins' ); ?>
		</p>
		<?php
	}

	/**
	 * Table body.
	 *
	 * @param string $page_slug Page slug.
	 * @since  2.6.8
	 */
	public function tableBody( $page_slug ) {
		$admin_setts  = eos_dp_get_option( 'eos_dp_admin_setts' );
		$admin_theme  = eos_dp_get_option( 'eos_dp_admin_theme' );
		$post_types   = get_post_types( array( 'show_ui' => true ), 'objects' );
		$row          = 1;

		wp_nonce_field( 'eos_dp_pro_auto_settings_admin', 'eos_dp_pro_auto_settings_admin' );

		uasort(
			$post_types,
			function( $a, $b ) {
				return strcasecmp( $a->labels->singular_name, $b->labels->singular_name );
			}
		);

		foreach ( $post_types as $post_type ) {
			if ( ! isset( $post_type->labels->singular_name ) ) {
				continue;
			}

			$post_key       = sanitize_key( $post_type->name );
			$admin_page_key = 'single_' . $post_key;
			$title          = sprintf(
				/* translators: %s is the singular name of the post type. */
				esc_attr__( 'Edit Single %s', 'freesoul-deactivate-plugins' ),
				$post_type->labels->singular_name
			);
			$last_posts     = wp_get_recent_posts(
				array(
					'numberposts' => 1,
					'post_type'   => $post_key,
					'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				)
			);
			if ( ! empty( $last_posts ) ) {
				$edit_url = get_edit_post_link( $last_posts[0]['ID'], 'raw' );
			} else {
				$edit_url = admin_url( 'post-new.php?post_type=' . $post_key );
			}

			if ( ! $edit_url ) {
				continue;
			}

			$values = isset( $admin_setts[ $admin_page_key ] ) ? explode( ',', $admin_setts[ $admin_page_key ] ) : array_fill( 0, count( $this->active_plugins ), ',' );
			$this->render_row( $admin_page_key, $title, $edit_url, $values, $admin_theme, $row );
			++$row;
		}
	}

	/**
	 * Render a matrix row for one post type edit screen.
	 *
	 * @param string $admin_page_key Settings key.
	 * @param string $title          Row title.
	 * @param string $edit_url       Preview/edit URL.
	 * @param array  $values         Disabled plugins for the row.
	 * @param array  $admin_theme    Theme activation settings.
	 * @param int    $row            Row index.
	 * @since  2.6.8
	 */
	private function render_row( $admin_page_key, $title, $edit_url, $values, $admin_theme, $row ) {
		$admin_page_key_attr = sanitize_key(
			str_replace(
				'.',
				'-',
				str_replace(
					'admin.php?page=',
					'eos_dp_tlp-',
					str_replace( admin_url(), '', $edit_url )
				)
			)
		);
		$args                = array(
			'eos_dp_debug'   => 'no_errors',
			'admin_page_key' => $admin_page_key_attr,
			'eos_dp_pro_id'  => md5( EOS_DP_PRO_TESTING_UNIQUE_ID ),
			'test_id'        => time(),
		);
		$preview_url         = str_replace( '&amp;', '&', add_query_arg( $args, wp_nonce_url( $edit_url, 'eos_dp_preview', 'eos_dp_preview' ) ) );
		?>
		<tr class="eos-dp-admin-row eos-dp-post-row fdp-edit-single" data-admin="<?php echo esc_attr( $admin_page_key ); ?>">
			<td class="eos-dp-post-name-wrp">
				<span class="fdp-row-actions-ico dashicons dashicons-plus" title="<?php esc_attr_e( 'Action buttons', 'freesoul-deactivate-plugins' ); ?>"></span>
				<span class="eos-dp-not-active-wrp"><input title="<?php
				/* translators: %s is the row title. */
				printf( esc_attr__( 'Activate/deactivate all plugins for %s', 'freesoul-deactivate-plugins' ), esc_attr( $title ) ); ?>" data-row="<?php echo esc_attr( $row ); ?>" class="eos-dp-global-chk-row" type="checkbox" /></span>
				<a class="eos-dp-title" href="<?php echo esc_url( $edit_url ); ?>" target="_blank"><?php echo esc_html( $title ); ?></a>
				<div class="eos-dp-actions">
					<a class="eos-dp-view fdp-has-tooltip" href="<?php echo esc_url( $edit_url ); ?>" target="_blank">
						<span class="dashicons dashicons-visibility"></span>
						<div class="fdp-tooltip"><?php esc_html_e( 'View page with plugins loaded according to the saved options.', 'freesoul-deactivate-plugins' ); ?></div>
					</a>
					<a data-page_speed_insights="false" class="eos-dp-preview fdp-has-tooltip" oncontextmenu="return false;" href="<?php echo esc_url( $preview_url ); ?>" target="_blank">
						<span class="dashicons dashicons-search"></span>
						<div class="fdp-tooltip"><?php esc_html_e( 'Preview the page loading plugins according to the settings you see now on this row', 'freesoul-deactivate-plugins' ); ?></div>
					</a>
					<a data-page_speed_insights="false" class="eos-dp-preview fdp-has-tooltip" oncontextmenu="return false;" href="<?php echo esc_url( add_query_arg( 'js', 'off', $preview_url ) ); ?>" target="_blank">
						<span class="dashicons dashicons-search">
							<span class="eos-dp-no-js">JS</span>
						</span>
						<div class="fdp-tooltip"><?php esc_html_e( 'Preview the page loading plugins and the theme according to the settings you see now on this row and disable JavaScript execution', 'freesoul-deactivate-plugins' ); ?></div>
					</a>
					<?php do_action( 'eos_dp_action_buttons' ); ?>
					<a href="#" class="eos-dp-pro-autosettings fdp-has-tooltip">
						<span class="dashicons dashicons-plugins-checked"></span>
						<div class="fdp-tooltip"><?php esc_html_e( 'Suggest unused plugins', 'freesoul-deactivate-plugins' ); ?></div>
					</a>
					<?php do_action( 'eos_dp_backend_actions' ); ?>
					<a class="eos-dp-invert-selection fdp-has-tooltip" href="#"><span class="dashicons"><span style="display:inline-block"><span class="fdp-invert-up"></span><span class="fdp-invert-down"></span></span></span>
						<div class="fdp-tooltip"><?php esc_html_e( 'Invert selection', 'freesoul-deactivate-plugins' ); ?></div>
					</a>
					<a class="eos-dp-copy fdp-has-tooltip" href="#"><span class="dashicons dashicons-admin-page"></span>
						<div class="fdp-tooltip"><?php esc_html_e( 'Copy settings for this row', 'freesoul-deactivate-plugins' ); ?></div>
					</a>
					<a class="eos-dp-paste fdp-has-tooltip" href="#"><span class="dashicons dashicons-category"></span>
						<div class="fdp-tooltip"><?php esc_html_e( 'Paste previously copied row settings', 'freesoul-deactivate-plugins' ); ?></div>
					</a>
					<?php do_action( 'eos_dp_archive_action_buttons' ); ?>
				</div>
			</td>
			<?php
			$k = 0;
			foreach ( $this->active_plugins as $plugin ) {
				if ( in_array( $plugin, array_keys( $this->plugins_by_dirs ), true ) ) {
					$extra_class = EOS_DP_PLUGIN_BASE_NAME === $plugin ? ' eos-hidden' : '';
					?>
					<td class="center<?php
					echo ! in_array( $plugin, $values, true ) ? ' eos-dp-active' : '';
					echo esc_attr( $extra_class );
					?>" data-path="<?php echo esc_attr( $plugin ); ?>">
						<div class="eos-dp-td-chk-wrp eos-dp-td-admin-chk-wrp">
							<input class="eos-dp-row-<?php echo esc_attr( $row ); ?> eos-dp-col-<?php echo esc_attr( $k + 1 ); ?> eos-dp-col-<?php echo esc_attr( $k + 1 ) . '-' . esc_attr( $admin_page_key ); ?>" data-checked="<?php echo in_array( $plugin, $values, true ) ? 'checked' : 'not-checked'; ?>" type="checkbox"<?php echo in_array( $plugin, $values, true ) ? ' checked' : ''; ?> />
						</div>
					</td>
					<?php
					++$k;
				}
			}
			?>
			<td class="center<?php echo ! isset( $admin_theme[ $admin_page_key ] ) || $admin_theme[ $admin_page_key ] ? ' eos-dp-active' : ''; ?>">
				<div class="eos-dp-td-chk-wrp eos-dp-td-admin-chk-wrp">
					<input class="eos-dp-row-theme eos-dp-col-<?php echo esc_attr( $k + 1 ); ?> eos-dp-col-<?php echo esc_attr( $k + 1 ) . '-' . esc_attr( $admin_page_key ); ?>" data-checked="checked" type="checkbox" checked />
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Return array of nonces.
	 *
	 * @since  2.6.8
	 * @return array
	 */
	public function get_nonces_map() {
		$nonces = parent::get_nonces_map();
		$nonces['eos_dp_admin_by_post_type'] = 'eos_dp_admin_setts';
		return $nonces;
	}
}
