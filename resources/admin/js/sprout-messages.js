;(function( $, sc, undefined ) {

	sc.messageEdit = {
		config: {
		},
	};

	sc.messageSettings = {
		config: {
		},
	};

	sc.messageEdit.redactor_init = function() {
		$('#message_creation_form_wrap .si_redactorize').redactor();
	};

	sc.messageEdit.createMessage = function( $button ) {
		var $widget = $('#message_creation_form_wrap'),
			$fields = $( "#message_creation_form :input" ).serializeArray(),
			$save_button_og_text = $button.text();
		
		$button.after( _sprout_clients.spinner );
		$.post( ajaxurl, { action: 'sc_create_message', serialized_fields: $fields },
			function( response ) {
				//$('.sc_spinner').remove();
				$('.inline_error_message').remove();
				if ( ! response.success ) {
					$button.after('<span class="inline_error_message">' + response.data.error_message + '</span>');	
				}
				else {
					sc.messageEdit.refreshMessagesMetaBox( response.data.view );
					$widget.html('');
				}
			}
		);
	};

	sc.messageEdit.editMessage = function( $button ) {
		var $widget = $('#message_creation_form_wrap'),
			$fields = $( "#message_creation_form :input" ).serializeArray(),
			$save_button_og_text = $button.text();
		
		$button.after( _sprout_clients.spinner );
		$.post( ajaxurl, { action: 'sc_edit_message', serialized_fields: $fields },
			function( response ) {
				//$('.sc_spinner').remove();
				$('.inline_error_message').remove();
				if ( ! response.success ) {
					$button.after('<span class="inline_error_message">' + response.data.error_message + '</span>');	
				}
				else {
					sc.messageEdit.refreshMessagesMetaBox( response.data.view );
					$widget.html('');
				}
			}
		);
	};

	sc.messageEdit.showMessagesFormMetaBox = function( $client_id ) {
		var $widget = $('#message_creation_form_wrap');

		$.post( ajaxurl, { action: 'sc_create_message_form', client_id: $client_id },
			function( response ) {
				$('.sc_spinner').remove();
				$('.inline_error_message').remove();
				if ( ! response.success ) {
					$button.after('<span class="inline_error_message">' + response.data.error_message + '</span>');	
				}
				else {
					$widget.html( response.data.view );
					sc.clientEdit.select2_init();
					sc.messageEdit.redactor_init();
					$('.sc_spinner').remove();
				}
			}
		);
	};

	sc.messageEdit.editMessagesFormMetaBox = function( $message_id ) {
		var $widget = $('#message_creation_form_wrap');
		$.post( ajaxurl, { action: 'sc_edit_message_form', message_id: $message_id },
			function( response ) {
				$('.sc_spinner').remove();
				$('.inline_error_message').remove();
				if ( ! response.success ) {
					$widget.after('<span class="inline_error_message">' + response.data.error_message + '</span>');	
				}
				else {
					$widget.html( response.data.view );
					sc.clientEdit.select2_init();
					sc.messageEdit.redactor_init();
					$('.sc_spinner').remove();
				}
			}
		);
	};

	sc.messageEdit.refreshMessagesMetaBox = function( view ) {
		var $widget = $('#message_message_wrap');
		$widget.html( view );
	};

	sc.messageEdit.deleteMessage = function( message_id ) {
		var $message_wraps = $( '.client_message.message-' + message_id ),
			nonce = _sprout_clients.security;

		$.post( ajaxurl, { action: 'sc_delete_message', message_id: message_id, nonce: nonce },
			function( response ) {
				if ( ! response.success ) {
					console.log( response.error );
				}
				else {
					$message_wraps.fadeOut();
				}
			}
		);
	};

	/**
	 * Edit Management Methods
	 */
	sc.messageEdit.init = function() {

		$('#show_create_message_form').on('click', function(e) {
			e.stopPropagation();
			e.preventDefault();
			var $client_id = $( this ).data('client-id');
			$( this ).hide();
			$( this ).after( _sprout_clients.spinner );
			sc.messageEdit.showMessagesFormMetaBox( $client_id );
		});

		$('#create_message').on('click', function(e) {
			e.stopPropagation();
			e.preventDefault();
			sc.messageEdit.createMessage( $( this ) );
		});

		$('#edit_message').on('click', function(e) {
			e.stopPropagation();
			e.preventDefault();
			sc.messageEdit.editMessage( $( this ) );
		});

		$('.sc_delete_message').on('click', function(e) {
			e.stopPropagation();
			e.preventDefault();
			var $message_id = $( this ).data('message-id');
			$( this ).after( _sprout_clients.spinner );
			$( this ).remove();
			sc.messageEdit.deleteMessage( $message_id );
		});

		$('.sc_edit_message').on('click', function(e) {
			e.stopPropagation();
			e.preventDefault();
			var $message_id = $( this ).data('message-id');
			$('#show_create_message_form').hide();
			$('#show_create_message_form').after( _sprout_clients.spinner );
			//$( this ).hide();
			$( this ).after( _sprout_clients.spinner );
			sc.messageEdit.editMessagesFormMetaBox( $message_id );
		});

		$('#sa_message_format').on('click', function(e) {
			if ( $(this).is(':checked') ) {
				sc.messageEdit.redactor_init();
			}
			else {
				$('#sa_message_message').redactor( 'core.destroy' );
			}
		});

	}

})( jQuery, window.sc = window.sc || {} );

// Init
jQuery(function() {
	sc.messageEdit.init();
});