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
		<p>
			<b><?php _e( 'Assign Team Member(s)', 'sprout-invoices' ) ?></b> <span class="helptip" title="<?php _e( 'Engagements can have multiple users assigned.', 'sprout-invoices' ) ?>"></span>
			<select id="assigned_users" style="width:100%" class="sa_select2">
				<option></option>
				<?php foreach ( $users as $user ) : ?>
					<?php if ( ! in_array( $user->ID, $assigned_users ) ) : ?>
						<option value="<?php echo (int) $user->ID ?>" <?php selected( in_array( $user->ID, $assigned_users ), true ) ?> data-url="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . $user->ID ) ) ?>" data-user-email="<?php echo esc_attr( $user->user_email ); ?>"><?php echo esc_html( $user->display_name ) ?></option>
					<?php endif ?>
				<?php endforeach ?>
			</select>
			<?php if ( ! empty( $assigned_users ) ) : ?>
				<ul id="assigned_users_list">
					<?php foreach ( $assigned_users as $a_user_id ) : ?>
						<?php
							$u = get_userdata( $a_user_id );
						if ( ! is_a( $u, 'WP_User' ) ) {
							continue;
						} ?>
						<li id="list_user_id-<?php echo (int) $a_user_id ?>"><?php printf( '<a href="%s" class="si_tooltip" title="%s">%s</a>', admin_url( 'user-edit.php?user_id=' . $a_user_id ), $u->user_email, $u->display_name ) ?>  <a data-id="<?php echo (int) $a_user_id ?>" class="remove_user sc_del_button">X</a> <?php do_action( 'engagement_assigned_user_list', $a_user_id ) ?></li>
					<?php endforeach ?>
				</ul>
				<div id="hidden_assigned_users_list" class="cloak">
					<?php foreach ( $assigned_users as $a_user_id ) : ?>
						<input type="hidden" name="assigned_users[]" value="<?php echo (int) $a_user_id ?>" />
					<?php endforeach ?>
				</div>
			<?php else : ?>
				<ul id="assigned_users_list"></ul>
				<div id="hidden_assigned_users_list" class="cloak"></div>
			<?php endif ?>

			<a href="#TB_inline?width=300&height=200&inlineId=user_creation_modal" id="user_creation_modal_tb_link" class="thickbox button" title="<?php _e( 'Create new user for this engagement', 'sprout-invoices' ) ?>"><?php _e( 'New User', 'sprout-invoices' ) ?></a>
		</p>
		<hr/>
		<p><?php printf( __( '<b>Created:</b> %s' , 'sprout-invoices' ), date( $date_format, strtotime( $post->post_date ) ) ) ?></p>
		<p><?php printf( __( '<b>Updated:</b> %s' , 'sprout-invoices' ), date( $date_format, strtotime( $post->post_modified ) ) ) ?></p>
		<?php do_action( 'engagement_submit_post_users_list' ) ?>
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
			if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 == $post->ID ) {
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
