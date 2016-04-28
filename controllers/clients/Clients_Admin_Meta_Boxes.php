<?php

/**
 * Clients Controller
 *
 *
 * @package Sprout_Clients
 * @subpackage Clients
 */
class SC_Clients_Admin_Meta_Boxes extends SC_Clients {

	public static function init() {

		if ( is_admin() ) {

			// Meta boxes
			add_action( 'admin_init', array( __CLASS__, 'register_meta_boxes' ), 5 );
			add_action( 'admin_init', array( __CLASS__, 'register_dynamic_meta_boxes' ) );
			// add_filter( 'wp_insert_post_data', array( __CLASS__, 'update_post_data' ), 100, 2 );
			add_action( 'do_meta_boxes', array( __CLASS__, 'modify_meta_boxes' ) );
			add_action( 'edit_form_top', array( __CLASS__, 'name_box' ) );

			remove_action( 'edit_form_top', array( 'SI_Clients', 'name_box' ) );

		}

	}

	/////////////////
	// Meta boxes //
	/////////////////

	/**
	 * Regsiter meta boxes for estimate editing.
	 *
	 * @return
	 */
	public static function register_meta_boxes() {
		// estimate specific
		$args = array(
			'si_client_users' => array(
				'title' => sc__( 'Associated Contacts' ),
				'show_callback' => array( __CLASS__, 'show_people_meta_box' ),
				'save_callback' => array( __CLASS__, 'save_meta_box_people' ),
				'context' => 'normal',
				'priority' => 'high',
				'save_priority' => 0,
				'weight' => 10,
			),
			'si_client_information' => array(
				'title' => sc__( 'Business Detail' ),
				'show_callback' => array( __CLASS__, 'show_information_meta_box' ),
				'save_callback' => array( __CLASS__, 'save_meta_box_client_information' ),
				'context' => 'normal',
				'priority' => 'high',
				'save_priority' => 0,
				'weight' => 15,
			),
			'si_client_submit' => array(
				'title' => 'Update',
				'show_callback' => array( __CLASS__, 'show_submit_meta_box' ),
				'save_callback' => array( __CLASS__, 'save_submit_meta_box' ),
				'context' => 'side',
				'priority' => 'high',
			),
			'si_client_communication' => array(
				'title' => sc__( 'Lead Communications' ),
				'show_callback' => array( __CLASS__, 'show_communication_meta_box' ),
				'save_callback' => array( __CLASS__, 'save_meta_box_client_communication' ),
				'context' => 'side',
				'priority' => 'high',
				'save_priority' => 0,
				'weight' => 15,
			),
			'si_client_notes' => array(
				'title' => sc__( 'Notes' ),
				'show_callback' => array( __CLASS__, 'show_client_notes_view' ),
				'save_callback' => array( __CLASS__, '_save_null' ),
				'context' => 'normal',
				'priority' => 'low',
				'weight' => 50,
			),
		);
		do_action( 'sprout_meta_box', $args, Sprout_Client::POST_TYPE );
	}

	/**
	 * Regsiter meta boxes for estimate editing.
	 *
	 * @return
	 */
	public static function register_dynamic_meta_boxes() {
		if ( ! is_admin() || ! isset( $_GET['post'] ) || get_post_type( $_GET['post'] ) !== Sprout_Client::POST_TYPE ) {
			return;
		}
		// TODO disabled since the widget needs an id.
		//return;

		// estimate specific
		$args = array(
			'si_client_twitter_feed' => array(
				'title' => sc__( 'Twitter Feed' ),
				'show_callback' => array( __CLASS__, 'show_twitter_feed' ),
				'save_callback' => null,
				'context' => 'side',
				'priority' => 'high',
				'save_priority' => 0,
				'weight' => 500,
			),
		);
		do_action( 'sprout_meta_box', $args, Sprout_Client::POST_TYPE );
	}

	/**
	 * Remove publish box and add something custom for estimates
	 *
	 * @param string  $post_type
	 * @return
	 */
	public static function modify_meta_boxes( $post_type ) {
		if ( Sprout_Client::POST_TYPE === $post_type ) {
			remove_meta_box( 'submitdiv', null, 'side' );
		}
	}

	/**
	 * Add quick links
	 * @param  object $post
	 * @return
	 */
	public static function name_box( $post ) {
		if ( get_post_type( $post ) === Sprout_Client::POST_TYPE ) {
			$client = Sprout_Client::get_instance( $post->ID );
			self::load_view( 'admin/meta-boxes/clients/name', array(
					'client' => $client,
					'id' => $post->ID,
					'type' => $client->get_type(),
					'statuses' => $client->get_statuses(),
					'all_statuses' => sc_get_client_statuses(),
					'post_status' => $post->post_status,
			) );
		}
	}

