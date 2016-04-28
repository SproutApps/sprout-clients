<table id="engagement_history" class="sa_table">
	<thead>
		<tr>
			<th><?php _e( 'Type' , 'sprout-invoices' ) ?></th>
			<th><?php _e( 'Date' , 'sprout-invoices' ) ?></th>
			<th><?php _e( 'Info' , 'sprout-invoices' ) ?></th>
			<th><?php _e( 'Edit' , 'sprout-invoices' ) ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><span class="history_status created"><?php _e( 'Created' , 'sprout-invoices' ) ?></span></td>
			<td><?php echo date_i18n( get_option( 'date_format' ) . ' @ '.get_option( 'time_format' ), strtotime( $post->post_date ) ) ?></td>
			<td><?php printf( __( 'Authored by %1$s.' , 'sprout-invoices' ), sc_get_users_full_name( $post->post_author ) ) ?></td>
			<td>&nbsp;</td>
		</tr>
		<?php if ( ! empty( $history ) ) : ?>
			<?php foreach ( $history as $time => $data ) : ?>
			<tr class="record-<?php echo (int) (int) $data['id'] ?> type_<?php esc_attr( $data['type'] ) ?>">
				<td>
					<a href="<?php echo esc_url( remove_query_arg( 'history_page', add_query_arg( array( 'history_type' => $data['type_slug'] ) ) ) ) ?>" title="<?php echo esc_attr( $data['type'] ); ?>"><?php echo esc_html( $data['type'] ); ?></a>
				</td>
				<td>
					<?php if ( $time ) : ?>
						<?php echo date_i18n( get_option( 'date_format' ).' @ '.get_option( 'time_format' ), $time ) ?>
					<?php endif ?>
				</td>
				<td width="70%">
					<?php if ( isset( $data['title'] ) && $data['title'] ) : ?>
						<b><?php echo esc_html( $data['title'] ); ?></b>
					<?php endif ?>
					<?php if ( isset( $data['content'] ) && $data['content'] ) : ?>
						<?php echo wpautop( $data['content'] ) ?>
					<?php endif ?>
				</td>
				<td>
					<?php if ( isset( $data['edit'] ) && $data['edit'] ) : ?>
						<a class="thickbox edit_private_note" href="<?php echo admin_url( 'admin-ajax.php?action=si_edit_private_note_view&width=600&height=350&note_id=' . (int) $data['id'] ) ?>" id="show_edit_record_tb_link_<?php echo (int) $data['id'] ?>" title="<?php _e( 'Edit Note', 'sprout-invoices' ) ?>"><?php _e( 'Edit', 'sprout-invoices' ) ?></a>
					<?php endif ?>
					<?php if ( $data['id'] ) : ?>
						<span class="history_deletion"><button data-id="<?php echo (int) $data['id'] ?>" class="delete_engagement_record sc_del_button">X</button></span>
					<?php endif ?>
				</td>
			</tr>
			<?php endforeach ?>
		<?php endif ?>
	</tbody>
</table>
<div id="sa_history_table_pagination" class="clearfix">
	<?php if ( isset( $_REQUEST['history_type'] ) ) : ?>
		<a href="<?php echo esc_url( remove_query_arg( array( 'history_type', 'history_page' ) ) ) ?>"  class="button filter_history" id="view_all_notes"><?php _e( 'Clear Filter' , 'sprout-invoices' ); ?></a>
	<?php endif ?>
	<div class="sa_tablenav">
		<?php print $pagination ?>
	</div>
</div>
