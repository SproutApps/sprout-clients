<?php

/**
 * Clients Controller
 *
 *
 * @package Sprout_Clients
 * @subpackage Clients
 */
class SC_Clients_Admin_Table extends SC_Clients {

	public static function init() {

		if ( is_admin() ) {

			// Admin columns
			if ( class_exists( 'Sprout_Invoices' ) ) {
				add_filter( 'si_client_columns', array( __CLASS__, 'register_columns' ) );
			} else {
				add_filter( 'manage_edit-'.Sprout_Client::POST_TYPE.'_columns', array( __CLASS__, 'register_columns' ) );
			}

			add_action( 'manage_'.Sprout_Client::POST_TYPE.'_posts_custom_column', array( __CLASS__, 'column_display' ), 20, 2 );
			add_action( 'post_row_actions', array( __CLASS__, 'modify_row_actions' ), 10, 2 );

			// User Admin columns
			add_filter( 'manage_users_columns', array( __CLASS__, 'user_register_columns' ) );
			add_filter( 'manage_users_custom_column', array( __CLASS__, 'user_column_display' ), 10, 3 );

			// Advanced manager support
			if ( function_exists( 'tribe_setup_apm' ) ) {
				self::set_column_defaults();

				self::setup_tribe_posts_manager();
				add_filter( 'tribe_apm_headers_'.Sprout_Client::POST_TYPE, array( __CLASS__, 'modify_apm_columns' ), 10, 3 );
			} else {
				add_action( 'restrict_manage_posts', array( __CLASS__, 'restrict_contacts_by_status' ) );
			}
		}

	}

	public static function restrict_contacts_by_status() {
		global $typenow;
		if ( Sprout_Client::POST_TYPE === $typenow ) {

			$selected = 0;
			$taxonomy = Sprout_Client::TYPE_TAXONOMY;
			if ( isset( $_GET[ $taxonomy ] ) && $_GET[ $taxonomy ] ) {
				$term = get_term_by( 'slug', $_GET[ $taxonomy ], $taxonomy );
				$selected = $term->term_id;
			}
			$status_taxonomy = get_taxonomy( $taxonomy );
			wp_dropdown_categories( array(
				'show_option_all' => sprintf( __( 'All %s' , 'sprout-invoices' ), $status_taxonomy->label ),
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'orderby'         => 'name',
				'selected'        => $selected,
				'show_count'      => true,
				'value_field'     => 'slug',
			) );

			$type_selected = 0;
			$taxonomy = Sprout_Client::STATUS_TAXONOMY;
			if ( isset( $_GET[ $taxonomy ] ) && $_GET[ $taxonomy ] ) {
				$term = get_term_by( 'slug', $_GET[ $taxonomy ], $taxonomy );
				$type_selected = $term->term_id;
			}
			$status_taxonomy = get_taxonomy( $taxonomy );
			wp_dropdown_categories( array(
				'show_option_all' => sprintf( __( 'All %s' , 'sprout-invoices' ), $status_taxonomy->label ),
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'orderby'         => 'name',
				'selected'        => $type_selected,
				'show_count'      => true,
				'value_field'     => 'slug',
			) );

			if ( $selected || $type_selected ) {
				$url = add_query_arg( array(
					'post_type' => Sprout_Client::POST_TYPE,
				), admin_url( 'edit.php' ) );
				printf( '<a href="%s" id="filter_reset_link" class="sc_del_button">X</a>', $url );
			}
		}
	}


	public static function setup_tribe_posts_manager() {
		global $sc_filters;
		$filter_array = array(
			'sc_phone' => array(
				'name' => __( 'Phone' , 'sprout-invoices' ),
				'meta' => '_phone',
			),
			'sc_facebook' => array(
				'name' => __( 'Facebook' , 'sprout-invoices' ),
				'meta' => '_facebook',
			),
			'sc_linkedin' => array(
				'name' => __( 'Linkedin' , 'sprout-invoices' ),
				'meta' => '_linkedin',
			),
			'sc_skype' => array(
				'name' => __( 'Skype' , 'sprout-invoices' ),
				'meta' => '_skype',
			),
			'sc_twitter' => array(
				'name' => __( 'Twitter' , 'sprout-invoices' ),
				'meta' => '_twitter',
			),
			'sc_website' => array(
				'name' => __( 'Website' , 'sprout-invoices' ),
				'meta' => '_website',
			),
		);
		$sc_filters = tribe_setup_apm( Sprout_Client::POST_TYPE, $filter_array );
		$sc_filters->do_metaboxes = false;
	}

