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
			if($card_list.length > 0 && $selected_card_id.length === 0){
				return false;
			}
			
			return true;
		}
		
		function getSelectedCardId(){
			$selected_card_id = $("input[name='card_id']:checked");
			if($selected_card_id.length > 0){
				return $selected_card_id.val();
			}
			
			return "";
		}
		
		if ( $( '#payment_method_omise' ).is( ':checked' ) ) {
			if( !validSelection() ){
				showError( omise_params.no_card_selected );
				return false;
			}
			
			if( getSelectedCardId() !== "" )
			{
				//submit the form right away if the card_id is not blank
				return true;
			}
			
			if ( 0 === $( 'input.omise_token' ).length ) {
				$form.block({
					message: null,
					overlayCSS: {
						background: '#fff url(' + wc_checkout_params.ajax_loader_url + ') no-repeat center',
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
					showError(errors);
					$form.unblock();
					return false;
				}

				hideError();

				if(Omise){
					Omise.setPublicKey(omise_params.key);
					Omise.createToken("card", omise_card, function (statusCode, response) {
						if (statusCode == 200) {
							$.each( omise_card_fields, function( index, field ) {
								field.val( '' );
							} );
							$form.append( '<input type="hidden" class="omise_token" name="omise_token" value="' + response.id + '"/>' );
							$form.submit();
						} else {
							if ( response.object && 'error' === response.object && 'invalid_card' === response.code ) {
								showError( omise_params.invalid_card + "<br/>" + response.message );
							} else if(response.message){
								showError( omise_params.cannot_create_token + "<br/>" + response.message );
							}else if(response.responseJSON && response.responseJSON.message){
								showError( omise_params.cannot_create_token + "<br/>" + response.responseJSON.message );
							}else if(response.status==0){
								showError( omise_params.cannot_create_token + "<br/>" + omise_params.cannot_connect_api + omise_params.retry_checkout );
							}else {
								showError( omise_params.cannot_create_token + "<br/>" + omise_params.retry_checkout );
							}
							$form.unblock();
						};
					});
				}else{
					showError( omise_params.cannot_load_omisejs + '<br/>' + omise_params.check_internet_connection );
					$form.unblock();
				}
				
				return false;
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
