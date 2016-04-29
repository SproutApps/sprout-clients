<?php


/**
 * Updates class
 *
 * @package Sprout_Invoice
 * @subpackage Updates
 */
class SC_Free_License extends SC_Controller {
	const LICENSE_KEY_OPTION = 'sc_license_key';
	const LICENSE_UID_OPTION = 'sc_uid';
	const API_CB = 'https://sproutapps.co/';
	protected static $license_key;
	protected static $uid;

	public static function init() {
		self::$license_key = trim( get_option( self::LICENSE_KEY_OPTION, '' ) );
		self::$uid = trim( get_option( self::LICENSE_UID_OPTION, 0 ) );

		if ( is_admin() ) {
			// AJAX
			add_action( 'wp_ajax_sc_get_license',  array( __CLASS__, 'maybe_get_free_license' ), 10, 0 );
		}

		add_filter( 'sc_get_purchase_link', array( __CLASS__, 'add_uid_to_url' ) );
		add_filter( 'sc_get_sa_link', array( __CLASS__, 'add_uid_to_url' ) );

		// Messaging
		add_action( 'sc_settings_page',  array( __CLASS__, 'thank_for_registering' ), 10, 0 );

		//add_action( 'admin_notices',  array( __CLASS__, 'my_promo_message' ), 10, 0 );

		// callback for license
		add_action( 'admin_init', array( __CLASS__, 'init_sc_fs_callback' ) );

		self::register_settings();

	}

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {
		// Settings
		$settings = array(
			'sc_activation' => array(
				'title' => __( 'No Options?' , 'sprout-invoices' ),
				'weight' => 0,
				'tab' => SC_Controller::SETTINGS_PAGE,
				'callback' => array( __CLASS__, 'update_setting_description' ),
				'settings' => array(),
				),
			);
		do_action( 'sprout_settings', $settings, self::SETTINGS_PAGE );

	}

	public static function update_setting_description() {
		?>
		<p><?php printf( __( 'Yep. There are no options, which is pretty cool if you ask me. If you want to enable enagements and messaging (and enable some options) than you should purchase <a href="%s">the pro version</a>.', 'sprout-invoices' ), self::PLUGIN_URL, sc_get_purchase_link() ); ?></p>
		<p><?php printf( __( 'PSA: The future of <a href="%s">Sprout Clients</a> relies on happy customers supporting Sprout Apps by purchasing upgraded versions. If you like this free version of Sprout Clients please consider <a href="%s">purchasing an upgrade</a>.', 'sprout-invoices' ), self::PLUGIN_URL, sc_get_purchase_link() ); ?></p>
		<?php
	}

	public static function license_key() {
		return self::$license_key;
	}

	public static function uid() {
		return self::$uid;
	}

	public static function license_status() {
		return ( self::$license_key ) ? true : false;
	}

	///////////
	// AJAX //
	///////////

	public static function maybe_get_free_license() {
		if ( ! isset( $_REQUEST['security'] ) ) {
			self::ajax_fail( 'Forget something?' );
		}

		$nonce = $_REQUEST['security'];
		if ( ! wp_verify_nonce( $nonce, self::NONCE ) ) {
			self::ajax_fail( 'Not going to fall for it!' );
		}

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( ! isset( $_REQUEST['license'] ) ) {
			self::ajax_fail( 'No email submitted' );
		}

		if ( ! is_email( $_REQUEST['license'] ) ) {
			self::ajax_fail( 'No Email Submitted' );
		}

		$license_response = self::get_free_license( $_REQUEST['license'] );
		if ( is_object( $license_response ) ) {
			$message = __( 'Thank you for registering Sprout Clients with Sprout Apps.', 'sprout-clients' );
			$response = array(
					'license' => $license_response->license_key,
					'uid' => $license_response->uid,
					'response' => $message,
					'error' => ! isset( $license_response->license_key ),
				);

			update_option( self::LICENSE_KEY_OPTION, $license_response->license_key );
			update_option( self::LICENSE_UID_OPTION, $license_response->uid );
		} else {
			$message = __( 'License not created.', 'sprout-clients' );
			$response = array(
					'response' => $message,
					'error' => 1,
				);
		}

		header( 'Content-type: application/json' );
		echo wp_json_encode( $response );
		exit();
	}

	public static function thank_for_registering() {
		if ( ! self::$uid ) {
			return;
		}
	}

	////////////
	// Promos //
	////////////

	public static function my_promo_message() {
		if ( false === SI_Free_License::license_status() ) {
			return;
		}
		printf( '<div class="updated notice is-dismissible"><p><span class="icon-sproutapps-flat"></span><strong>Sprout Clients Pro Discount</strong>: Just <a href="%s">generate a free license key</a> for your site and a discount will be sent to you instantly.</p></div>', admin_url( 'admin.php?page=sprout-apps%2Fsettings&tab=dashboard' ) );
	}

	//////////////
	// Utility //
	//////////////


	public static function get_free_license( $license = '' ) {
		$first_name = '';
		$last_name = '';
		$user = get_user_by( 'email', $license );
		if ( is_a( $user, 'WP_User' ) ) {
			$first_name = $user->first_name;
			$last_name = $user->last_name;
		}

		// data to send in our API request
		$api_params = array(
			'action' => 'sgmnt_free_license',
			'item_name' => urlencode( self::PLUGIN_NAME ),
			'url' => urlencode( home_url() ),
			'uid' => $license,
			'first_name' => $first_name,
			'last_name' => $last_name,
		);

		// Call the custom API.
		$response = wp_safe_remote_get( add_query_arg( $api_params, self::API_CB . 'wp-admin/admin-ajax.php' ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false; }

		// decode the license data
		$license_response = json_decode( wp_remote_retrieve_body( $response ) );

		return $license_response;
	}

	public static function add_uid_to_url( $url = '' ) {
		if ( ! self::$uid ) {
			return $url;
		}
		return add_query_arg( array( 'suid' => self::$uid ), $url );
	}

	public static function init_sc_fs_callback() {
		if ( ! function_exists( 'sc_fs' ) ) {
			return;
		}
		if ( ! self::$uid && sc_fs()->is_registered() ) {
			self::after_sc_account_connection( sc_fs()->get_user() );
		}
	}

	public static function after_sc_account_connection( FS_User $user ) {
		if ( ! is_admin() ) {
			return;
		}

		$email = $user->email;

		if ( '' === $email ) {
			return;
		}
		$license = self::get_free_license( $email );
		update_option( self::LICENSE_KEY_OPTION, $license->license_key );
		update_option( self::LICENSE_UID_OPTION, $license->uid );
	}
}
