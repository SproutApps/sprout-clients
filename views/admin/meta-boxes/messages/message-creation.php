<!-- Create Client Form -->
<div id="message_creation_form_wrap" class="sc_messages_form_wrap clearfix">
	<div id="message_creation_form" class="admin_fields clearfix">
		<?php sa_admin_fields( $fields, 'message' ); ?>
	</div>	
	<p>
		<a href="javascript:void(0)" id="create_message" class="button button-large button-primary"><?php _e( 'Create message', 'sprout-invoices' ) ?></a>
	</p>
</div>
