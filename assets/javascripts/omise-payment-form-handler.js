(function ($) {
	const $form = $('form.checkout, form#order_review');

	function hideError() {
		$(".woocommerce-error").remove();
	}

	function showError(message) {
		if (!message) {
			return;
		}

		$(".woocommerce-error, input.omise_token").remove();
		let $ulError = $("<ul>").addClass("woocommerce-error");

		if ($.isArray(message)) {
			$.each(message, function(i, v) {
				$ulError.append($("<li>" + v + "</li>"));
			})
		} else {
			$ulError.html("<li>" + message + "</li>");
		}

		$form.prepend($ulError);
		$("html, body").animate({ scrollTop:0 },"slow");
	}

	function omiseFormHandler() {
		function validSelection() {
			const $card_list = $("input[name='card_id']");
			const $selected_card_id = $("input[name='card_id']:checked");
			// there is some existing cards but nothing selected then warning
			if ($card_list.length > 0 && $selected_card_id.length === 0) {
				return false;
			}

			return true;
		}

		function getSelectedCardId() {
			const $selected_card_id = $("input[name='card_id']:checked");
			if ($selected_card_id.length > 0) {
				return $selected_card_id.val();
			}

			return "";
		}

		if ($('#payment_method_omise').is(':checked')) {
			if (!validSelection()) {
				showError(omise_params.no_card_selected);
				return false;
			}

			if (getSelectedCardId() !== "") {
				//submit the form right away if the card_id is not blank
				return true;
			}

			if (0 === $('input.omise_token').length) {
				requestCardToken();
				return false;
			}
		}
	}

	function omiseInstallmentFormHandler() {
		if (!$('#payment_method_omise_installment').is(':checked')) {
			return true;
		}

		if (0 === $('input.omise_token').length && 0 === $('input.omise_source').length) {
			requestCardToken();
			return false;
		}

		return true;
	}

	function googlePay() {
		window.addEventListener('loadpaymentdata', event => {
			document.getElementById('place_order').style.display = 'inline-block';
			const params = {
				method: 'googlepay',
				data: JSON.stringify(JSON.parse(event.detail.paymentMethodData.tokenizationData.token))
			}
			const billingAddress = (event.detail.paymentMethodData.info?.billingAddress);
			if (billingAddress) {
				Object.assign(params, {
					billing_name: billingAddress.name,
					billing_city: billingAddress.locality,
					billing_country: billingAddress.countryCode,
					billing_postal_code: billingAddress.postalCode,
					billing_state: billingAddress.administrativeArea,
					billing_street1: billingAddress.address1,
					billing_street2: [billingAddress.address2, billingAddress.address3].filter(s => s).join(' '),
					billing_phone_number: billingAddress.phoneNumber,
				});
			}

			hideError();

			Omise.setPublicKey(omise_params.key);
			Omise.createToken('tokenization', params, (statusCode, response) => {
				if (statusCode == 200) {
					document.getElementById('googlepay-button-container').style.display = 'none';
					document.getElementById('googlepay-text').innerHTML = 'Card is successfully selected. Please proceed to \'Place order\'.';
					document.getElementById('googlepay-text').classList.add('googlepay-selected');

					const form = document.querySelector('form.checkout');
					const input = document.createElement('input');
					input.setAttribute('type', 'hidden');
					input.setAttribute('class', 'omise_token');
					input.setAttribute('name', 'omise_token');
					input.setAttribute('value', response.id);
					form.appendChild(input);
				} else {
					handleTokensApiError(response)
				}
			});
		});
	}

	function handleTokensApiError(response) {
		if (response.object && 'error' === response.object && 'invalid_card' === response.code) {
			showError(omise_params.invalid_card + "<br/>" + mapApiResponseToTranslatedTest(response.message));
		} else if (response.message) {
			showError(omise_params.cannot_create_token + "<br/>" + mapApiResponseToTranslatedTest(response.message));
		} else if (response.responseJSON && response.responseJSON.message) {
			showError(omise_params.cannot_create_token + "<br/>" + mapApiResponseToTranslatedTest(response.responseJSON.message));
		} else if (response.status == 0) {
			showError(omise_params.cannot_create_token + "<br/>" + omise_params.cannot_connect_api + omise_params.retry_checkout);
		} else {
			showError(omise_params.cannot_create_token + "<br/>" + omise_params.retry_checkout);
		}
		$form.unblock();
	}

	/**
	 * Return a translated localized text if found else return the same text.
	 *
	 * @param {string} message
	 * @returns string
	 */
	function mapApiResponseToTranslatedTest(message)
	{
		return omise_params[message] ? omise_params[message] : message;
	}

	function requestCardToken() {
		hideError()
		$form.block({
			message: null,
			overlayCSS: {
				background: '#fff url(' + wc_checkout_params.ajax_loader_url + ') no-repeat center',
				backgroundSize: '16px 16px',
				opacity: 0.6
			}
		});

		const billingAddress = getBillingAddress();
		OmiseCard.requestCardToken(billingAddress);
	}

	/**
	 * @returns object | null
	 */
	function getBillingAddress() {
		const billingAddress = {};
		const billingAddressFields = {
			'country': 'billing_country',
			'postal_code': 'billing_postcode',
			'state': 'billing_state',
			'city': 'billing_city',
			'street1': 'billing_address_1',
			'street2': 'billing_address_2'
		};

		for (let key in billingAddressFields) {
			const billingField = document.getElementById(billingAddressFields[key]);

			// If the billing field is not present and the field
			// is billing address 2, skip to the next field
			if (!billingField && billingAddressFields[key] === 'billing_address_2') {
				continue;
			}

			// If any other field is not present or the value is empty,
			// return null to indicate billing address is not complete
			if (!billingField || billingField.value.trim() === "") {
				return null;
			}

			// construct address object required for token
			billingAddress[key] = billingField.value.trim();
		}

		return billingAddress;
	}

	function handleCreateOrder(payload) {
		$form.unblock();
		if (payload.token) {
			if (payload.remember) {
				$('.omise_save_customer_card').val(payload.remember)
			}
			$form.append('<input type="hidden" class="omise_token" name="omise_token" value="' + payload.token + '"/>');
			if (payload.source) {
				$form.append('<input type="hidden" class="omise_source" name="omise_source" value="' + payload.source + '"/>');
			}
			$form.submit();
		} else {
			if (payload.source) {
				$form.append('<input type="hidden" class="omise_source" name="omise_source" value="' + payload.source + '"/>');
			}
			$form.submit();
		}
	}

	function initializeSecureCardForm() {
		const omiseCardElement = document.getElementById('omise-card');
		if (omiseCardElement && $('#payment_method_omise').is(':checked')) {
			showOmiseEmbeddedCardForm({
				element: omiseCardElement,
				publicKey: omise_params.key,
				hideRememberCard: HIDE_REMEMBER_CARD,
				locale: LOCALE,
				theme: CARD_FORM_THEME ?? 'light',
				design: FORM_DESIGN,
				brandIcons: CARD_BRAND_ICONS,
				onSuccess: handleCreateOrder,
				onError: (error) => {
					showError(error)
					$form.unblock()
				}
			})
		} else {
			OmiseCard.destroy();
		}
	}

	function initializeInstallmentForm() {
		const omiseInstallmentElement = document.getElementById('omise-installment');
		if (omiseInstallmentElement && $('#payment_method_omise_installment').is(':checked')){
			showOmiseInstallmentForm({
				element: omiseInstallmentElement,
				publicKey: omise_installment_params.key,
				amount: OMISE_UPDATED_CART_AMOUNT,
				locale: LOCALE,
				onSuccess: handleCreateOrder,
				onError: (error) => {
					showError(error)
					$form.unblock()
				}
			})
		} else {
			OmiseCard.destroy();
		}
	}

	function setupOmiseForm() {
		var selectedPaymentMethod = $('input[name="payment_method"]:checked').val();
		if (selectedPaymentMethod === 'omise') {
			initializeSecureCardForm();
		} else if (selectedPaymentMethod === 'omise_installment') {
			initializeInstallmentForm();
		} else {
			OmiseCard.destroy();
		}
	}

	$(function () {
		$('body').on('checkout_error', function () {
			$('.omise_token').remove();
			$('.omise_source').remove();
		});

		$('form.checkout').unbind('checkout_place_order_omise');
		$('form.checkout').on('checkout_place_order_omise', function () {
			return omiseFormHandler();
		});
		$('form.checkout').unbind('checkout_place_order_omise_installment');
		$('form.checkout').on('checkout_place_order_omise_installment', function () {
			return omiseInstallmentFormHandler();
		});

		/* Pay Page Form */
		$('form#order_review').on('submit', function () {
			return omiseFormHandler();
		});

		/* Both Forms */
		$('form.checkout, form#order_review').on('change', '#omise_cc_form input', function() {
			$('.omise_token').remove();
			$('.omise_source').remove();
		});

		$('form.checkout').on('change', 'input[name="payment_method"]', function() {
			setupOmiseForm();
		});

		$(document).on('updated_checkout', function () {
			setupOmiseForm();
		});

		setupOmiseForm();
		googlePay();
	})
})(jQuery)
