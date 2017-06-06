<?php

/**
 * Fixes other plugins issues.
 *
 * @package Sprout_Clients
 * @subpackage Compatibility
 */
class SC_Compatibility extends SC_Controller {

	public static function init() {
		// attempt to kill all select2 registrations on si_admin pages
		add_action( 'init', array( __CLASS__, 'deregister_select2' ), PHP_INT_MAX );
		// atttempt to kill all select2 registrations on si_admin pages REALLY LATE
		add_action( 'wp_print_scripts', array( __CLASS__, 'deenqueue_select2' ), PHP_INT_MAX );

		// WP SEO
		add_filter( 'init', array( __CLASS__, 'prevent_wpseo_from_being_assholes_about_admin_columns' ), 10 );
		add_filter( 'add_meta_boxes', array( __CLASS__, 'prevent_wpseo_from_being_assholes_about_private_cpts_metaboxes' ), 10 );
		// Gravity Forms fix
		add_filter( 'gform_display_add_form_button', array( __CLASS__, 'sc_maybe_remove_gravity_forms_add_button' ), 10, 1 );

		if ( class_exists( 'acf' ) ) {
			// ACF Fix
			add_filter( 'post_submitbox_start', array( __CLASS__, '_acf_post_submitbox_start' ) );

			add_action( 'init', array( __CLASS__, 'replace_older_select2_with_new' ), 5 );
		}

		if ( class_exists( 'Caldera_Forms' ) ) {
			add_action( 'init', array( __CLASS__, 'deregister_select2_for_caldera' ), 15 );
		}

		if ( function_exists( 'ultimatemember_activation_hook' ) ) {
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'unregister_select2_from_ultimate_member' ), 10 );
			add_action( 'do_meta_boxes', array( __CLASS__, 'remove_um_metabox' ), 9 );
		}

		// TC_back_pro_slider
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'deregister_select2_from_customizer' ), 100 );
		add_filter( 'add_meta_boxes', array( __CLASS__, 'prevent_slider_pro_adding_metaboxes' ), 100 );

		add_action( 'parse_query', array( __CLASS__, 'remove_seo_header_stuff' ) );
	}

	public static function deregister_select2() {
		if ( self::is_sc_admin() ) {
			wp_deregister_script( 'select2' );
			wp_deregister_style( 'select2' );
			// Register the SI version with the old handle
			wp_register_style( 'select2', SC_URL . '/resources/admin/plugins/select2/css/select2.min.css', null, self::SC_VERSION, false );
			wp_register_script( 'select2', SC_URL . '/resources/admin/plugins/select2/js/select2.min.js', array( 'jquery' ), self::SC_VERSION, false );

			wp_deregister_script( 'select2_4.0' );
			wp_deregister_style( 'select2_4.0_css' );
		}
	}

	public static function deenqueue_select2() {
		if ( self::is_sc_admin() ) {
			foreach ( wp_scripts()->queue as $handle ) {
				if ( strpos( $handle, 'select2' ) !== false && 'select2_4.0' !== $handle ) {
					wp_dequeue_script( $handle );
					// Register the SI version with the old handle
					wp_register_script( 'select2', SC_URL . '/resources/admin/plugins/select2/js/select2.min.js', array( 'jquery' ), self::SC_VERSION, false );

				}
			}

			foreach ( wp_styles()->queue as $handle ) {
				if ( strpos( $handle, 'select2' ) !== false && 'select2_4.0_css' !== $handle ) {
					wp_dequeue_style( $handle );
					wp_register_style( 'select2', SC_URL . '/resources/admin/plugins/select2/css/select2.min.css', null, self::SC_VERSION, false );
				}
			}
		}
	}

	public static function remove_seo_header_stuff() {
		if ( self::is_sc_admin() ) {
			add_filter( 'index_rel_link', '__return_false' );
			add_filter( 'parent_post_rel_link', '__return_false' );
			add_filter( 'start_post_rel_link', '__return_false' );
			add_filter( 'previous_post_rel_link', '__return_false' );
			add_filter( 'next_post_rel_link', '__return_false' );
		}
	}

	public static function prevent_wpseo_from_being_assholes_about_admin_columns() {
		if ( self::is_sc_admin() ) {
			// Disable Yoast admin columns.
			add_filter( 'wpseo_use_page_analyscs', '__return_false' );
		}
	}

	public static function prevent_wpseo_from_being_assholes_about_private_cpts_metaboxes() {
		if ( self::is_sc_admin() ) {
			// Disable Yoast metabox
			$cpts = array( Sprout_Client::POST_TYPE, 'sa_engagement', 'sc_message', SC_Record::POST_TYPE );
			foreach ( $cpts as $cpt ) {
				remove_meta_box( 'wpseo_meta', $cpt, 'normal' );
			}
		}
	}

	public static function sc_maybe_remove_gravity_forms_add_button( $is_post_edit_page ) {
		if ( is_admin() ) {
		    if ( strpos( get_post_type(), 'sa_' ) !== false ) {
		    	return false;
		    }
		}
		return $is_post_edit_page;
	}

	public static function _acf_post_submitbox_start() {
		if ( ! SC_Controller::is_sc_admin() ) {
			return;
		}
		?>
			<script type="text/javascript">
			(function($){
				acf.add_action('submit', function( $el ){
					$('input[type="submit"]').removeClass('disabled button-disabled button-primary-disabled');
				});
			})(jQuery);
			</script>
		<?php
	}

	public static function deregister_select2_for_caldera() {
		if ( self::is_sc_admin() ) {
			wp_deregister_script( 'cf-select2minjs' );
			wp_deregister_style( 'cf-select2css' );
		}
	}

	public static function deregister_select2_from_customizer() {
		if ( self::is_sc_admin() ) {
			wp_deregister_script( 'selecter-script' );
			wp_deregister_style( 'tc-select2-css' );
		}
	}

	public static function prevent_slider_pro_adding_metaboxes() {
		if ( self::is_sc_admin() ) {
			// Disable Yoast metabox
			$cpts = array( Sprout_Client::POST_TYPE, 'sa_engagement', 'sc_message', SC_Record::POST_TYPE );
			foreach ( $cpts as $cpt ) {
				remove_meta_box( 'layout_sectionid', $cpt, 'normal' );
				remove_meta_box( 'slider_sectionid', $cpt, 'normal' );
			}
		}
	}

	public static function replace_older_select2_with_new() {
		if ( self::is_sc_admin() ) {
			wp_deregister_script( 'select2' );
			wp_deregister_style( 'select2' );
			// Register the SC verscon with the old handle
			wp_register_style( 'select2', SC_URL . '/resources/admin/plugins/select2/css/select2.min.css', null, self::SC_VERSION, false );
			wp_register_script( 'select2', SC_URL . '/resources/admin/plugins/select2/js/select2.min.js', array( 'jquery' ), self::SC_VERSION, false );
		}
	}

	public static function unregister_select2_from_ultimate_member() {
		if ( self::is_sc_admin() ) {
			wp_deregister_script( 'um_minified' );
			wp_deregister_style( 'um_minified' );
		}
	}

	public static function remove_um_metabox() {
		$cpts = array( Sprout_Client::POST_TYPE, 'sa_engagement', 'sc_message', SC_Record::POST_TYPE );
		foreach ( $cpts as $cpt ) {
			remove_meta_box( 'um-admin-access-settings', $cpt, 'scde' );
		}
	}
}