	/**
	 * Overload the columns for the invoice post type admin
	 *
	 * @param array   $columns
	 * @return array
	 */
	public static function register_columns( $original_columns ) {
		$columns['cb'] = $original_columns['cb'];
		$columns['title'] = __( 'Contact' , 'sprout-invoices' );
		$columns['sc_client_type'] = __( 'Type' , 'sprout-invoices' );
		$columns['sc_client_status'] = __( 'Statuses' , 'sprout-invoices' );
		$columns['lead_info'] = __( 'Info' , 'sprout-invoices' );
		$columns['contacts'] = __( 'Associated Contacts' , 'sprout-invoices' );
		if ( class_exists( 'Sprout_Invoices' ) ) {
			$columns['invoices'] = __( 'Invoices' , 'sprout-invoices' );
			$columns['estimates'] = __( 'Estimates' , 'sprout-invoices' );
		}
		return $columns;
	}

	public static function modify_apm_columns( $columns = array() ) {
		unset( $columns['taxonomy-sc_client_type'] );
		unset( $columns['taxonomy-sc_client_status'] );
		return $columns;
	}

	public static function set_column_defaults() {
		$default_columns = array( 'title', 'sc_client_type', 'sc_client_status', 'contacts' );
		update_user_meta( get_current_user_id(), 'tribe_columns_'.Sprout_Client::POST_TYPE, $default_columns );
	}

	/**
	 * Display the content for the column
	 *
	 * @param string  $column_name
	 * @param int     $id          post_id
	 * @return string
	 */
	public static function column_display( $column_name, $id ) {
		$client = Sprout_Client::get_instance( $id );
		if ( ! is_a( $client, 'Sprout_Client' ) ) {
			return; // return for that temp post
		}

		switch ( $column_name ) {

			case 'sc_client_type':
				sc_type_select( $id );
				break;

			case 'sc_client_status':
				sc_status_select( $id );
				break;

			case 'lead_info':
				echo '<p>';
				$address = si_format_address( $client->get_address(), 'string', '<br/>' );
				print $address;
				if ( '' !== $address ) {
					echo '<br/>';
				}
				echo make_clickable( esc_url( $client->get_website() ) );
				echo '</p>';
				break;

			case 'contacts':
				$associated_users = $client->get_associated_users();
				echo '<p>';
				printf( '<b>%s</b>: ', sc__( 'User Contacts' ) );
				if ( ! empty( $associated_users ) ) {
					$users_print = array();
					foreach ( $associated_users as $user_id ) {
						$user = get_userdata( $user_id );
						if ( ! is_a( $user, 'WP_User' ) ) {
							$client->remove_associated_user( $user_id );
							continue;
						}
						$users_print[] = sprintf( '<span class="associated_user"><a href="%s">%s</a></span>', get_edit_user_link( $user_id ), sc_get_users_full_name( $user_id ) );
					}
				}
				if ( ! empty( $users_print ) ) {
					print implode( ', ', $users_print );
				} else {
					sc_e( 'No associated people/users.' );
				}
				echo '</p>';
				break;

			default:
				// code...
			break;
		}

	}

	/**
	 * Filter the array of row action links below the title.
	 *
	 * @param array   $actions An array of row action links.
	 * @param WP_Post $post    The post object.
	 */
	public static function modify_row_actions( $actions = array(), $post = array() ) {
		if ( Sprout_Client::POST_TYPE === $post->post_type ) {
			unset( $actions['trash'] );
			// remove quick edit
			unset( $actions['inline hide-if-no-js'] );
		}
		return $actions;
	}

	/**
	 * Register the client column. In CSS make it small.
	 * @param  array $columns
	 * @return array
	 */
	public static function user_register_columns( $columns ) {
		unset( $columns['contact'] );
		$columns['contact'] = '<div class="dashicons dashicons-businessman"></div>';
		return $columns;
	}

	/**
	 * User column display
	 * @param  string $empty
	 * @param  string $column_name
	 * @param  int $id
	 * @return string
	 */
	public static function user_column_display( $empty = '', $column_name, $id ) {
		switch ( $column_name ) {
			case 'contact':
				$client_ids = Sprout_Client::get_clients_by_user( $id );

				if ( ! empty( $client_ids ) ) {
					$string = '';
					foreach ( $client_ids as $client_id ) {
						$string .= sprintf( __( '<a class="doc_link" title="%s" href="%s">%s</a>' , 'sprout-invoices' ), get_the_title( $client_id ), get_edit_post_link( $client_id ), '<div class="dashicons dashicons-businessman"></div>' );
					}
					return $string;
				}
				break;

			default:
				break;
		}
	}

	public static function filter_admin_search( $meta_search = '', $post_type = '' ) {
		if ( Sprout_Client::POST_TYPE !== $post_type ) {
			return array();
		}
		$meta_search = array(
			'_phone',
			'_website',
			'_phone',
			'_facebook',
			'_twitter',
			'_linkedin',
			'_skype',
		);
		return $meta_search;
	}
}
