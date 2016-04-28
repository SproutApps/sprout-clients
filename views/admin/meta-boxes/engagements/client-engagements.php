<div class="clearfix">
	<a class="thickbox button edit_private_note button-large" href="#TB_inline?width=600&height=380&inlineId=engagement_creation_modal" id="engagement_creation_modal_link" title="<?php _e( 'Create Engagement', 'sprout-invoices' ) ?>"><?php _e( 'Quick Engagement', 'sprout-invoices' ) ?></a>
</div>
<table id="engagement_engagement" class="sa_table">
	<thead>
		<tr>
			<th></th>
			<th><?php _e( 'Info' , 'sprout-invoices' ) ?></th>
			<th><?php _e( 'Notes' , 'sprout-invoices' ) ?></th>
			<th><?php _e( 'Edit' , 'sprout-invoices' ) ?></th>
		</tr>
	</thead>
	<tbody>
		<?php if ( ! empty( $engagements ) ) : ?>
			<?php foreach ( $engagements as $post_id ) : ?>
			<?php
				$engagement = Sprout_Engagement::get_instance( $post_id );
				$history = $engagement->get_history( SC_Controller::PRIVATE_NOTES_TYPE );
				$current_type = $engagement->get_type();
				$statuses = $engagement->get_statuses(); ?>
			<tr class="client_engagement engagement-<?php echo (int) $post_id ?>">
				<td style="background-color: #eee;">
					<span class="sc_engagement_type engagement_type"><span class="rotate_type_name"><?php echo $current_type->name ?></span></span>
				</td>
				<td>
					<a href="<?php echo get_edit_post_link( $post_id ) ?>" title="<?php _e( 'Edit Engagment', 'sprout-invoices' ) ?>" class="engagement_title"><?php echo get_the_title( $post_id ) ?></a>
					<?php if ( ! empty( $statuses ) ) :  ?>	
						<p class="sc_engagement_current_statuses">
							<?php foreach ( $statuses as $status ) :  ?>
								<?php
									$url = add_query_arg( array(
										'post_type' => Sprout_Engagement::POST_TYPE,
										Sprout_Engagement::STATUS_TAXONOMY => $status->slug,
									), admin_url( 'edit.php' ) ); ?>
								<?php printf( '<a href="%3$s" class="button button-small sc_status status_id_%2$s" title="%1$s">%1$s</a>', esc_attr( $status->name ), (int) $status->term_id, esc_url_raw( $url ) ); ?>
							<?php endforeach ?>	
						</p>
					<?php endif ?>
					<p>
						<small><?php printf( '<b>Start:</b> %s', date_i18n( get_option( 'date_format' ), $engagement->get_start_date() ) ) ?></small><br/>
						<small><?php printf( '<b>End:</b> %s', date_i18n( get_option( 'date_format' ), $engagement->get_end_date() ) ) ?></small>
					</p>
				</td>
				<td width="75%">
					<ul>
					<?php foreach ( $history as $time => $data ) :  ?>
							<li class="record-<?php echo (int) (int) $data['id'] ?> type_<?php esc_attr( $data['type'] ) ?>">
								<?php if ( isset( $data['title'] ) && $data['title'] ) : ?>
									<b><?php echo esc_html( $data['title'] ); ?></b>&nbsp;<small><?php echo date_i18n( get_option( 'date_format' ), $time ) ?></small>
								<?php endif ?>
								<?php if ( isset( $data['content'] ) && $data['content'] ) : ?>
									<?php echo wpautop( $data['content'] ); ?>
								<?php endif ?>
							</li>
					<?php endforeach ?>
					</ul>
				</td>
				<td>
					<a href="<?php echo get_edit_post_link( $post_id ) ?>" title="<?php _e( 'Edit Engagment', 'sprout-invoices' ) ?>"><?php _e( 'Edit', 'sprout-invoices' ) ?></a>
					<span class="engagement_deletion"><button data-id="<?php echo (int) $post_id ?>" class="delete_engagement_engagement sc_del_button">X</button></span>
				</td>
			</tr>
			<?php endforeach ?>
		<?php endif ?>
	</tbody>
</table>
<div class="clearfix">
	<a href="<?php echo add_query_arg( array( 'post_type' => Sprout_Engagement::POST_TYPE ), admin_url( 'post-new.php' ) ) ?>" id="create_new_engagement" class="page-title-action"><?php sc_e( 'Add Engagement' ) ?></a>
</div>
