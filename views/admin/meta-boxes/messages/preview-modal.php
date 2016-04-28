<!-- Create Client Form -->
<div id="engagement_creation_modal" style="display:none;">
	<div id="engagement_creation_form" class="clearfix">
		<?php sa_form_fields( $fields, 'engagement' ); ?>
	</div>	
	<p>
		<a href="javascript:void(0)" id="create_engagement" class="button button-large button-primary"><?php sc_e( 'Create engagement' ) ?></a>
	</p>
</div>
