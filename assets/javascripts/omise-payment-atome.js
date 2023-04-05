(function ($) {
	const $form = $('form.checkout, form#order_review');

	function hideError() {
		$(".woocommerce-error").remove();
	}

	function showError(message) {
		if (!message) {
			return;
		}

		let $ulError = $("<ul>").addClass("woocommerce-error");
        $ulError.html("<li>" + message + "</li>");
        $form.prepend($ulError);
		$("html, body").animate({ scrollTop:0 },"slow");
	}

	function omiseFormHandler() {
        hideError();
		if ($('#payment_method_omise_atome').is(':checked')) {
            if ($("#omise_atome_phone_default").is(":checked")) {
                return true;
            }
			
            if (!$('#omise_phone_number').val()) {
                $form.block({
                    message: null,
                    overlayCSS: {
                        background: '#fff url(' + wc_checkout_params.ajax_loader_url + ') no-repeat center',
                        backgroundSize: '16px 16px',
                        opacity: 0.6
                    }
                });
                showError('Phone number is required in Atome');
                $form.unblock();
                return false;
            }

            $form.unblock();
            $form.submit();
		}
	}

	$(function () {
		$('form.checkout').on('checkout_place_order', function (e) {
			return omiseFormHandler();
		});
	})
})(jQuery)
