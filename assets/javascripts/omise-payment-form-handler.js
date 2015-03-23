(function ( $, undefined ) {
	var $form = $( 'form.checkout, form#order_review' );
	
	function omiseFormHandler(){
		function showError(message){
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
			
			$form.prepend( $ulError );
			$("html, body").animate({
				 scrollTop:0
				 },"slow");
		}
		
		function hideError(){
			$(".woocommerce-error").remove();
		}
		
		function validSelection(){
			$card_list = $("input[name='card_id']");
			$selected_card_id = $("input[name='card_id']:checked");
			// there is some existing cards but nothing selected then warning
			if($card_list.size() > 0 && $selected_card_id.size() === 0){
				return false;
			}
			
			return true;
		}
		
		function getSelectedCardId(){
			$selected_card_id = $("input[name='card_id']:checked");
			if($selected_card_id.size() > 0){
				return $selected_card_id.val();
			}
			
			return "";
		}
		
		if ( $( '#payment_method_omise' ).is( ':checked' ) ) {
			if( !validSelection() ){
				showError("Please select a card or enter new payment information");
				return false;
			}
			
			if( getSelectedCardId() !== "" )
			{
				//submit the form right away if the card_id is not blank
				return true;
			}
			
			if ( 0 === $( 'input.omise_token' ).size() ) {
				$form.block({
					message: null,
					overlayCSS: {
						background: '#fff url(' + wc_checkout_params.ajax_loader_url + ') no-repeat center',
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
					showError(errors);
					$form.unblock();
					return false;
				}else{
					hideError();
					if(Omise){
						Omise.setPublicKey(omise_params.key);
						Omise.createToken("card", card, function (statusCode, response) {
						    if (statusCode == 200) {
						    	$form.append( '<input type="hidden" class="omise_token" name="omise_token" value="' + response.id + '"/>' );
						    	$( '#omise_card_name' ).val("");
						    	$( '#omise_card_number' ).val("");
						    	$( '#omise_card_expiration_month' ).val("");
						    	$( '#omise_card_expiration_year' ).val("");
						    	$( '#omise_card_security_code' ).val("");
								$form.submit();
						    } else {
						    	if(response.message){
						    		showError( "Unable to process payment with Omise. " + response.message );
						    	}else if(response.responseJSON && response.responseJSON.message){
						    		showError( "Unable to process payment with Omise. " + response.responseJSON.message );
						    	}else if(response.status==0){
						    		showError( "Unable to process payment with Omise. No response from Omise Api." );
						    	}else {
						    		showError( "Unable to process payment with Omise [ status=" + response.status + " ]" );
						    	}
						    	$form.unblock();
						    };
						  });
					}else{
						showError( 'Something wrong with connection to Omise.js. Please check your network connection' );
						$form.unblock();
					}
					
					return false;
				}
			}
			
		}
	}
	
	$(function(){
		$( 'body' ).on( 'checkout_error', function () {
			$( '.omise_token' ).remove();
		});
		
		$( 'form.checkout' ).unbind('checkout_place_order_omise');
		$( 'form.checkout' ).on( 'checkout_place_order_omise', function () {
			return omiseFormHandler();
		});
		
		/* Pay Page Form */
		$( 'form#order_review' ).on( 'submit', function () {
			return omiseFormHandler();
		});
		
		/* Both Forms */
		$( 'form.checkout, form#order_review' ).on( 'change', '#omise_cc_form input', function() {
			$( '.omise_token' ).remove();
		});
	})
})(jQuery)
