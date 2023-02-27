(function ( $, undefined ) {
	const $omise_card_panel = $("#omise_card_panel");
	const $form = $("#omise_cc_form");
	
	function showError(message, target) {
		if(target === undefined){
			target = $omise_card_panel;
		}
		
		target.unblock();
		
		if(!message){
			return;
		}
		$(".woocommerce-error, input.omise_token").remove();
		
		const $ulError = $("<ul>").addClass("woocommerce-error");
		
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
		const data = {
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
		hideError();
		$form.block({
			message: null,
			overlayCSS: {
				background: '#fff url(' + omise_params.ajax_loader_url + ') no-repeat center',
				backgroundSize: '16px 16px',
				opacity: 0.6
			}
		});
		OmiseCard.requestCardToken()
	}
	
	$(".delete_card").click(function(event){
		if(confirm('Confirm delete card?')){
			let $button = $(this);
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

	function saveCard(payload) {
		const data = {
			action: "omise_create_card",
			omise_token: payload.token,
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
	}

	showOmiseEmbeddedCardForm({
		element: document.getElementById('omise-card'),
		publicKey: omise_params.key,
		locale: LOCALE,
		theme: CARD_FORM_THEME ?? 'light',
		design: FORM_DESIGN,
		hideRememberCard: true,
		onSuccess: saveCard,
		onError: (error) => {
			showError(error)
			$form.unblock()
		}
	})
}
)(jQuery);
