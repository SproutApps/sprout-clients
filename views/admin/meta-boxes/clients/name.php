<div id="context_updates" class="clearfix">
	<div class="clearfix">
		<?php $title = ( 'auto-draft' !== $post_status  ) ? get_the_title( $id ) : '' ; ?>
		<div id="titlediv">
			<?php if ( '' === $title ) : ?>
				<label class="" id="title-prompt-text" for="title"><?php sc_e( 'Contact Name' ) ?></label>
			<?php endif ?>
			<input type="text" name="post_title" size="30" value="<?php echo esc_html( $title ); ?>" id="title">
		</div>

		<div id="sc_type_select">
			<?php sc_type_select() ?>&nbsp;<span class="helptip client_type_help" data-sa-dropdown="#client_type_select_help"></span>
			<div id="client_type_select_help" class="sa-dropdown sa-dropdown-tip sa-dropdown-relative ">
				<div class="sa-dropdown-panel">
					<?php _e( 'Select the type of client this contact is. If a type does not exist you can always create a new one that better fits.' , 'sprout-invoices' ) ?>
				</div>
			</div>
		</div><!-- #sc_type_select -->
	</div>

	<div id="sc_status_selections" class="clearfix">
		<?php sc_status_select( $id ) ?>
	</div><!-- #sc_status_selections -->
</div>