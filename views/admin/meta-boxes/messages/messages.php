<?php
	printf( '<div id="message_creation_form_wrap" class="clearfix"><a href="javascript:void(0)" id="show_create_message_form" data-client-id="%s" class="button button-large">%s</a></div>', $client_id, __( 'Create message' ) ); ?>

<div id="message_message_wrap">
	<table id="message_message" class="sa_table">
		<thead>
			<tr>
				<th><?php _e( 'Send/Sent' , 'sprout-invoices' ) ?></th>
				<th><?php _e( 'Subject' , 'sprout-invoices' ) ?></th>
				<th><?php _e( 'Message' , 'sprout-invoices' ) ?></th>
				<th><?php _e( 'Recipient' , 'sprout-invoices' ) ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! empty( $messages ) ) : ?>
				<?php foreach ( $messages as $post_id ) : ?>
				<?php
					$message = SC_Message::get_instance( $post_id );
					$subject = $message->get_subject();
					$status = $message->get_status();
					$content = $message->get_message_content();
					$recipients = $message->get_recipients();
					$send = $message->get_send_time();
					$sent = $message->get_sent_time(); ?>
				<tr class="client_message message-<?php echo (int) $post_id ?>">
					<td style="background-color: #eee; white-space: nowrap; ">
						<?php if ( $sent > 0 ) :  ?>
							<span class="sc_message_type message_type message_sent"><span class="rotate_type_name"><?php printf( __( '<b>Sent</b> %s ago', 'sprout-invoices' ), human_time_diff( current_time( 'timestamp' ), $sent ) ); ?></span></span>
						<?php else : ?>
							<span class="sc_message_type message_type"><span class="rotate_type_name"><?php echo date( get_option( 'date_format' ), $send ) ?></span></span>
						<?php endif ?>					
					</td>
					<td width="25%">
						<a class="thickbox preview_message" href="<?php echo admin_url( 'admin-ajax.php?action=sc_preview_message&width=750&height=450' ) ?>&message_id=<?php echo $post_id ?>" id="message_preview_modal_link" title="<?php _e( 'Preview Message', 'sprout-invoices' ) ?>"><?php echo sa_get_truncate( strip_tags( $subject ), 10 ) ?></a>
						<a class="thickbox preview_message" href="<?php echo admin_url( 'admin-ajax.php?action=sc_preview_message&width=750&height=450' ) ?>&message_id=<?php echo $post_id ?>" id="message_preview_modal_link" title="<?php _e( 'Preview Message', 'sprout-invoices' ) ?>"><span class="sc_preview_button"></span></a>
					</td>
					<td width="50%">
						<?php echo sa_get_truncate( strip_tags( $content ), 30 ) ?>
						<span class="message_edit"><button data-message-id="<?php echo (int) $post_id ?>" class="sc_edit_message sc_edit_button">X</button></span>
					</td>
					<td>
						<ul>
						<?php foreach ( $recipients as $user_id ) :  ?>
								<li class="message-recipient-<?php echo (int) $user_id ?> type_<?php esc_attr( $user_id ) ?>">
									<?php printf( '<span class="associated_user"><a href="%s">%s</a></span>', get_edit_user_link( $user_id ), sc_get_users_full_name( $user_id ) ) ?>
								</li>
						<?php endforeach ?>
						</ul>
					</td>
					<td>
						<span class="message_deletion"><button data-message-id="<?php echo (int) $post_id ?>" class="sc_delete_message sc_del_button">X</button></span>
					</td>
				</tr>
				<?php endforeach ?>
			<?php else : ?>
				<tr class="client_messages" colspan="5">
					<td><?php _e( 'There are no scheduled messages.', 'sprout-invoices' ) ?></td>
				</tr>
			<?php endif ?>
		</tbody>
	</table>
	<div id="sa_messages_table_pagination" class="clearfix">
		<div class="sa_tablenav">
			<?php print $pagination ?>
		</div>
	</div>
</div>
