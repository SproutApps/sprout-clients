<?php


/**
 * Sprout Clients API Controller
 *
 * @package Sprout_Clients
 * @subpackage HSD Admin Settings
 */
class SC_Settings extends SC_Controller {

	public static function init() {
		// Register Settings
		self::register_settings();

		// Redirect after activation
		add_action( 'admin_init', array( __CLASS__, 'redirect_on_activation' ), 20, 0 );

	}

	//////////////
	// Settings //
	//////////////

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {

		// Settings page
		$args = array(
			'slug' => self::SETTINGS_PAGE,
			'title' => 'Sprout Clients Settings',
			'menu_title' => 'Sprout Clients',
			'tab_title' => __( 'General Settings' , 'sprout-invoices' ),
			'weight' => 10,
			'reset' => false,
			'section' => SC_Controller::SETTINGS_PAGE,
			);
		do_action( 'sprout_settings_page', $args );

		// Welcome page
		$args = array(
			'slug' => 'sc_getting_started',
			'title' => 'Sprout Client Settings',
			'menu_title' => 'Sprout Client',
			'tab_title' => 'Getting Started',
			'weight' => 1,
			'reset' => false,
			'tab_only' => true,
			'section' => SC_Controller::SETTINGS_PAGE,
			'callback' => array( __CLASS__, 'welcome_page' ),
			);
		do_action( 'sprout_settings_page', $args );
	}

	///////////////////
	// Welcome Page //
	///////////////////

	/**
	 * Dashboard
	 * @return string
	 */
	public static function welcome_page() {
		// TODO REMOVE - don't flush the rewrite rules every time this page is loaded,
		// this will help those that have already installed though.
		flush_rewrite_rules();

		if ( isset( $_GET['whats-new'] ) ) {
			self::load_view( 'admin/whats-new/'.$_GET['whats-new'].'.php', array() );
			return;
		}
		if ( SC_FREE_TEST ) {
			self::load_view( 'admin/sprout-clients-dashboard-free.php', array() );
		} else {
			self::load_view( 'admin/sprout-clients-dashboard.php', array() );
		}

	}



	/**
	 * Check if the plugin has been activated, redirect if true and delete the option to prevent a loop.
	 * @package Sprout_Invoices
	 * @subpackage Base
	 * @ignore
	 */
	public static function redirect_on_activation() {
		if ( get_option( 'sc_do_activation_redirect', false ) ) {
			// Flush the rewrite rules after SI is activated.
			flush_rewrite_rules();
			delete_option( 'sc_do_activation_redirect' );
			wp_redirect( admin_url( 'admin.php?page=' . self::APP_DOMAIN . '/sprout_client_settings&tab=sc_getting_started' ) );
		}
	}
}
