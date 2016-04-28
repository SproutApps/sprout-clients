<?php

/**
 * Clients Controller
 *
 *
 * @package Sprout_Clients
 * @subpackage Clients
 */
class SC_Users extends SC_Clients {

	const DOB = 'sc_dob';
	const PHONE = 'sc_phone';
	const TWITTER = 'sc_twitter';
	const LINKEDIN = 'sc_linkedin';
	const NOTE = 'sc_note';

	public static function init() {
		add_action( 'show_user_profile', array( __CLASS__, 'user_profile_fields' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'user_profile_fields' ) );

		add_action( 'personal_options_update', array( __CLASS__, 'save_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'save_profile_fields' ) );
	}

	public static function user_profile_fields( $user ) {
		$user_id = $user->ID;
		self::load_view( 'admin/user/profile_fields.php', array(
			'user' => $user,
			'phone' => self::get_users_phone( $user_id ),
			'twitter' => self::get_users_twitter( $user_id ),
			'linkedin' => self::get_users_linkedin( $user_id ),
			'dob' => self::get_users_dob( $user_id ),
			'note' => self::get_users_note( $user_id ),
			'clients' => Sprout_Client::get_clients_by_user( $user_id ),
			) );
	}

	public static function save_profile_fields( $user_id = 0 ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false; }

		update_user_meta( $user_id, self::DOB, $_POST['sc_dob'] );
		update_user_meta( $user_id, self::PHONE, $_POST['sc_phone'] );
		update_user_meta( $user_id, self::TWITTER, $_POST['sc_twitter'] );
		update_user_meta( $user_id, self::LINKEDIN, $_POST['sc_linkedin'] );
		update_user_meta( $user_id, self::NOTE, $_POST['sc_note'] );
	}

	public static function get_users_phone( $user_id = 0 ) {
		if ( ! $user_id ) {
			return __( 'N/A' , 'sprout-invoices' );
		}
		return get_the_author_meta( self::PHONE, $user_id );
	}

	public static function get_users_dob( $user_id = 0 ) {
		if ( ! $user_id ) {
			return __( 'N/A' , 'sprout-invoices' );
		}
		return get_the_author_meta( self::DOB, $user_id );
	}

	public static function get_users_twitter( $user_id = 0 ) {
		if ( ! $user_id ) {
			return __( 'N/A' , 'sprout-invoices' );
		}
		return get_the_author_meta( self::TWITTER, $user_id );
	}

	public static function get_users_linkedin( $user_id = 0 ) {
		if ( ! $user_id ) {
			return __( 'N/A' , 'sprout-invoices' );
		}
		return get_the_author_meta( self::LINKEDIN, $user_id );
	}

	public static function get_users_note( $user_id = 0 ) {
		if ( ! $user_id ) {
			return;
		}
		return get_the_author_meta( self::NOTE, $user_id );
	}
}