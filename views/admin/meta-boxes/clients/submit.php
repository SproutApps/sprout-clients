<?php

/**
 * Copy of the post_submit_meta_box function
 *
 */

$post_type = $post->post_type;
$post_type_object = get_post_type_object( $post_type );
$can_publish = current_user_can( $post_type_object->cap->publish_posts );
$date_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );
?>
<div class="submitbox" id="submitpost">

	<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
	<div style="display:none;">
		<?php submit_button( __( 'Save' ), 'button', 'save' ); ?>
	</div>

	<div id="minor-publishing">
		<p><?php printf( __( '<b>Created:</b> %s' , 'sprout-invoices' ), date( $date_format, strtotime( $post->post_date ) ) ) ?></p>
		<p><?php printf( __( '<b>Updated:</b> %s' , 'sprout-invoices' ), date( $date_format, strtotime( $post->post_modified ) ) ) ?></p>
		<?php do_action( 'client_submit_post_users_list' ) ?>
		<div class="clear"></div>
	</div><!-- #minor-publishing -->

	<div id="major-publishing-actions" class="clearfix">
		<?php do_action( 'post_submitbox_start' ); ?>
		<div id="delete-action">
			<?php
			if ( current_user_can( 'delete_post', $post->ID ) ) { ?>
				<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID, null, true ); ?>"><?php _e( 'Delete' , 'sprout-invoices' ) ?></a><?php
			} ?>
		</div>

		<div id="publishing-action">
			<?php
			if ( ! in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
				if ( $can_publish ) : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish' ) ?>" />
					<?php submit_button( __( 'Publish' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
				<?php else : ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Submit for Review' ) ?>" />
					<?php submit_button( __( 'Submit for Review' ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
				<?php endif;
			} else { ?>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update' ) ?>" />
					<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Update' ) ?>" />
			<?php
			} ?>
			<span class="spinner"></span>
		</div>
	</div><!-- #major-publishing-actions -->
</div>