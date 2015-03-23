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

		var omise_card_name   = $( '#omise_card_name' ).val(),
			omise_card_number   = $( '#omise_card_number' ).val(),
			omise_card_expiration_month   = $( '#omise_card_expiration_month' ).val(),
			omise_card_expiration_year = $( '#omise_card_expiration_year' ).val(),
			omise_card_security_code    = $( '#omise_card_security_code' ).val();
		
		// Serialize the card into a valid card object.
		var card = {
		    "name": omise_card_name,
		    "number": omise_card_number,
		    "expiration_month": omise_card_expiration_month,
		    "expiration_year": omise_card_expiration_year,
		    "security_code": omise_card_security_code
		};
		
		var errors = OmiseUtil.validate_card(card);
		if(errors.length > 0){
			showError(errors, $form);
			return false;
		}else{
			hideError();
			if(Omise){
				Omise.setPublicKey(omise_params.key);
				Omise.createToken("card", card, function (statusCode, response) {
				    if (statusCode == 200) {
				    	$( '#omise_card_name' ).val("");
				    	$( '#omise_card_number' ).val("");
				    	$( '#omise_card_expiration_month' ).val("");
				    	$( '#omise_card_expiration_year' ).val("");
				    	$( '#omise_card_security_code' ).val("");
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
				    		showError( "Unable to create a card. " + response.message, $form );
				    	}else if(response.responseJSON && response.responseJSON.message){
				    		showError( "Unable to create a card. " + response.responseJSON.message, $form );
				    	}else if(response.status==0){
				    		showError( "Unable to create a card. No response from Omise Api.", $form );
				    	}else {
				    		showError( "Unable to create a card [ status=" + response.status + " ].", $form );
				    	}
				    };
				  });
			}else{
				showError( 'Something wrong with connection to Omise.js. Please check your network connection', $form );
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
