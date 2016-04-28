<?php

/**
 * Admin capabilities controller.
 *
 * @package Sprout_Invoice
 * @subpackage Capabilities
 */
class SC_Admin_Capabilities extends SC_Controller {

	public static function init() {
		add_action( 'sc_plugin_activation_hook', array( __CLASS__, 'maybe_add_caps' ), 100 );
		add_action( 'sc_plugin_deactivation_hook', array( __CLASS__, 'remove_caps' ) );
	}

	public static function maybe_add_caps( $new_version = 0 ) {
		$sc_version = get_option( 'si_current_version', self::SC_VERSION );
		if ( version_compare( 2.0, $sc_version, '<' ) ) {
			self::add_caps();
		}
	}

	public static function sc_caps() {
		$caps = array(
			'administrator' => array(
					'manage_sprout_clients_options',
					'manage_sprout_clients_records',
					'edit_sprout_clients',
					'publish_sprout_clients',
				),
			'editor' => array(
					'edit_sprout_clients',
					'delete_sprout_clients',
					'publish_sprout_clients',
				),
			'author' => array(
					'edit_sprout_clients',
					'delete_sprout_clients',
					'publish_sprout_clients',
				),
			'contributer' => array(
					'edit_sprout_clients',
				),
			);
		return $caps;
	}

	/**
	 * Add new capabilities
	 */
	public static function add_caps() {
		global $wp_roles;
		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			foreach ( self::sc_caps() as $role => $new_caps ) {
				foreach ( $new_caps as $new_cap ) {
					$wp_roles->add_cap( $role, $new_cap );
				}
			}
		}
		error_log( 'add caps: ' . print_r( $wp_roles, true ) );
	}


	/**
	 * Remove core post type capabilities (called on uninstall)
	 */
	public static function remove_caps() {
		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			foreach ( self::sc_caps() as $role => $new_caps ) {
				foreach ( $new_caps as $new_cap ) {
					$wp_roles->remove_cap( $role, $new_cap );
				}
			}
		}
	}
}
