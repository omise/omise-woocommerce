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
		
		if ( $( '#payment_method_omise' ).is( ':checked' ) && $("#card_id").val()=="" ) {
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
						Omise.config.defaultHost = omise_params.vault_url;
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
						    	showError( response.responseJSON.message );
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

		return true;
	}
	
	$(function(){
		$( 'body' ).on( 'checkout_error', function () {
			$( '.omise_token' ).remove();
		});
		
		$( 'form.checkout' ).unbind('checkout_place_order_omise');
		$( 'form.checkout' ).on( 'checkout_place_order_omise', function () {
			return omiseFormHandler();
		});
	})
})(jQuery)