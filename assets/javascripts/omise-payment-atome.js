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

    function omiseHandleError(message) {
        $form.block({
            message: null,
            overlayCSS: {
                background: '#fff url(' + wc_checkout_params.ajax_loader_url + ') no-repeat center',
                backgroundSize: '16px 16px',
                opacity: 0.6
            }
        });
        showError(message);
        $form.unblock();
        return false;
    }

	function omiseFormHandler() {
        hideError();
		if ($('#payment_method_omise_atome').is(':checked')) {
            if ($("#omise_atome_phone_default").is(":checked")) {
                return true;
            }

            const phoneNumber = $('#omise_atome_phone_number').val();
			
            if (!phoneNumber) {
                return omiseHandleError('Phone number is required in Atome');
            }

            const phonePattern = /(\+)?([0-9]{10,13})/;

            if (!phonePattern.test(phoneNumber)) {
                return omiseHandleError('Phone number should be a number in Atome');
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