	/**
	 * Show custom submit box.
	 * @param  WP_Post $post
	 * @param  array $metabox
	 * @return
	 */
	public static function show_submit_meta_box( $post, $metabox ) {
		$client = Sprout_Client::get_instance( $post->ID );

		$args = apply_filters( 'si_get_users_for_association_args', array( 'fields' => array( 'ID', 'user_email', 'display_name' ) ) );
		$users = get_users( $args );
		self::load_view( 'admin/meta-boxes/clients/submit', array(
				'id' => $post->ID,
				'client' => $client,
				'post' => $post,
				'associated_users' => $client->get_associated_users(),
				'users' => $users,
				'invoices' => $client->get_invoices(),
				'estimates' => $client->get_estimates(),
		), false );
	}


	/**
	 * People
	 * @param  object $post
	 * @return
	 */
	public static function show_people_meta_box( $post ) {
		if ( get_post_type( $post ) === Sprout_Client::POST_TYPE ) {
			$client = Sprout_Client::get_instance( $post->ID );
			$args = apply_filters( 'si_get_users_for_association_args', array( 'fields' => array( 'ID', 'user_email', 'display_name' ) ) );
			$users = get_users( $args );
			self::load_view( 'admin/meta-boxes/clients/associated-users', array(
					'client' => $client,
					'id' => $post->ID,
					'associated_users' => $client->get_associated_users(),
					'users' => $users,
			) );

			add_thickbox();

			// add the user creation modal
			$fields = self::user_form_fields( $post->ID );
			self::load_view( 'admin/meta-boxes/clients/create-user-modal', array( 'fields' => $fields ) );
		}
	}

	/**
	 * Information
	 * @param  object $post
	 * @return
	 */
	public static function show_information_meta_box( $post ) {
		if ( get_post_type( $post ) === Sprout_Client::POST_TYPE ) {
			$client = Sprout_Client::get_instance( $post->ID );
			self::load_view( 'admin/meta-boxes/clients/info', array(
					'client' => $client,
					'id' => $post->ID,
					'associated_users' => $client->get_associated_users(),
					'fields' => self::form_fields( false, $client ),
					'address' => $client->get_address(),
			) );
		}
	}

	/**
	 * Saving info meta
	 * @param  int $post_id
	 * @param  object $post
	 * @param  array $callback_args
	 * @return
	 */
	public static function save_meta_box_client_information( $post_id, $post, $callback_args ) {
		// name is updated in the title div
		$website = ( isset( $_POST['sa_metabox_website'] ) && '' !== $_POST['sa_metabox_website'] ) ? $_POST['sa_metabox_website'] : '' ;

		$address = array(
			'street' => isset( $_POST['sa_metabox_street'] ) ? $_POST['sa_metabox_street'] : '',
			'city' => isset( $_POST['sa_metabox_city'] ) ? $_POST['sa_metabox_city'] : '',
			'zone' => isset( $_POST['sa_metabox_zone'] ) ? $_POST['sa_metabox_zone'] : '',
			'postal_code' => isset( $_POST['sa_metabox_postal_code'] ) ? $_POST['sa_metabox_postal_code'] : '',
			'country' => isset( $_POST['sa_metabox_country'] ) ? $_POST['sa_metabox_country'] : '',
		);

		$client = Sprout_Client::get_instance( $post_id );
		$client->set_website( $website );
		$client->set_address( $address );

		$user_id = 0;
		// Attempt to create a user
		if ( isset( $_POST['sa_metabox_email'] ) && '' !== $_POST['sa_metabox_email'] ) {
			$user_args = array(
				'user_login' => self::esc__( $_POST['sa_metabox_email'] ),
				'display_name' => isset( $_POST['sa_metabox_name'] ) ? self::esc__( $_POST['sa_metabox_name'] ) : self::esc__( $_POST['sa_metabox_email'] ),
				'user_pass' => wp_generate_password(), // random password
				'user_email' => isset( $_POST['sa_metabox_email'] ) ? self::esc__( $_POST['sa_metabox_email'] ) : '',
				'first_name' => isset( $_POST['sa_metabox_first_name'] ) ? self::esc__( $_POST['sa_metabox_first_name'] ) : '',
				'last_name' => isset( $_POST['sa_metabox_last_name'] ) ? self::esc__( $_POST['sa_metabox_last_name'] ) : '',
				'user_url' => isset( $_POST['sa_metabox_website'] ) ? self::esc__( $_POST['sa_metabox_website'] ) : '',
			);
			$user_id = self::create_user( $user_args );
		}

		if ( $user_id ) {
			$client->add_associated_user( $user_id );
		}
	}

