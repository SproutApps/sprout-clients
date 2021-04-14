;(function( $, sc, undefined ) {

	sc.engagementEdit = {
		config: {
		},
	};

	sc.engagementSettings = {
		config: {
		},
	};

	sc.engagementEdit.changeType = function( $selection ) {
		var $selection_wrap = $selection.closest('.type_change_selection'),
			$button_wrap = $selection.closest('.sc_type_update'),
			item_id = $selection_wrap.data('item-id'),
			nonce = $selection_wrap.data('nonce'),
			type_id = $selection.data('type-id');
		
		$.post( ajaxurl, { action: 'sc_change_engagement_type', type_id: type_id, engagement_id: item_id, security: nonce },
			function( data ) {
				if ( data.error ) {
					$selection_wrap.html( data.response );	
				}
				else {
					// swap out the button with the new one
					$button_wrap.parent().html( data );
				};
				return data;
			}
		);
	};

	sc.engagementEdit.updateStatusOption = function( $selection, context ) {
		var $term_id = $selection.val(),
			$selections = $selection.closest('.status_change_selection'),
			$selections_wrap = $selection.closest('.sc_statuses_update'),
			item_id = $selections.data('item-id'),
			nonce = $selections.data('nonce'),
			status_id = $selection.val();
		
		$.post( ajaxurl, { action: 'sc_edit_engagement_status', type_id: status_id, engagement_id: item_id, security: nonce, context: context },
			function( data ) {
				if ( data.error ) {
					$selections_wrap.html( data.response );	
				}
				if ( 'add' === context ) {
					$( '#current_statuses_' + item_id + '.sc_current_statuses .sc_status.status_id_' + status_id ).addClass('current');
				}
				else {
					$( '#current_statuses_' + item_id + '.sc_current_statuses .sc_status.status_id_' + status_id ).removeClass('current');	
				};
				return true;
			}
		);
	};

	sc.engagementEdit.addUserToEngagement = function( $user_id, $user_name, $edit_url ) {
		var $dl = $('#assigned_users_list'),
			user_item = '<li id="list_user_id-'+$user_id+'"><a href="'+$edit_url+'">'+$user_name+'</a> <a data-id="'+$user_id+'" class="remove_user sc_del_button">X</a></li>';
		
		$dl.append( user_item );
		$('#assigned_users_list').append($('<input/>', {
							type: 'hidden',
							name: 'assigned_users[]',
							value: $user_id
						}));
	};

	sc.engagementEdit.removeUserFromEngagement = function( $user_id ) {
		$('#list_user_id-'+$user_id).remove();
		$('#hidden_assigned_users_list').find( '[value="'+$user_id+'"]' ).remove();
	};


	sc.engagementEdit.createUser = function( $button ) {
		var $fields = $( "#user_create_form :input" ).serializeArray(),
			$engagement_id = $( "#sa_user_engagement_id" ).val(),
			$save_button_og_text = $button.text();

		$button.after( _sprout_clients.spinner );
		$.post( ajaxurl, { action: 'sa_create_user', serialized_fields: $fields },
			function( response ) {
				$('.sc_spinner').remove();
				$('.inline_error_message').remove();
				if ( ! response.success ) {
					$button.after('<span class="inline_error_message">' + response.data.error_message + '</span>');	
				}
				else {
					sc.engagementEdit.addUserToEngagement( response.data.user_id, response.data.display_name, '' );
					self.parent.tb_remove();
				}
			}
		);
	};

	sc.engagementEdit.createNote = function( $add_button ) {
		var post_id = $add_button.data( 'post-id' ),
			nonce = _sprout_clients.security,
			$private_note = $( '[name="private_note"]' ),
			add_button_og_text = $add_button.text();
		
		$add_button.html( '' );
		$add_button.append( _sprout_clients.spinner );
		$.post( ajaxurl, { action: 'sc_create_engagement_private_note', associated_id: post_id, notes: $private_note.val(), security: nonce },
			function( response ) {
				console.log(response);
				if ( response.success ) {
					var tr = '<tr><td>' + response.data.type + '</td><td>' + response.data.post_date + '</td><td>' + response.data.content + '</td><td>&nbsp;</td></tr>';
					$('#engagement_history tbody').prepend( tr );
					$private_note.val('');

				}
				else {
					$add_button.after('<span class="inline_message inline_error_message">' + response.data.message + '</span>');
				};

				$add_button.html( add_button_og_text );
				return true;
			}
		);
	};

	sc.engagementEdit.deleteRecord = function( button ) {
		var $button = $(button),
			record_id = $button.data( 'id' ),
			$record_wraps = $( '.record-' + record_id ),
			nonce = _sprout_clients.security;

		$.post( ajaxurl, { action: 'si_delete_record', record_id: record_id, nonce: nonce },
			function( response ) {
				console.log(response);
				if ( response.error ) {
					console.log( response.error );
				}
				else {
					$record_wraps.fadeOut();
				}
			}
		);
	};

	sc.engagementEdit.editPrivateNote = function( button ) {
		var $button = $(button),
			record_id = $button.data( 'id' ),
			private_note = $( '#sa_note_note' ).val(),
			nonce = _sprout_clients.security;

		$('span.inline_error_message').hide();
		$button.after( _sprout_clients.spinner );
		$.post( ajaxurl, { action: 'si_edit_private_note', record_id: record_id, private_note: private_note, nonce: nonce },
			function( response ) {
				$('.sc_spinner').remove();
				$('.inline_error_message').remove();
				if ( response.error ) {
					$button.after('<span class="inline_error_message">' + response.response + '</span>');	
				}
				else {
					// close modal
					self.parent.tb_remove();
					$( '.record-' + record_id + ' p:first-of-type' ).html( private_note );
				}
			}
		);
	};

	sc.engagementEdit.select2_init = function() {
		$('.sa_select2').select2({
			// Support for optgroup searching
			matcher: function modelMatcher (params, data) {
				data.parentText = data.parentText || "";

				// Always return the object if there is nothing to compare
				if ($.trim(params.term) === '') {
					return data;
				}

				// Do a recursive check for options with children
				if (data.children && data.children.length > 0) {
					// Clone the data object if there are children
					// This is required as we modify the object to remove any non-matches
					var match = $.extend(true, {}, data);

					// Check each child of the option
					for (var c = data.children.length - 1; c >= 0; c--) {
						var child = data.children[c];
						child.parentText += data.parentText + " " + data.text;

						var matches = modelMatcher(params, child);

						// If there wasn't a match, remove the object in the array
						if (matches == null) {
							match.children.splice(c, 1);
						}
					}

					// If any children matched, return the new object
					if (match.children.length > 0) {
						return match;
					}

					// If there were no matching children, check just the plain object
					return modelMatcher(params, match);
				}

				// If the typed-in term matches the text of this term, or the text from any
				// parent term, then it's a match.
				var original = (data.parentText + ' ' + data.text).toUpperCase();
				var term = params.term.toUpperCase();


				// Check if the text contains the term
				if (original.indexOf(term) > -1) {
					return data;
				}

				// If it doesn't contain the term, don't return anything
				return null;
			}
		});
	};

	/**
	 * Edit Management Methods
	 */
	sc.engagementEdit.init = function() {

		/**
		 * select2 init
		 */
		sc.engagementEdit.select2_init();

		/**
		 * Remove user and hidden option associated list
		 */
		$('.item_add_type').on('click', function(e) {
			e.stopPropagation();
			e.preventDefault();
			sc.engagementEdit.changeType( $( this ) );
		});

		/**
		 * Remove user and hidden option associated list
		 */
		$('.sc_statuses_update input').on('change', function(e) {
			var $selection = $( this );
			if ( $selection.is(':checked') ) {
				sc.engagementEdit.updateStatusOption( $selection, 'add' );
			}
			else {
				sc.engagementEdit.updateStatusOption( $selection, 'remove' );
			};
		});

		// Associate Users
		$('#assigned_users').on('change', function(e) {
			e.stopPropagation();
			e.preventDefault();

			var $data = $(this).select2('data')[0],
				$option = $(this).find("option:selected"),
				$user_id = $data.id,
				$user_name = $data.text,
				$edit_url = $option.data('url');

			sc.engagementEdit.addUserToEngagement( $user_id, $user_name, $edit_url );
		});

		/**
		 * Remove user and hidden option associated list
		 */
		$('.remove_user').on('click', function(e) {
			e.stopPropagation();
			e.preventDefault();

			var $user_id = $( this ).data('id');

			sc.engagementEdit.removeUserFromEngagement( $user_id );
		});

		/**
		 * Create user via ajax
		 */
		$('#sc_create_user').on('click', function(e) {
			sc.engagementEdit.createUser( $(this) );
		});

		// Create private note
		$("#save_private_engagement_note").on('click', function(e) {
			e.stopPropagation();
			e.preventDefault();
			sc.engagementEdit.createNote( $( this ) );
		});

		/**
		 * delete engagement history record
		 */
		$('.delete_engagement_record').on( 'click', function(e) {
			e.stopPropagation();
			e.preventDefault();
			sc.engagementEdit.deleteRecord( this );
		});

		/**
		 * edit private note
		 */
		$('#save_edit_private_note').on( 'click', function(e) {
			e.stopPropagation();
			e.preventDefault();
			sc.engagementEdit.editPrivateNote( this );
		});
	}

})( jQuery, window.sc = window.sc || {} );

// Init
jQuery(function() {
	sc.engagementEdit.init();
});