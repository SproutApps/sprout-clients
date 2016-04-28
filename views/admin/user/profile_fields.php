<h3><?php sc_e( 'Contact Information' ) ?></h3>
<table class="form-table">
<tr>
	<th>
		<label for="sc_dob"><?php sc_e( 'DOB' ); ?></label>
	</th>
	<td>
		<input type="text" name="sc_dob" id="sc_dob" value="<?php echo esc_attr( $dob ); ?>" class="regular-text" />
		<br><span class="description"><?php sc_e( 'Date of birth' ); ?></span>
	</td>
</tr>
<tr>
	<th>
		<label for="sc_phone"><?php sc_e( 'Phone' ); ?></label>
	</th>
	<td>
		<input type="text" name="sc_phone" id="sc_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text" />
		<br><span class="description"><?php sc_e( 'Phone number for this user.' ); ?></span>
	</td>
</tr>
<tr>
	<th>
		<label for="sc_twitter"><?php sc_e( 'Twitter' ); ?></label>
	</th>
	<td>
		<input type="text" name="sc_twitter" id="sc_twitter" value="<?php echo esc_attr( $twitter ); ?>" class="regular-text" />
		<br><span class="description"><?php _e( "User's Twitter handle" ); ?></span>
	</td>
</tr>
<tr>
	<th>
		<label for="sc_linkedin"><?php sc_e( 'Linkedin' ); ?></label>
	</th>
	<td>
		<input type="text" name="sc_linkedin" id="sc_linkedin" value="<?php echo esc_attr( $linkedin ); ?>" class="regular-text" />
		<br><span class="description"><?php sc_e( "User's Linkedin profile url" ); ?></span>
	</td>
</tr>
<tr>
	<th>
		<label for="sc_linkedin"><?php sc_e( 'Note' ); ?></label>
	</th>
	<td>
		<textarea name="sc_note" id="sc_note" rows="5" cols="30"><?php echo esc_textarea( $note ); ?></textarea>
		<br><span class="description"><?php sc_e( 'Save some notes about this person.' ); ?></span>
	</td>
</tr>
<?php if ( ! empty( $clients ) ) : ?>
	<tr>
		<th>
			<label for="sc_clients"><?php sc_e( 'Associated Clients' ); ?></label>
		</th>
		<td>
			<ul>
				<?php foreach ( $clients as $client_id ) : ?>
					<li><?php printf( '<a href="%s">%s</a>', get_edit_post_link( $client_id ), get_the_title( $client_id ) ) ?></li>
				<?php endforeach ?>
			</ul>
		</td>
	</tr>
<?php endif ?>
</table>