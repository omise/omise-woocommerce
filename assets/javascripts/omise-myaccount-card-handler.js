(function ( $, undefined ) {
	$omise_card_panel = $("#omise_card_panel");
	$form = $("#omise_cc_form");
	
	function showError(message, target){
		if(target===undefined){
			target = $omise_card_panel;
		}
		
		target.unblock();
		
		if(!message){
			return;
		}
		$(".woocommerce-error, input.omise_token").remove();
		
		$ulError = $("<ul>").addClass("woocommerce-error");
		
		if($.isArray(message)){
			$.each(message, function(i,v){
				$ulError.append($("<li>" + v + "</li>"));
			})
		}else{
			$ulError.html("<li>" + message + "</li>");
		}
		
		target.prepend( $ulError );
	}
	
	function hideError(){
		$(".woocommerce-error").remove();
	}
	
	function delete_card(card_id, nonce){
		data = {
				action: "omise_delete_card", 
				card_id: card_id, 
				omise_nonce: nonce
				};
		
		$.post(omise_params.ajax_url, data, 
			function(response){
				if(response.deleted){
					window.location.reload();
				}else{
					showError(response.message);
				}
			}, "json"
		);
		
	}
	
	function create_card(){
		$form.block({
			message: null,
			overlayCSS: {
				background: '#fff url(' + omise_params.ajax_loader_url + ') no-repeat center',
				backgroundSize: '16px 16px',
				opacity: 0.6
			}
		});

		let errors                  = [],
			omise_card              = {},
			omise_card_number_field = 'number',
			omise_card_fields       = {
				'name'             : $( '#omise_card_name' ),
				'number'           : $( '#omise_card_number' ),
				'expiration_month' : $( '#omise_card_expiration_month' ),
				'expiration_year'  : $( '#omise_card_expiration_year' ),
				'security_code'    : $( '#omise_card_security_code' )
			};

		$.each( omise_card_fields, function( index, field ) {
			omise_card[ index ] = (index === omise_card_number_field) ? field.val().replace(/\s/g, '') : field.val();
			if ( "" === omise_card[ index ] ) {
				errors.push( omise_params[ 'required_card_' + index ] );
			}
		} );
		
		if ( errors.length > 0 ) {
			showError(errors, $form);
			return false;
		}else{
			hideError();
			if(Omise){
				Omise.setPublicKey(omise_params.key);
				Omise.createToken("card", omise_card, function (statusCode, response) {
				    if (statusCode == 200) {
						$.each( omise_card_fields, function( index, field ) {
							field.val( '' );
						} );

				    	data = {
								action: "omise_create_card", 
								omise_token: response.id, 
								omise_nonce: $("#omise_add_card_nonce").val() 
							    };
						
						$.post(omise_params.ajax_url, data, 
							function(wp_response){
								if(wp_response.id){
									window.location.reload();
								}else{
									showError(wp_response.message, $form);
								}
							}, "json"
						);
					} else {
						if(response.message){
							showError( omise_params.cannot_create_card + "<br/>" + response.message, $form );
						}else if(response.responseJSON && response.responseJSON.message){
							showError( omise_params.cannot_create_card + "<br/>" + response.responseJSON.message, $form );
						}else if(response.status==0){
							showError( omise_params.cannot_create_card + "<br/>" + omise_params.cannot_connect_api, $form );
						}else {
							showError( omise_params.retry_or_contact_support, $form );
						}
					};
				});
			}else{
				showError( omise_params.cannot_load_omisejs + '<br/>' + omise_params.check_internet_connection, $form );
			}
		}
	}
	
	$(".delete_card").click(function(event){
		if(confirm('Confirm delete card?')){
			var $button = $(this);
			$button.block({
				message: null,
				overlayCSS: {
					background: '#fff url(' + omise_params.ajax_loader_url + ') no-repeat center',
					backgroundSize: '16px 16px',
					opacity: 0.6
				}
			});
			delete_card($button.data("card-id"), $button.data("delete-card-nonce"));
		}
	});
	
	$("#omise_add_new_card").click(function(event){
		create_card();
	});
}
)(jQuery);
