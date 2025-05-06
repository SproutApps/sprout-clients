<?php


function _change_woo_si_invoice_due_date( $order_id = 0, $invoice_id = 0 ) {
	$invoice = SI_Invoice::get_instance( $invoice_id );
	$due_date = time() + ( 86400 * 5 ); // "5" is the days from now it will be due
	$invoice->set_due_date( $due_date );
}
add_action( 'si_woocommerce_payment', '_change_woo_si_invoice_due_date', 10, 2 );


/**
 * SC_Invoices_History Controller
 *
 *
 * @package SI_Client_History
 * @subpackage Clients
 */
class SC_Invoices_History extends SC_Client_History {

	public static function init() {
		if ( ! class_exists( 'Sprout_Invoices' ) ) {
			return;
		}
		add_filter( 'client_history', array( __CLASS__, 'add_records_to_client_history' ), 10, 3 );
	}

	public static function add_records_to_client_history( $history, Sprout_Client $client, $type = '' ) {
		$history = $history ?? array();
		$records = array();
		$invoices = $client->get_invoices();
		if ( ! empty( $invoices ) ) {
			foreach ( $invoices as $invoice_id ) {
				$records += SC_Record::get_records_by_association( $invoice_id );
			}
		}
		$estimates = $client->get_estimates();
		if ( ! empty( $estimates ) ) {
			foreach ( $estimates as $estimate_id ) {
				$records += SC_Record::get_records_by_association( $estimate_id );
			}
		}

		if ( ! empty( $records ) ) {

			foreach ( $records as $record_id ) {
				$record = SC_Record::get_instance( $record_id );
				if ( '' !== $type && $type !== $record->get_type() ) {
					continue;
				}
				// parent taking care of this one.
				if ( $record->get_type() === SC_Controller::PRIVATE_NOTES_TYPE ) {
					continue;
				}
				$r_post = $record->get_post();
				$time = strtotime( $r_post->post_date );
				$history[ $time ] = array(
					'id' => $record_id,
					'record_id' => $record_id,
					'title' => esc_html( $r_post->post_title ),
					'content' => wpautop( $r_post->post_content ),
					'date' => wpautop( $r_post->post_date ),
					'type_slug' => $record->get_type(),
					);

				switch ( $record->get_type() ) {

					// Sprout Invoices
					case 'si_history_update':
						$history[ $time ]['type'] = sc__( 'Estimate Updated' );
						break;

					case 'si_history_update':
						$history[ $time ]['type'] = sc__( 'Estimate Viewed' );
						break;

					case 'si_notification':
						$history[ $time ]['type'] = sc__( 'Notification' );
						ob_start();
						?>
							<p>
								<p>
									<a href="#TB_inline?width=600&height=380&inlineId=notification_message_<?php echo (int) $r_post->ID ?>" id="show_notification_tb_link_<?php echo (int) $r_post->ID ?>" class="thickbox si_tooltip notification_message" title="<?php _e( 'View Message', 'sprout-invoices' ) ?>"><?php _e( 'View Message', 'sprout-invoices' ) ?></a>
								</p>
								<div id="notification_message_<?php echo (int) $r_post->ID ?>" class="cloak">
									<?php echo wpautop( $r_post->post_content ) ?>
								</div>
							</p>
						<?php
						$notification_message = ob_get_clean();
						$history[ $time ]['content'] = $notification_message;
						break;

					case 'si_invoice_created':
						$history[ $time ]['type'] = sc__( 'Invoice Created' );
						break;

					case 'si_history_status_update':
					default:
						$history[ $time ]['type'] = sc__( 'Status Update' );
						unset( $history[ $time ]['title'] );
						break;
				}
			}
		}
		return $history;
	}

}