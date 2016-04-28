<?php

if ( ! function_exists( 'sc_type_select' ) ) :
	function sc_type_select( $client_id = 0 ) {
		if ( ! $client_id ) {
			$client_id = get_the_ID();
		}
		print apply_filters( 'sc_type_select', sc_get_type_select( $client_id ), $client_id );
	}
endif;

if ( ! function_exists( 'sc_get_type_select' ) ) :
	function sc_get_type_select( $client_id = 0 ) {
		if ( ! $client_id ) {
			$client_id = get_the_ID();
		}
		$client = Sprout_Client::get_instance( $client_id );
		$current_type = $client->get_type();
		ob_start();
		?>
		<div id="type_<?php echo (int) $client_id ?>" class="sc_type_update client_type <?php echo $current_type->slug ?>">
			<span class="type_change_button" data-sa-dropdown="#type_change_<?php echo (int) $client_id ?>" data-horizontal-offset="-5">
				<?php printf( '<span class="sc_type button button-large current_type" title="%1$s"><b>%2$s</b>&nbsp;<span class="dashicons dashicons-arrow-down"></span></span>', sc__( 'Change Type of Client' ), $current_type->name ); ?>
			</span>
			<div id="type_change_<?php echo (int) $client_id ?>" class="sa-dropdown sa-dropdown-tip sa-dropdown-relative sa-dropdown-anchor-right type_change_selection" data-item-id="<?php echo (int) $client_id ?>" data-nonce="<?php echo wp_create_nonce( SC_Controller::NONCE ) ?>">
				<ul class="sa-dropdown-menu">
					<?php foreach ( sc_get_client_types() as $term_id => $label ) : ?>
						<li><a href="#" class="item_add_type" data-type-id="<?php echo (int) $term_id ?>"><b><?php echo esc_html( $label ) ?></b></a></li>
					<?php endforeach ?>
					<li class="sa-dropdown-divider"></li>
					<li><a href="<?php echo admin_url( 'edit-tags.php?taxonomy=sc_client_type&post_type=sa_client' ) ?>"><?php sc_e( 'Add/Edit Client Types' ) ?></a></li>
				</ul>
			</div>
		</div>
	<?php
	$view = ob_get_clean();
	return apply_filters( 'sc_get_type_select', $view, $client_id );
	}
endif;

if ( ! function_exists( 'sc_status_select' ) ) :
	function sc_status_select( $client_id = 0 ) {
		if ( ! $client_id ) {
			$client_id = get_the_ID();
		}
		print apply_filters( 'sc_status_select', sc_get_status_select( $client_id ), (int) $client_id );
	}
endif;

if ( ! function_exists( 'sc_get_status_select' ) ) :
	function sc_get_status_select( $client_id = 0 ) {
		if ( ! $client_id ) {
			$client_id = get_the_ID();
		}
		$client = Sprout_Client::get_instance( $client_id );
		$statuses = $client->get_statuses();
		$all_statuses = sc_get_client_statuses();
		$status_terms = get_terms( Sprout_Client::STATUS_TAXONOMY, array( 'hide_empty' => false ) );
		$status_ids = ( ! empty( $statuses ) ) ? wp_list_pluck( $statuses, 'term_id' ) : array() ;
		ob_start();
		?>
		<div id="statuses_<?php echo (int) $client_id ?>" class="sc_statuses_update">
			<span class="status_change_button" data-sa-dropdown="#add_status_<?php echo (int) $client_id ?>">
				<?php printf( '<button class="sc_add_status button" title="%1$s">%1$s&nbsp;<span class="dashicons dashicons-arrow-down"></span></button>', esc_attr( sc__( 'Status' ) ) ); ?>
			</span>&nbsp;<span class="helptip client_status_help" data-sa-dropdown="#client_status_select_help_<?php echo (int) $client_id ?>"></span>
			<div id="client_status_select_help_<?php echo (int) $client_id ?>" class="sa-dropdown sa-dropdown-tip sa-dropdown-relative ">
				<div class="sa-dropdown-panel">
					<?php sc_e( 'Select all statuses that currently fit this engagement.' ) ?>
				</div>
			</div>
			<div id="add_status_<?php echo (int) $client_id ?>" class="sa-dropdown sa-dropdown-tip sa-dropdown-relative status_change_selection" data-item-id="<?php echo (int) $client_id ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( SC_Controller::NONCE ) ) ?>" data-vertical-offset="50">
				<ul class="sa-dropdown-menu">
					<?php foreach ( $all_statuses as $term_id => $label ) : ?>
						<?php
							$checked = ( ! empty( $statuses ) && in_array( $term_id, $status_ids ) ) ? 'checked="checked"' : '' ;
							printf( '<li><label><input type="checkbox" value="%1$s" %3$s/><b>%2$s</b></label></li>', (int) $term_id, esc_html( $label ), esc_attr( $checked ) );
							?>					
					<?php endforeach ?>
					<li class="sa-dropdown-divider"></li>
					<li><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=sc_client_status&post_type=sa_client' ) ) ?>"><?php sc_e( 'Add/Edit Client Statuses' ) ?></a></li>
				</ul>
			</div>
		</div>
		<div id="current_statuses_<?php echo (int) $client_id ?>" class="sc_current_statuses">
			<?php foreach ( $status_terms as $term ) : ?>
				<?php
					$current_status = ( ! empty( $statuses ) && in_array( $term->term_id, $status_ids ) ) ? 'current' : '' ;
					$url = add_query_arg( array(
						'post_type' => Sprout_Client::POST_TYPE,
						Sprout_Client::STATUS_TAXONOMY => $term->slug,
					), admin_url( 'edit.php' ) ); ?>
				<?php printf( '<a href="%4$s" class="button button-small sc_status %2$s status_id_%3$s" title="%1$s" style="background-color:#%5$s">%1$s</a>', esc_attr( $term->name ), esc_attr( $current_status ), (int) $term->term_id, esc_url_raw( $url ), sc_get_client_status_color( $term->term_id ) ); ?>
			<?php endforeach ?>
		</div>
		<?php
		$view = ob_get_clean();
		return apply_filters( 'sc_get_status_select', $view, $client_id );
	}
