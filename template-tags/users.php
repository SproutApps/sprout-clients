<?php

if ( ! function_exists( 'sc_get_users_note' ) ) :
	/**
	 * Get user's note
	 * @param  integer $user_id
	 * @return string
	 */
	function sc_get_users_note( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_the_ID();
		}
		return apply_filters( 'sc_get_users_note', SC_Users::get_users_note( $user_id ), $user_id );
	}
endif;

if ( ! function_exists( 'sc_get_users_phone' ) ) :
	/**
	 * Get user's phone
	 * @param  integer $user_id
	 * @return string
	 */
	function sc_get_users_phone( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_the_ID();
		}
		return apply_filters( 'sc_get_users_phone', SC_Users::get_users_phone( $user_id ), $user_id );
	}
endif;

if ( ! function_exists( 'sc_get_users_dob' ) ) :
	/**
	 * Get user's dob
	 * @param  integer $user_id
	 * @return string
	 */
	function sc_get_users_dob( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_the_ID();
		}
		return apply_filters( 'sc_get_users_dob', SC_Users::get_users_dob( $user_id ), $user_id );
	}
endif;

if ( ! function_exists( 'sc_get_users_twitter' ) ) :
	/**
	 * Get user's twitter
	 * @param  integer $user_id
	 * @return string
	 */
	function sc_get_users_twitter( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_the_ID();
		}
		return apply_filters( 'sc_get_users_twitter', SC_Users::get_users_twitter( $user_id ), $user_id );
	}
endif;

if ( ! function_exists( 'sc_get_users_linkedin' ) ) :
	/**
	 * Get user's linkedin
	 * @param  integer $user_id
	 * @return string
	 */
	function sc_get_users_linkedin( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_the_ID();
		}
		return apply_filters( 'sc_get_users_linkedin', SC_Users::get_users_linkedin( $user_id ), $user_id );
	}
endif;