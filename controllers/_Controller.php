<?php

/**
 * A base class from which all other controllers should be derived
 *
 * @package Sprout_Clients
 * @subpackage Controller
 */
abstract class SC_Controller extends Sprout_Clients {
	const CRON_HOOK = 'sc_cron';
	const DAILY_CRON_HOOK = 'sc_daily_cron';
	const DEFAULT_TEMPLATE_DIRECTORY = 'sc_templates';
	const SETTINGS_PAGE = 'sprout_client_settings';
	const NONCE = 'sprout_clients_controller_nonce';
	const PRIVATE_NOTES_TYPE = 'sa_private_notes';

	private static $template_path = self::DEFAULT_TEMPLATE_DIRECTORY;
	private static $shortcodes = array();

	public static function init() {
		if ( is_admin() ) {
			// On Activation
			add_action( 'sc_plugin_activation_hook', array( __CLASS__, 'sprout_clients_activated' ) );

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_resources' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue' ), 20 );
		}

		// Register Shortcodes
		add_action( 'sc_shortcode', array( __CLASS__, 'register_shortcode' ), 0, 3 );
		// Add shortcodes
		add_action( 'init', array( __CLASS__, 'add_shortcodes' ) );

		// Cron
		add_filter( 'cron_schedules', array( __CLASS__, 'sc_cron_schedule' ) );
		add_action( 'init', array( __CLASS__, 'set_schedule' ), 10, 0 );

	}

	/////////////////
	// Shortcodes //
	/////////////////

	/**
	 * Wrapper for the add_shorcode function WP provides
	 * @param string the shortcode
	 * @param array $callback
	 * @param array $args FUTURE
	 */
	public static function register_shortcode( $tag = '', $callback = array(), $args = array() ) {
		// FUTURE $args
		self::$shortcodes[ $tag ] = $callback;
	}

	/**
	 * Loop through registered shortcodes and use the WP function.
	 * @return
	 */
	public static function add_shortcodes(){
		foreach ( self::$shortcodes as $tag => $callback ) {
			add_shortcode( $tag, $callback );
		}
	}

	public static function get_shortcodes() {
		return self::$shortcodes;
	}


	/**
	 * Template path for templates/views, default to 'invoices'.
	 *
	 * @return string self::$template_path the folder
	 */
	public static function get_template_path() {
		return apply_filters( 'sc_template_path', self::$template_path );
	}

	/**
	 * Fire actions based on plugin being updated.
	 * @return
	 */
	public static function sprout_clients_activated() {
		add_option( 'sc_do_activation_redirect', true );
		// Get the previous version number
		$sc_version = get_option( 'sc_current_version', self::SC_VERSION );
		if ( version_compare( $sc_version, self::SC_VERSION, '<' ) ) { // If an upgrade create some hooks
			do_action( 'sc_version_upgrade', $sc_version );
			do_action( 'sc_version_upgrade_'.$sc_version );
		}
		// Set the new version number
		update_option( 'sc_current_version', self::SC_VERSION );
	}



	public static function register_resources() {

		// Select2
		wp_register_style( 'select2_4.0_css', SC_URL . '/resources/admin/plugins/select2/css/select2.min.css', null, self::SC_VERSION, false );
		wp_register_script( 'select2_4.0', SC_URL . '/resources/admin/plugins/select2/js/select2.min.js', array( 'jquery' ), self::SC_VERSION, false );

		// Redactor
		wp_register_script( 'redactor', SC_URL . '/resources/admin/plugins/redactor/redactor.min.js', array( 'jquery' ), self::SC_VERSION );
		wp_register_style( 'redactor', SC_URL . '/resources/admin/plugins/redactor/redactor.css', array(), self::SC_VERSION );

		// Dropdown
		wp_register_style( 'sa_dropdown_css', SC_URL . '/resources/admin/plugins/dropdown/jquery.dropdown.min.css', array(), self::SC_VERSION, false );
		wp_register_script( 'sa_dropdown', SC_URL . '/resources/admin/plugins/dropdown/jquery.dropdown.min.js', array( 'jquery' ), self::SC_VERSION, false );

		// Admin
		wp_register_script( 'sprout_clients', SC_URL . '/resources/admin/js/sprout-clients.js', array( 'jquery', 'select2_4.0', 'sa_dropdown' ), self::SC_VERSION );
		wp_register_style( 'sprout_clients', SC_URL . '/resources/admin/css/sprout-clients.css', array(), self::SC_VERSION );

		wp_register_script( 'sprout_engagements', SC_URL . '/resources/admin/js/sprout-engagements.js', array( 'jquery', 'select2_4.0', 'sa_dropdown' ), self::SC_VERSION );

		wp_register_script( 'sprout_messages', SC_URL . '/resources/admin/js/sprout-messages.js', array( 'jquery', 'select2_4.0', 'sa_dropdown', 'redactor' ), self::SC_VERSION );

	}

	public static function admin_enqueue() {
		$screen = get_current_screen();
		$screen_post_type = str_replace( 'edit-', '', $screen->id );
		if (
			! in_array( $screen_post_type, array( Sprout_Client::POST_TYPE, 'sa_engagement' ) ) &&
			strpos( $screen->base, self::APP_DOMAIN ) === false
			) {
			return;
		}
		wp_enqueue_script( 'select2_4.0' );
		wp_enqueue_style( 'select2_4.0_css' );

		wp_enqueue_script( 'sa_dropdown' );
		wp_enqueue_style( 'sa_dropdown_css' );

		wp_enqueue_style( 'sprout_clients' );

		if ( Sprout_Client::POST_TYPE === $screen_post_type ) {
			wp_enqueue_script( 'sprout_clients' );
			wp_enqueue_script( 'sprout_messages' );

			wp_enqueue_script( 'redactor' );
			wp_enqueue_style( 'redactor' );
		}

		if ( 'sa_engagement' === $screen_post_type ) {
			wp_enqueue_script( 'sprout_engagements' );
		}

		$_sprout_clients = array(
			'security' => wp_create_nonce( self::NONCE ),
			'spinner' => '<span class="sc_spinner sc_inline_spinner"></span>',
		);

		if ( in_array( $screen_post_type, array( Sprout_Client::POST_TYPE, 'sa_engagement' ) ) ) {
			if ( ! SC_FREE_TEST && file_exists( SC_PATH.'/resources/admin/plugins/redactor/redactor.min.js' ) ) {
				$_sprout_clients['redactor'] = true;
				wp_enqueue_script( 'redactor' );
				wp_enqueue_style( 'redactor' );
			}
		}

		wp_localize_script( 'sprout_clients', '_sprout_clients', apply_filters( 'sc_scripts_localization', $_sprout_clients ) );
		wp_localize_script( 'sprout_engagements', '_sprout_clients', apply_filters( 'sc_scripts_localization', $_sprout_clients ) );
		wp_localize_script( 'sprout_messages', '_sprout_clients', apply_filters( 'sc_scripts_localization', $_sprout_clients ) );
	}

	/**
	 * Filter WP Cron schedules
	 * @param  array $schedules
	 * @return array
	 */
	public static function sc_cron_schedule( $schedules ) {
		$schedules['minute'] = array(
			'interval' => 60,
			'display' => __( 'Once a Minute' )
		);
		$schedules['quarterhour'] = array(
			'interval' => 900,
			'display' => __( '15 Minutes' )
		);
		$schedules['halfhour'] = array(
			'interval' => 1800,
			'display' => __( 'Twice Hourly' )
		);
		return $schedules;
	}

	/**
	 * schedule wp events for wpcron.
	 */
	public static function set_schedule() {
		if ( self::DEBUG ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
		}
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			$interval = apply_filters( 'sc_set_schedule', 'quarterhour' );
			wp_schedule_event( time(), $interval, self::CRON_HOOK );
		}
		if ( ! wp_next_scheduled( self::DAILY_CRON_HOOK ) ) {
			wp_schedule_event( time(), 'daily', self::DAILY_CRON_HOOK );
		}
	}

	/**
	 * Display the template for the given view
	 *
	 * @static
	 * @param string  $view
	 * @param array   $args
	 * @param bool    $allow_theme_override
	 * @return void
	 */
	public static function load_view( $view, $args, $allow_theme_override = true ) {
		// whether or not .php was added
		if ( substr( $view, -4 ) != '.php' ) {
			$view .= '.php';
		}
		$file = SC_PATH.'/views/'.$view;
		if ( $allow_theme_override && defined( 'TEMPLATEPATH' ) ) {
			$file = self::locate_template( array( $view ), $file );
		}
		$file = apply_filters( 'sprout_clients_template_'.$view, $file );
		$args = apply_filters( 'load_view_args_'.$view, $args, $allow_theme_override );
		if ( ! empty( $args ) ) { extract( $args ); }
		if ( self::DEBUG ) {
			include $file;
		}
		else {
			include $file;
		}
	}

	/**
	 * Return a template as a string
	 *
	 * @static
	 * @param string  $view
	 * @param array   $args
	 * @param bool    $allow_theme_override
	 * @return string
	 */
	protected static function load_view_to_string( $view, $args, $allow_theme_override = true ) {
		ob_start();
		self::load_view( $view, $args, $allow_theme_override );
		return ob_get_clean();
	}

	/**
	 * Locate the template file, either in the current theme or the public views directory
	 *
	 * @static
	 * @param array   $possibilities
	 * @param string  $default
	 * @return string
	 */
	protected static function locate_template( $possibilities, $default = '' ) {
		$possibilities = apply_filters( 'sprout_clients_template_possibilities', $possibilities );
		$possibilities = array_filter( $possibilities );
		// check if the theme has an override for the template
		$theme_overrides = array();
		foreach ( $possibilities as $p ) {
			$theme_overrides[] = self::get_template_path().'/'.$p;
		}
		if ( $found = locate_template( $theme_overrides, false ) ) {
			return $found;
		}

		// check for it in the templates directory
		foreach ( $possibilities as $p ) {
			if ( file_exists( SC_PATH.'/views/templates/'.$p ) ) {
				return SC_PATH.'/views/templates/'.$p;
			}
		}

		// we don't have it
		return $default;
	}

	//////////////
	// Utility //
	//////////////

	public static function is_sc_admin() {
		$bool = false;
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return $bool;
		}

		// Options
		if ( isset( $_GET['page'] ) && strpos( $_GET['page'] , self::APP_DOMAIN ) !== false ) {
			$bool = true;
		}

		global $current_screen;
		if ( isset( $current_screen->id ) && strpos( $current_screen->id, self::APP_DOMAIN ) !== false ) {
			$bool = true;
		}

		if ( ! $bool ) { // check if admin for SI post types.
			$post_type = false;

			if ( isset( $current_screen->post_type ) ) {
				$post_type = $current_screen->post_type;
			} else {
				// Trying hard to figure out the post type if not yet set.
				$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : false;
				if ( $post_id ) {
					$post_type = get_post_type( $post_id );
				} else {
					$post_type = ( isset( $_REQUEST['post_type'] ) ) ? $_REQUEST['post_type'] : false ;
				}
			}
			if ( $post_type ) {
				if ( in_array( $post_type, array( Sprout_Client::POST_TYPE, Sprout_Engagement::POST_TYPE, SC_Message::POST_TYPE, SC_Record::POST_TYPE ) ) ) {
					return true;
				}
			}
		}
		return apply_filters( 'is_sc_admin', $bool );
	}

	/**
	 * Get default state options
	 * @param  array  $args
	 * @return array
	 */
	public static function get_state_options( $args = array() ) {
		$states = self::$grouped_states;
		if ( isset( $args['include_option_none'] ) && $args['include_option_none'] ) {
			$states = array( __( 'Select' , 'sprout-invoices' ) => array( $args['include_option_none'] ) ) + $states;
		}
		$states = apply_filters( 'sprout_state_options', $states, $args );
		return $states;
	}

	/**
	 * Get default countries options
	 * @param  array  $args
	 * @return array
	 */
	public static function get_country_options( $args = array() ) {
		$countries = self::$countries;
		if ( isset( $args['include_option_none'] ) && $args['include_option_none'] ) {
			$countries = array( '' => $args['include_option_none'] ) + $countries;
		}
		$countries = apply_filters( 'sprout_country_options', $countries, $args );
		return $countries;
	}

	/**
	 * Standard Address Fields.
	 * Params are used for filter only.
	 * @param  integer $user_id
	 * @param  boolean $shipping
	 * @return array
	 */
	public static function get_standard_address_fields( $required = true, $user_id = 0 ) {
		$fields = array();
		$fields['first_name'] = array(
			'weight' => 50,
			'label' => __( 'First Name' , 'sprout-invoices' ),
			'placeholder' => __( 'First Name' , 'sprout-invoices' ),
			'type' => 'text',
			'required' => $required
		);
		$fields['last_name'] = array(
			'weight' => 51,
			'label' => __( 'Last Name' , 'sprout-invoices' ),
			'placeholder' => __( 'Last Name' , 'sprout-invoices' ),
			'type' => 'text',
			'required' => $required
		);
		$fields['street'] = array(
			'weight' => 60,
			'label' => __( 'Street Address' , 'sprout-invoices' ),
			'placeholder' => __( 'Street Address' , 'sprout-invoices' ),
			'type' => 'textarea',
			'rows' => 2,
			'required' => $required
		);
		$fields['city'] = array(
			'weight' => 65,
			'label' => __( 'City' , 'sprout-invoices' ),
			'placeholder' => __( 'City' , 'sprout-invoices' ),
			'type' => 'text',
			'required' => $required
		);

		$fields['postal_code'] = array(
			'weight' => 70,
			'label' => __( 'ZIP Code' , 'sprout-invoices' ),
			'placeholder' => __( 'ZIP Code' , 'sprout-invoices' ),
			'type' => 'text',
			'required' => $required
		);

		$fields['zone'] = array(
			'weight' => 75,
			'label' => __( 'State' , 'sprout-invoices' ),
			'type' => 'select-state',
			'options' => self::get_state_options( array( 'include_option_none' => ' -- '.__( 'State' , 'sprout-invoices' ).' -- ' ) ),
			'attributes' => array( 'class' => 'sa_select2' ),
			'required' => $required
		); // FUTURE: Add some JavaScript to switch between select box/text-field depending on country

		$fields['country'] = array(
			'weight' => 80,
			'label' => __( 'Country' , 'sprout-invoices' ),
			'type' => 'select',
			'required' => $required,
			'options' => self::get_country_options( array( 'include_option_none' => ' -- '.__( 'Country' , 'sprout-invoices' ).' -- ' ) ),
			'attributes' => array( 'class' => 'sa_select2' ),
		);
		return apply_filters( 'si_get_standard_address_fields', $fields, $required, $user_id );
	}

	public static function login_required( $redirect = '' ) {
		if ( ! get_current_user_id() && apply_filters( 'sc_login_required', true ) ) {
			if ( ! $redirect && self::using_permalinks() ) {
				$schema = is_ssl() ? 'https://' : 'http://';
				$redirect = $schema.$_SERVER['SERVER_NAME'].htmlspecialchars( $_SERVER['REQUEST_URI'] );
				if ( isset( $_REQUEST ) ) {
					$redirect = urlencode( add_query_arg( $_REQUEST, esc_url_raw( $redirect ) ) );
				}
			}
			wp_redirect( wp_login_url( $redirect ) );
			exit();
		}
		return true; // explicit return value, for the benefit of the router plugin
	}

	/**
	 * Is current site using permalinks
	 * @return bool
	 */
	public static function using_permalinks() {
		return get_option( 'permalink_structure' ) !== '';
	}

	/**
	 * Tell caching plugins not to cache the current page load
	 */
	public static function do_not_cache() {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
	}

	/**
	 * Tell caching plugins to clear their caches related to a post
	 *
	 * @static
	 * @param int $post_id
	 */
	public static function clear_post_cache( $post_id ) {
		if ( function_exists( 'wp_cache_post_change' ) ) {
			// WP Super Cache

			$GLOBALS['super_cache_enabled'] = 1;
			wp_cache_post_change( $post_id );

		} elseif ( function_exists( 'w3tc_pgcache_flush_post' ) ) {
			// W3 Total Cache

			w3tc_pgcache_flush_post( $post_id );

		}
	}

	public static function ajax_fail( $message = '', $json = true ) {
		if ( '' === $message ) {
			$message = __( 'Something failed.' , 'sprout-invoices' );
		}
		if ( $json ) { header( 'Content-type: application/json' ); }
		if ( self::DEBUG ) { header( 'Access-Control-Allow-Origin: *' ); }
		if ( $json ) {
			echo wp_json_encode( array( 'error' => 1, 'response' => $message ) );
		}
		else {
			echo esc_attr( $message );
		}
		exit();
	}

	/**
	 * Comparison function
	 */
	public static function sort_by_weight( $a, $b ) {
		if ( ! isset( $a['weight'] ) || ! isset( $b['weight'] ) ) {
			return 0; }

		if ( $a['weight'] === $b['weight'] ) {
			return 0;
		}
		return ( $a['weight'] < $b['weight'] ) ? -1 : 1;
	}

	/**
	 * Turn all URLs in clickable links.
	 *
	 * @param string $value
	 * @param array  $protocols  http/https, ftp, mail, twitter
	 * @param array  $attributes
	 * @param string $mode       normal or all
	 * @return string
	 */
	public static function linkify( $value, $protocols = array( 'http', 'mail' ), array $attributes = array() )
	{
		// Link attributes
		$attr = '';
		foreach ( $attributes as $key => $val ) {
			$attr = ' ' . $key . '="' . htmlentities( $val ) . '"';
		}

		$links = array();

		// Extract existing links and tags
		$value = preg_replace_callback( '~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) { return '<' . array_push( $links, $match[1] ) . '>'; }, $value );

		// Extract text links for each protocol
		foreach ( (array) $protocols as $protocol ) {
			switch ( $protocol ) {
				case 'http':
				case 'https':   $value = preg_replace_callback( '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { if ( $match[1] ) { $protocol = $match[1]; } $link = $match[2] ?: $match[3]; return '<' . array_push( $links, "<a $attr href=\"$protocol://$link\">$link</a>" ) . '>'; }, $value ); break;
				case 'mail':    $value = preg_replace_callback( '~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) { return '<' . array_push( $links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>" ) . '>'; }, $value ); break;
				case 'twitter': $value = preg_replace_callback( '~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) { return '<' . array_push( $links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1]  . "\">{$match[0]}</a>" ) . '>'; }, $value ); break;
				default: $value = preg_replace_callback( '~' . preg_quote( $protocol, '~' ) . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { return '<' . array_push( $links, "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>" ) . '>'; }, $value ); break;
			}
		}

		// Insert all link
		return preg_replace_callback( '/<(\d+)>/', function ($match) use (&$links) { return $links[$match[1] - 1]; }, $value );
	}

	/////////////
	// Buried //
	/////////////



	protected static $countries = array(
		'AF' => 'Afghanistan',
		'AX' => 'Aland Islands',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua and Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia, Plurinational State of',
		'BQ' => 'Bonaire, Sint Eustatius and Saba',
		'BA' => 'Bosnia and Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'BN' => 'Brunei Darussalam',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CA' => 'Canada',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos (Keeling) Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CG' => 'Congo',
		'CD' => 'Congo, The Democratic Republic of the',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'CI' => "Cote D'ivoire",
		'HR' => 'Croatia',
		'CU' => 'Cuba',
		'CW' => 'Curacao',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'GQ' => 'Equatorial Guinea',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands (Malvinas)',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GR' => 'Greece',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GG' => 'Guernsey',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard Island and McDonald Islands',
		'VA' => 'Holy See (Vatican City State)',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran, Islamic Republic of',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IM' => 'Isle of Man',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JE' => 'Jersey',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'KP' => "Korea, Democratic People's Republic of",
		'KR' => 'Korea, Republic of',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => "Lao People's Democratic Republic",
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libyan Arab Jamahiriya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macao',
		'MK' => 'Macedonia, The Former Yugoslav Republic of',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia, Federated States of',
		'MD' => 'Moldova, Republic of',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestinian Territory, Occupied',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PN' => 'Pitcairn',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'RE' => 'Reunion',
		'RO' => 'Romania',
		'RU' => 'Russian Federation',
		'RW' => 'Rwanda',
		'BL' => 'Saint Barthelemy',
		'SH' => 'Saint Helena, Ascension and Tristan Da Cunha',
		'KN' => 'Saint Kitts and Nevis',
		'LC' => 'Saint Lucia',
		'MF' => 'Saint Martin (French Part)',
		'PM' => 'Saint Pierre and Miquelon',
		'VC' => 'Saint Vincent and the Grenadines',
		'WS' => 'Samoa',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome and Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SX' => 'Sint Maarten (Dutch Part)',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia and the South Sandwich Islands',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard and Jan Mayen',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syrian Arab Republic',
		'TW' => 'Taiwan, Province of China',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania, United Republic of',
		'TH' => 'Thailand',
		'TL' => 'Timor-Leste',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad and Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks and Caicos Islands',
		'TV' => 'Tuvalu',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'GB' => 'United Kingdom',
		'US' => 'United States',
		'UM' => 'United States Minor Outlying Islands',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VE' => 'Venezuela,
		Bolivarian Republic of',
		'VN' => 'Viet Nam',
		'VG' => 'Virgin Islands, British',
		'VI' => 'Virgin Islands, U.S.',
		'WF' => 'Wallis and Futuna',
		'EH' => 'Western Sahara',
		'YE' => 'Yemen',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe'
	);

	protected static $states = array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AS' => 'American Samoa',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'AE' => 'Armed Forces - Europe',
		'AP' => 'Armed Forces - Pacific',
		'AA' => 'Armed Forces - USA/Canada',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FM' => 'Federated States of Micronesia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'GU' => 'Guam',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MH' => 'Marshall Islands',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'PR' => 'Puerto Rico',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VI' => 'Virgin Islands',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
		'canada' => '== Canadian Provinces ==',
		'AB' => 'Alberta',
		'BC' => 'British Columbia',
		'MB' => 'Manitoba',
		'NB' => 'New Brunswick',
		'NF' => 'Newfoundland',
		'NT' => 'Northwest Territories',
		'NS' => 'Nova Scotia',
		'NU' => 'Nunavut',
		'ON' => 'Ontario',
		'PE' => 'Prince Edward Island',
		'QC' => 'Quebec',
		'SK' => 'Saskatchewan',
		'YT' => 'Yukon Territory',
		'uk' => '== UK ==',
		'Avon' => 'Avon',
		'Bedfordshire' => 'Bedfordshire',
		'Berkshire' => 'Berkshire',
		'Borders' => 'Borders',
		'Buckinghamshire' => 'Buckinghamshire',
		'Cambridgeshire' => 'Cambridgeshire',
		'Central' => 'Central',
		'Cheshire' => 'Cheshire',
		'Cleveland' => 'Cleveland',
		'Clwyd' => 'Clwyd',
		'Cornwall' => 'Cornwall',
		'County Antrim' => 'County Antrim',
		'County Armagh' => 'County Armagh',
		'County Down' => 'County Down',
		'County Fermanagh' => 'County Fermanagh',
		'County Londonderry' => 'County Londonderry',
		'County Tyrone' => 'County Tyrone',
		'Cumbria' => 'Cumbria',
		'Derbyshire' => 'Derbyshire',
		'Devon' => 'Devon',
		'Dorset' => 'Dorset',
		'Dumfries and Galloway' => 'Dumfries and Galloway',
		'Durham' => 'Durham',
		'Dyfed' => 'Dyfed',
		'East Sussex' => 'East Sussex',
		'Essex' => 'Essex',
		'Fife' => 'Fife',
		'Gloucestershire' => 'Gloucestershire',
		'Grampian' => 'Grampian',
		'Greater Manchester' => 'Greater Manchester',
		'Gwent' => 'Gwent',
		'Gwynedd County' => 'Gwynedd County',
		'Hampshire' => 'Hampshire',
		'Herefordshire' => 'Herefordshire',
		'Hertfordshire' => 'Hertfordshire',
		'Highlands and Islands' => 'Highlands and Islands',
		'Humberside' => 'Humberside',
		'Isle of Wight' => 'Isle of Wight',
		'Kent' => 'Kent',
		'Lancashire' => 'Lancashire',
		'Leicestershire' => 'Leicestershire',
		'Lincolnshire' => 'Lincolnshire',
		'London' => 'London',
		'Lothian' => 'Lothian',
		'Merseyside' => 'Merseyside',
		'Mid Glamorgan' => 'Mid Glamorgan',
		'Norfolk' => 'Norfolk',
		'North Yorkshire' => 'North Yorkshire',
		'Northamptonshire' => 'Northamptonshire',
		'Northumberland' => 'Northumberland',
		'Nottinghamshire' => 'Nottinghamshire',
		'Oxfordshire' => 'Oxfordshire',
		'Powys' => 'Powys',
		'Rutland' => 'Rutland',
		'Shropshire' => 'Shropshire',
		'Somerset' => 'Somerset',
		'South Glamorgan' => 'South Glamorgan',
		'South Yorkshire' => 'South Yorkshire',
		'Staffordshire' => 'Staffordshire',
		'Strathclyde' => 'Strathclyde',
		'Suffolk' => 'Suffolk',
		'Surrey' => 'Surrey',
		'Tayside' => 'Tayside',
		'Tyne and Wear' => 'Tyne and Wear',
		'Warwickshire' => 'Warwickshire',
		'West Glamorgan' => 'West Glamorgan',
		'West Midlands' => 'West Midlands',
		'West Sussex' => 'West Sussex',
		'West Yorkshire' => 'West Yorkshire',
		'Wiltshire' => 'Wiltshire',
		'Worcestershire' => 'Worcestershire',
	);

	protected static $grouped_states = array(
		'United States' => array(
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AS' => 'American Samoa',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'AE' => 'Armed Forces - Europe',
			'AP' => 'Armed Forces - Pacific',
			'AA' => 'Armed Forces - USA/Canada',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FM' => 'Federated States of Micronesia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'GU' => 'Guam',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MH' => 'Marshall Islands',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'PR' => 'Puerto Rico',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VI' => 'Virgin Islands',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
		),
		'Canadian Provinces' => array(
			'AB' => 'Alberta',
			'BC' => 'British Columbia',
			'MB' => 'Manitoba',
			'NB' => 'New Brunswick',
			'NF' => 'Newfoundland',
			'NT' => 'Northwest Territories',
			'NS' => 'Nova Scotia',
			'NU' => 'Nunavut',
			'ON' => 'Ontario',
			'PE' => 'Prince Edward Island',
			'QC' => 'Quebec',
			'SK' => 'Saskatchewan',
			'YT' => 'Yukon Territory',
		),
		'UK' => array(
			'Avon' => 'Avon',
			'Bedfordshire' => 'Bedfordshire',
			'Berkshire' => 'Berkshire',
			'Borders' => 'Borders',
			'Buckinghamshire' => 'Buckinghamshire',
			'Cambridgeshire' => 'Cambridgeshire',
			'Central' => 'Central',
			'Cheshire' => 'Cheshire',
			'Cleveland' => 'Cleveland',
			'Clwyd' => 'Clwyd',
			'Cornwall' => 'Cornwall',
			'County Antrim' => 'County Antrim',
			'County Armagh' => 'County Armagh',
			'County Down' => 'County Down',
			'County Fermanagh' => 'County Fermanagh',
			'County Londonderry' => 'County Londonderry',
			'County Tyrone' => 'County Tyrone',
			'Cumbria' => 'Cumbria',
			'Derbyshire' => 'Derbyshire',
			'Devon' => 'Devon',
			'Dorset' => 'Dorset',
			'Dumfries and Galloway' => 'Dumfries and Galloway',
			'Durham' => 'Durham',
			'Dyfed' => 'Dyfed',
			'East Sussex' => 'East Sussex',
			'Essex' => 'Essex',
			'Fife' => 'Fife',
			'Gloucestershire' => 'Gloucestershire',
			'Grampian' => 'Grampian',
			'Greater Manchester' => 'Greater Manchester',
			'Gwent' => 'Gwent',
			'Gwynedd County' => 'Gwynedd County',
			'Hampshire' => 'Hampshire',
			'Herefordshire' => 'Herefordshire',
			'Hertfordshire' => 'Hertfordshire',
			'Highlands and Islands' => 'Highlands and Islands',
			'Humberside' => 'Humberside',
			'Isle of Wight' => 'Isle of Wight',
			'Kent' => 'Kent',
			'Lancashire' => 'Lancashire',
			'Leicestershire' => 'Leicestershire',
			'Lincolnshire' => 'Lincolnshire',
			'Lothian' => 'Lothian',
			'Merseyside' => 'Merseyside',
			'Mid Glamorgan' => 'Mid Glamorgan',
			'Norfolk' => 'Norfolk',
			'North Yorkshire' => 'North Yorkshire',
			'Northamptonshire' => 'Northamptonshire',
			'Northumberland' => 'Northumberland',
			'Nottinghamshire' => 'Nottinghamshire',
			'Oxfordshire' => 'Oxfordshire',
			'Powys' => 'Powys',
			'Rutland' => 'Rutland',
			'Shropshire' => 'Shropshire',
			'Somerset' => 'Somerset',
			'South Glamorgan' => 'South Glamorgan',
			'South Yorkshire' => 'South Yorkshire',
			'Staffordshire' => 'Staffordshire',
			'Strathclyde' => 'Strathclyde',
			'Suffolk' => 'Suffolk',
			'Surrey' => 'Surrey',
			'Tayside' => 'Tayside',
			'Tyne and Wear' => 'Tyne and Wear',
			'Warwickshire' => 'Warwickshire',
			'West Glamorgan' => 'West Glamorgan',
			'West Midlands' => 'West Midlands',
			'West Sussex' => 'West Sussex',
			'West Yorkshire' => 'West Yorkshire',
			'Wiltshire' => 'Wiltshire',
			'Worcestershire' => 'Worcestershire',
		)

	);

	protected static $locales = array(
		'Default' => '',
		'Albanian (Albania)' => 'sq_AL',
		'Albanian' => 'sq',
		'Arabic (Algeria)' => 'ar_DZ',
		'Arabic (Bahrain)' => 'ar_BH',
		'Arabic (Egypt)' => 'ar_EG',
		'Arabic (Iraq)' => 'ar_IQ',
		'Arabic (Jordan)' => 'ar_JO',
		'Arabic (Kuwait)' => 'ar_KW',
		'Arabic (Lebanon)' => 'ar_LB',
		'Arabic (Libya)' => 'ar_LY',
		'Arabic (Morocco)' => 'ar_MA',
		'Arabic (Oman)' => 'ar_OM',
		'Arabic (Qatar)' => 'ar_QA',
		'Arabic (Saudi Arabia)' => 'ar_SA',
		'Arabic (Sudan)' => 'ar_SD',
		'Arabic (Syria)' => 'ar_SY',
		'Arabic (Tunisia)' => 'ar_TN',
		'Arabic (United Arab Emirates)' => 'ar_AE',
		'Arabic (Yemen)' => 'ar_YE',
		'Arabic' => 'ar',
		'Belarusian (Belarus)' => 'be_BY',
		'Belarusian' => 'be',
		'Bulgarian (Bulgaria)' => 'bg_BG',
		'Bulgarian' => 'bg',
		'Catalan (Spain)' => 'ca_ES',
		'Catalan' => 'ca',
		'Chinese (China)' => 'zh_CN',
		'Chinese (Hong Kong)' => 'zh_HK',
		'Chinese (Singapore)' => 'zh_SG',
		'Chinese (Taiwan)' => 'zh_TW',
		'Chinese' => 'zh',
		'Croatian (Croatia)' => 'hr_HR',
		'Croatian' => 'hr',
		'Czech (Czech Republic)' => 'cs_CZ',
		'Czech' => 'cs',
		'Danish (Denmark)' => 'da_DK',
		'Danish' => 'da',
		'Dutch (Belgium)' => 'nl_BE',
		'Dutch (Netherlands)' => 'nl_NL',
		'Dutch' => 'nl',
		'English (Australia)' => 'en_AU',
		'English (Canada)' => 'en_CA',
		'English (India)' => 'en_IN',
		'English (Ireland)' => 'en_IE',
		'English (Malta)' => 'en_MT',
		'English (New Zealand)' => 'en_NZ',
		'English (Philippines)' => 'en_PH',
		'English (Singapore)' => 'en_SG',
		'English (South Africa)' => 'en_ZA',
		'English (United Kingdom)' => 'en_GB',
		'English (United States)' => 'en_US',
		'English' => 'en',
		'Estonian (Estonia)' => 'et_EE',
		'Estonian' => 'et',
		'Finnish (Finland)' => 'fi_FI',
		'Finnish' => 'fi',
		'French (Belgium)' => 'fr_BE',
		'French (Canada)' => 'fr_CA',
		'French (France)' => 'fr_FR',
		'French (Luxembourg)' => 'fr_LU',
		'French (Switzerland)' => 'fr_CH',
		'French' => 'fr',
		'German (Austria)' => 'de_AT',
		'German (Germany)' => 'de_DE',
		'German (Luxembourg)' => 'de_LU',
		'German (Switzerland)' => 'de_CH',
		'German' => 'de',
		'Greek (Cyprus)' => 'el_CY',
		'Greek (Greece)' => 'el_GR',
		'Greek' => 'el',
		'Hebrew (Israel)' => 'iw_IL',
		'Hebrew' => 'iw',
		'Hindi (India)' => 'hi_IN',
		'Hungarian (Hungary)' => 'hu_HU',
		'Hungarian' => 'hu',
		'Icelandic (Iceland)' => 'is_IS',
		'Icelandic' => 'is',
		'Indonesian (Indonesia)' => 'in_ID',
		'Indonesian' => 'in',
		'Irish (Ireland)' => 'ga_IE',
		'Irish' => 'ga',
		'Italian (Italy)' => 'it_IT',
		'Italian (Switzerland)' => 'it_CH',
		'Italian' => 'it',
		'Japanese (Japan)' => 'ja_JP',
		'Japanese (Japan,JP)' => 'ja_JP_JP',
		'Japanese' => 'ja',
		'Korean (South Korea)' => 'ko_KR',
		'Korean' => 'ko',
		'Latvian (Latvia)' => 'lv_LV',
		'Latvian' => 'lv',
		'Lithuanian (Lithuania)' => 'lt_LT',
		'Lithuanian' => 'lt',
		'Macedonian (Macedonia)' => 'mk_MK',
		'Macedonian' => 'mk',
		'Malay (Malaysia)' => 'ms_MY',
		'Malay' => 'ms',
		'Maltese (Malta)' => 'mt_MT',
		'Maltese' => 'mt',
		'Norwegian (Norway)' => 'no_NO',
		'Norwegian (Norway,Nynorsk)' => 'no_NO_NY',
		'Norwegian' => 'no',
		'Polish (Poland)' => 'pl_PL',
		'Polish' => 'pl',
		'Portuguese (Brazil)' => 'pt_BR',
		'Portuguese (Portugal)' => 'pt_PT',
		'Portuguese' => 'pt',
		'Romanian (Romania)' => 'ro_RO',
		'Romanian' => 'ro',
		'Russian (Russia)' => 'ru_RU',
		'Russian' => 'ru',
		'Serbian (Bosnia and Herzegovina)' => 'sr_BA',
		'Serbian (Montenegro)' => 'sr_ME',
		'Serbian (Serbia and Montenegro)' => 'sr_CS',
		'Serbian (Serbia)' => 'sr_RS',
		'Serbian' => 'sr',
		'Slovak (Slovakia)' => 'sk_SK',
		'Slovak' => 'sk',
		'Slovenian (Slovenia)' => 'sl_SI',
		'Slovenian' => 'sl',
		'Spanish (Argentina)' => 'es_AR',
		'Spanish (Bolivia)' => 'es_BO',
		'Spanish (Chile)' => 'es_CL',
		'Spanish (Colombia)' => 'es_CO',
		'Spanish (Costa Rica)' => 'es_CR',
		'Spanish (Dominican Republic)' => 'es_DO',
		'Spanish (Ecuador)' => 'es_EC',
		'Spanish (El Salvador)' => 'es_SV',
		'Spanish (Guatemala)' => 'es_GT',
		'Spanish (Honduras)' => 'es_HN',
		'Spanish (Mexico)' => 'es_MX',
		'Spanish (Nicaragua)' => 'es_NI',
		'Spanish (Panama)' => 'es_PA',
		'Spanish (Paraguay)' => 'es_PY',
		'Spanish (Peru)' => 'es_PE',
		'Spanish (Puerto Rico)' => 'es_PR',
		'Spanish (Spain)' => 'es_ES',
		'Spanish (United States)' => 'es_US',
		'Spanish (Uruguay)' => 'es_UY',
		'Spanish (Venezuela)' => 'es_VE',
		'Spanish' => 'es',
		'Swedish (Sweden)' => 'sv_SE',
		'Swedish' => 'sv',
		'Thai (Thailand)' => 'th_TH',
		'Thai (Thailand,TH)' => 'th_TH_TH',
		'Thai' => 'th',
		'Turkish (Turkey)' => 'tr_TR',
		'Turkish' => 'tr',
		'Ukrainian (Ukraine)' => 'uk_UA',
		'Ukrainian' => 'uk',
		'Vietnamese (Vietnam)' => 'vi_VN',
		'Vietnamese' => 'vi' );

}