endif;



if ( ! function_exists( 'sc_get_client_status_color' ) ) :
	function sc_get_client_status_color( $term_id = 0 ) {
		$color = Sprout_Clients_Tax::get_term_color( $term_id );
		return apply_filters( 'sc_get_client_status_color', $color );
	}
endif;

if ( ! function_exists( 'sc_get_all_status_select' ) ) :
	function sc_get_all_status_select() {
		$status_terms = get_terms( Sprout_Client::STATUS_TAXONOMY, array( 'hide_empty' => false ) );
		ob_start();
		?>
		<div id="statuses_filter" class="sc_statuses_filter">
			<span class="status_filter_button" data-sa-dropdown="#filter_status_all">
				<?php printf( '<button class="sc_filter_status button" title="%1$s">%1$s&nbsp;<span class="dashicons dashicons-arrow-down"></span></button>', esc_attr( sc__( 'Status' ) ) ); ?>
			</span>&nbsp;<span class="helptip client_filter_help" data-sa-dropdown="#client_status_filter_help_all"></span>
			<div id="client_status_filter_help_all" class="sa-dropdown sa-dropdown-tip sa-dropdown-relative ">
				<div class="sa-dropdown-panel">
					<?php sc_e( 'Select all statuses that currently fit this contact.' ) ?>
				</div>
			</div>
			<div id="filter_status_all" class="sa-dropdown sa-dropdown-tip sa-dropdown-relative status_filter_selection" data-vertical-offset="50">
				<ul class="sa-dropdown-menu">
					<?php foreach ( $status_terms as $term ) : ?>
						<?php
							$checked = '' ;
							printf( '<li><label><input type="checkbox" value="%1$s" name="%4$s" %3$s/><b>%2$s</b></label></li>', $term->slug, esc_html( $term->name ), esc_attr( $checked ), Sprout_Client::STATUS_TAXONOMY );
							?>					
					<?php endforeach ?>
					<li class="sa-dropdown-divider"></li>
					<li><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=sc_client_status&post_type=sa_client' ) ) ?>"><?php sc_e( 'Add/Edit Client Statuses' ) ?></a></li>
				</ul>
			</div>
		</div>
		<?php
		$view = ob_get_clean();
		return apply_filters( 'sc_get_all_status_select', $view );
	}
endif;

if ( ! function_exists( 'sc_get_client_statuses' ) ) :
	function sc_get_client_statuses( $assocated_array = true ) {
		$statuses = array();
		$terms = get_terms( Sprout_Client::STATUS_TAXONOMY, array( 'hide_empty' => false ) );
		foreach ( $terms as $term ) {
			$statuses[ $term->term_id ] = $term->name;
		}
		return apply_filters( 'sc_get_client_statuses', $statuses );
	}
endif;

if ( ! function_exists( 'sc_get_client_types' ) ) :
	function sc_get_client_types() {
		$types = array();
		$terms = get_terms( Sprout_Client::TYPE_TAXONOMY, array( 'hide_empty' => false ) );
		foreach ( $terms as $term ) {
			$types[ $term->term_id ] = $term->name;
		}
		return apply_filters( 'sc_get_client_types', $types );
	}
endif;

if ( ! function_exists( 'sa_get_truncate' ) ) :
	/**
 * Truncate a string, strip tags and append a more link
 * @param string  $text           string to truncate
 * @param integer $excerpt_length output length
 * @param boolean $more_link      add a more link
 * @return string                  truncated string w or w/o more link
 */
	function sa_get_truncate( $text, $excerpt_length = 44, $more_link = false ) {

		$text = strip_shortcodes( $text );

		$text = apply_filters( 'the_excerpt', $text );
		$text = str_replace( ']]>', ']]&gt;', $text );
		$text = strip_tags( $text );

		$words = explode( ' ', $text, $excerpt_length + 1 );
		if ( count( $words ) > $excerpt_length ) {
			array_pop( $words );
			$text = implode( ' ', $words );
			$text = rtrim( $text );
			$text .= '&hellip;';
		}
		if ( $more_link ) {
			$text = $text.' '.'<a href="'.$more_link.'" class="more">&nbsp;&raquo;</a>';
		}
		return apply_filters( 'sa_get_truncate', $text, $excerpt_length, $more_link );
	}
endif;
