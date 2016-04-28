<?php

if ( ! function_exists( 'wp_editor_styleless' ) ) :
	/**
 * Removes those pesky theme styles from the theme.
 * @see  wp_editor()
 * @return wp_editor()
 */
	function wp_editor_styleless( $content, $editor_id, $settings = array() ) {
		add_filter( 'mce_css', '__return_null' );
		$return = wp_editor( $content, $editor_id, $settings );
		remove_filter( 'mce_css', '__return_null' );
		return $return;
	}
endif;