	public static function update_post_data( $data = array(), $post = array() ) {
		if ( Sprout_Client::POST_TYPE === $post['post_type'] ) {
			$title = $post['post_title'];
			if ( isset( $_POST['sa_metabox_name'] ) && '' !== $_POST['sa_metabox_name'] ) {
				$title = $_POST['sa_metabox_name'];
			}
			// modify the post title
			$data['post_title'] = $title;
		}
		return $data;
	}

	/**
	 * Saving submit meta
	 * @param  int $post_id
	 * @param  object $post
	 * @param  array $callback_args
	 * @return
	 */
	public static function save_submit_meta_box( $post_id, $post, $callback_args ) {
		// nothing yet.
	}


	/**
	 * Show the history
	 *
	 * @param WP_Post $post
	 * @param array   $metabox
	 * @return
	 */
	public static function show_client_history_view( $post, $metabox ) {
		if ( 'auto-draft' === $post->post_status ) {
			printf( '<p>%s</p>', sc__( 'No history available.' ) );
			return;
		}
		$client = Sprout_Client::get_instance( $post->ID );
		self::load_view( 'admin/meta-boxes/clients/history', array(
				'id' => $post->ID,
				'post' => $post,
				'client' => $client,
				'history' => $client->get_history(),
		), false );
	}


	/**
	 * Show the notes
	 *
	 * @param WP_Post $post
	 * @param array   $metabox
	 * @return
	 */
	public static function show_client_notes_view( $post, $metabox ) {
		if ( 'auto-draft' === $post->post_status ) {
			printf( '<p>%s</p>', sc__( 'Save before creating any notes.' ) );
			return;
		}
		$client = Sprout_Client::get_instance( $post->ID );
		self::load_view( 'admin/meta-boxes/clients/notes', array(
				'id' => $post->ID,
				'post' => $post,
				'client' => $client,
		), false );
	}



	/**
	 * Information
	 * @param  object $post
	 * @return
	 */
	public static function show_communication_meta_box( $post ) {
		if ( get_post_type( $post ) === Sprout_Client::POST_TYPE ) {
			$client = Sprout_Client::get_instance( $post->ID );
			self::load_view( 'admin/meta-boxes/clients/communication', array(
					'client' => $client,
					'id' => $post->ID,
					'fields' => self::comms_fields( false, $client ),
					'website' => $client->get_website(),
			) );
		}
	}

	/**
	 * Saving communication meta
	 * @param  int $post_id
	 * @param  object $post
	 * @param  array $callback_args
	 * @return
	 */
	public static function save_meta_box_client_communication( $post_id, $post, $callback_args ) {
		// name is filtered via update_post_data
		$phone = ( isset( $_POST['sa_metabox_phone'] ) && '' !== $_POST['sa_metabox_phone'] ) ? $_POST['sa_metabox_phone'] : '' ;
		$twitter = ( isset( $_POST['sa_metabox_twitter'] ) && '' !== $_POST['sa_metabox_twitter'] ) ? $_POST['sa_metabox_twitter'] : '' ;
		$skype = ( isset( $_POST['sa_metabox_skype'] ) && '' !== $_POST['sa_metabox_skype'] ) ? $_POST['sa_metabox_skype'] : '' ;
		$facebook = ( isset( $_POST['sa_metabox_facebook'] ) && '' !== $_POST['sa_metabox_facebook'] ) ? $_POST['sa_metabox_facebook'] : '' ;
		$linkedin = ( isset( $_POST['sa_metabox_linkedin'] ) && '' !== $_POST['sa_metabox_linkedin'] ) ? $_POST['sa_metabox_linkedin'] : '' ;

		$client = Sprout_Client::get_instance( $post_id );
		$client->set_phone( $phone );
		$client->set_twitter( $twitter );
		$client->set_skype( $skype );
		$client->set_facebook( $facebook );
		$client->set_linkedin( $linkedin );

	}

	public static function show_twitter_feed() {
		$client = Sprout_Client::get_instance( get_the_id() );
		if ( ! is_a( $client, 'Sprout_Client' ) ) {
			return;
		}
		if ( '' === $client->get_twitter() ) {
			_e( 'No twitter username assigned.' , 'sprout-invoices' );
			return;
		}
		printf( '<a class="twitter-timeline" href="https://twitter.com/%1$s" data-widget-id="%2$s" data-screen-name="%1$s">Tweets by %1$s</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?"http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>', $client->get_twitter(), apply_filters( 'sc_twitter_widget_id', '492426361349234688' ) );
	}
}
