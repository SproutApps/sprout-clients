<?php

/**
 * SI Utility Template Functions
 *
 * @package Sprout_Clients
 * @subpackage Utility
 * @category Template Tags
 */

/**
 * A wrapper around WP's __() to add the plugin's text domain
 * @see Sprout_Clients::__()
 * @param string  $string
 * @return string|void
 */
function sc__( $string ) {
	return Sprout_Clients::__( $string );
}

/**
 * A wrapper around WP's _e() to add the plugin's text domain
 * @see Sprout_Clients::_e()
 * @param string  $string
 * @return void
 */
function sc_e( $string ) {
	return Sprout_Clients::_e( $string );
}

/**
 * A wrapper around WP's __() to add the plugin's text domain
 * @see Sprout_Clients::__()
 * @param string  $string
 * @return string|void
 */
function sc_esc__( $string ) {
	return Sprout_Clients::esc__( $string );
}

/**
 * A wrapper around WP's _e() to add the plugin's text domain
 * @see Sprout_Clients::_e()
 * @param string  $string
 * @return void
 */
function sc_esc_e( $string ) {
	return Sprout_Clients::esc_e( $string );
}

/**
 * A wrapper around WP's __() to add the plugin's text domain
 * @see Sprout_Clients::__()
 * @param string  $string
 * @return string|void
 */
function sc_esc_html__( $string ) {
	return Sprout_Clients::esc_html__( $string );
}

/**
 * A wrapper around WP's _e() to add the plugin's text domain
 * @see Sprout_Clients::_e()
 * @param string  $string
 * @return void
 */
function sc_esc_html_e( $string ) {
	return Sprout_Clients::esc_html_e( $string );
}


if ( ! function_exists( 'si_format_address' ) ) :
	/**
	 * Return a formatted address
	 * @param  array $address   an address array
	 * @param  string $return    return an array or a string with separation
	 * @param  string $separator if not returning an array what should the fields be separated by
	 * @return array|string            return an array by default of a string based on $return
	 */
	function si_format_address( $address, $return = 'array', $separator = "\n" ) {
		if ( empty( $address ) ) {
			return '';
		}
		$lines = array();
		if ( ! empty($address['first_name']) || ! empty($address['last_name']) ) {
			$lines[] = $address['first_name'].' '.$address['last_name'];
		}
		if ( ! empty( $address['street'] ) ) {
			$lines[] = $address['street'];
		}
		$city_line = '';
		if ( ! empty( $address['city'] ) ) {
			$city_line .= $address['city'];
		}
		if ( $city_line != '' && ( ! empty( $address['zone'] ) || ! empty( $address['postal_code'] ) ) ) {
			$city_line .= ', ';
			if ( ! empty( $address['zone'] ) ) {
				$city_line .= $address['zone'];
			}
			if ( ! empty( $address['postal_code'] ) ) {
				$city_line = rtrim( $city_line ).' '.$address['postal_code'];
			}
		}
		$lines[] = rtrim( $city_line );
		if ( ! empty( $address['country'] ) ) {
			$lines[] = $address['country'];
		}
		switch ( $return ) {
			case 'array':
			return $lines;
			default:
			return apply_filters( 'si_format_address', implode( $separator, $lines ), $address, $return, $separator );
		}
	}
endif;

function sc_get_users_full_name( $user_id = 0 ) {
	$name = sc__( 'Unknown' );
	if ( ! $user_id ) {
		return $name;
	}

	$user = get_userdata( $user_id );
	if ( $user ) {
		$name = ( '' === $user->first_name && '' === $user->last_name ) ? sc__( 'Mystery Man' ) : $user->first_name . '&nbsp;' . $user->last_name ;
	}
	return $name;
}


/////////////////////
// Developer Tools //
/////////////////////

if ( ! function_exists( 'prp' ) ) {
	/**
	 * print_r with a <pre> wrap
	 * @param array $array
	 * @return
	 */
	function prp( $array ) {
		echo '<pre style="white-space:pre-wrap;">';
		print_r( $array );
		echo '</pre>';
	}
}
if ( ! function_exists( 'wpbt' ) ) {
	function wpbt() {
		error_log( 'backtrace: ' . print_r( wp_debug_backtrace_summary( null, 0, false ), true ) );
	}
}

if ( ! function_exists( 'pp' ) ) {
	/**
	 * more elegant way to print_r an array
	 * @return string
	 */
	function pp() {
		$msg = __v_build_message( func_get_args() );
		echo '<pre style="white-space:pre-wrap; text-align: left; '.
			'font: normal normal 11px/1.4 menlo, monaco, monospaced; '.
			'background: white; color: black; padding: 5px;">'.$msg.'</pre>';
	}
	/**
	 * more elegant way to display a var dump
	 * @return string
	 */
	function dp() {
		$msg = __v_build_message( func_get_args(), 'var_dump' );
		echo '<pre style="white-space:pre-wrap;; text-align: left; '.
			'font: normal normal 11px/1.4 menlo, monaco, monospaced; '.
			'background: white; color: black; padding: 5px;">'.$msg.'</pre>';
	}

	/**
	 * simple error logging function
	 * @return [type] [description]
	 */
	function ep() {
		$msg = __v_build_message( func_get_args() );
		error_log( '**: '.$msg );
	}

	/**
	 * utility for ep, pp, dp
	 * @param array $vars
	 * @param string $func function
	 * @param string $sep  seperator
	 * @return void|string
	 */
	function __v_build_message( $vars, $func = 'print_r', $sep = ', ' ) {
		$msgs = array();

		if ( ! empty( $vars ) ) {
			foreach ( $vars as $var ) {
				if ( is_bool( $var ) ) {
					$msgs[] = ( $var ? 'true' : 'false' );
				}
				elseif ( is_scalar( $var ) ) {
					$msgs[] = $var;
				}
				else {
					switch ( $func ) {
						case 'print_r':
						case 'var_export':
							$msgs[] = $func( $var, true );
						break;
						case 'var_dump':
							ob_start();
							var_dump( $var );
							$msgs[] = ob_get_clean();
						break;
					}
				}
			}
		}

		return implode( $sep, $msgs );
	}
}