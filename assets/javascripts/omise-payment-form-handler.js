(function ($) {
  /* global omise_params, wc_checkout_params, OmiseCard, showOmiseEmbeddedCardForm, LOCALE */
  /* global Omise, HIDE_REMEMBER_CARD, CARD_FORM_THEME, FORM_DESIGN, CARD_BRAND_ICONS */

  const $form = $('form.checkout, form#order_review');

  function hideError() {
    $('.woocommerce-error').remove();
  }

  /**
   * @param {string} message Error message
   */
  function showError(message) {
    if (!message) {
      return;
    }

    $('.woocommerce-error, input.omise_token').remove();
    const $ulError = $('<ul>').addClass('woocommerce-error');

    if ($.isArray(message)) {
      $.each(message, function (i, v) {
        $ulError.append($('<li>' + v + '</li>'));
      });
    } else {
      $ulError.html('<li>' + message + '</li>');
    }

    $form.prepend($ulError);
    $('html, body').animate({ scrollTop: 0 }, 'slow');
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

    /**
     * @return {string} Value of the selected card
     */
    function getSelectedCardId() {
      const $selected_card_id = $("input[name='card_id']:checked");
      if ($selected_card_id.length > 0) {
        return $selected_card_id.val();
      }

      return '';
    }

    if ($('#payment_method_omise').is(':checked')) {
      if (!validSelection()) {
        showError(omise_params.no_card_selected);
        return false;
      }

      if (getSelectedCardId() !== '') {
        //submit the form right away if the card_id is not blank
        return true;
      }

      if (0 === $('input.omise_token').length) {
        // eslint-disable-next-line no-unused-expressions
        omise_params.secure_form_enabled
          ? requestCardToken()
          : traditionalForm();
        return false;
      }
    }
  }

  function traditionalForm() {
    $form.block({
      message: null,
      overlayCSS: {
        background:
          '#fff url(' +
          wc_checkout_params.ajax_loader_url +
          ') no-repeat center',
        backgroundSize: '16px 16px',
        opacity: 0.6,
      },
    });

    const errors = [],
      omise_card = {},
      omise_card_number_field = 'number',
      omise_card_fields = {
        name: $('#omise_card_name'),
        number: $('#omise_card_number'),
        expiration_month: $('#omise_card_expiration_month'),
        expiration_year: $('#omise_card_expiration_year'),
        security_code: $('#omise_card_security_code'),
      };

    $.each(omise_card_fields, function (index, field) {
      omise_card[index] =
        index === omise_card_number_field
          ? field.val().replace(/\s/g, '')
          : field.val();
      if ('' === omise_card[index]) {
        errors.push(omise_params['required_card_' + index]);
      }
    });

    if (errors.length > 0) {
      showError(errors);
      $form.unblock();
      return false;
    }

    hideError();

    if (Omise) {
      Omise.setPublicKey(omise_params.key);
      Omise.createToken('card', omise_card, function (statusCode, response) {
        if (parseInt(statusCode) === 200) {
          $.each(omise_card_fields, function (index, field) {
            field.val('');
          });

          $form.append(
            '<input type="hidden" class="omise_token" name="omise_token" value="' +
              response.id +
              '"/>'
          );
          $form.submit();
        } else {
          handleTokensApiError(response);
        }
      });
    } else {
      showError(
        omise_params.cannot_load_omisejs +
          '<br/>' +
          omise_params.check_internet_connection
      );
      $form.unblock();
    }
  }

  function googlePay() {
    window.addEventListener('loadpaymentdata', (event) => {
      document.getElementById('place_order').style.display = 'inline-block';
      const params = {
        method: 'googlepay',
        data: JSON.stringify(
          JSON.parse(event.detail.paymentMethodData.tokenizationData.token)
        ),
      };
      const billingAddress =
        event.detail.paymentMethodData.info?.billingAddress;
      if (billingAddress) {
        Object.assign(params, {
          billing_name: billingAddress.name,
          billing_city: billingAddress.locality,
          billing_country: billingAddress.countryCode,
          billing_postal_code: billingAddress.postalCode,
          billing_state: billingAddress.administrativeArea,
          billing_street1: billingAddress.address1,
          billing_street2: [billingAddress.address2, billingAddress.address3]
            .filter((s) => s)
            .join(' '),
          billing_phone_number: billingAddress.phoneNumber,
        });
      }

      hideError();

      Omise.setPublicKey(omise_params.key);
      Omise.createToken('tokenization', params, (statusCode, response) => {
        if (parseInt(statusCode) === 200) {
          document.getElementById('googlepay-button-container').style.display =
            'none';
          document.getElementById('googlepay-text').innerHTML =
            "Card is successfully selected. Please proceed to 'Place order'.";
          document
            .getElementById('googlepay-text')
            .classList.add('googlepay-selected');

          const form = document.querySelector('form.checkout');
          const input = document.createElement('input');
          input.setAttribute('type', 'hidden');
          input.setAttribute('class', 'omise_token');
          input.setAttribute('name', 'omise_token');
          input.setAttribute('value', response.id);
          form.appendChild(input);
        } else {
          handleTokensApiError(response);
        }
      });
    });
  }

  /**
   * @param {Object} response
   */
  function handleTokensApiError(response) {
    if (
      response.object &&
      'error' === response.object &&
      'invalid_card' === response.code
    ) {
      showError(
        omise_params.invalid_card +
          '<br/>' +
          mapApiResponseToTranslatedText(response.message)
      );
    } else if (response.message) {
      showError(
        omise_params.cannot_create_token +
          '<br/>' +
          mapApiResponseToTranslatedText(response.message)
      );
    } else if (response.responseJSON && response.responseJSON.message) {
      showError(
        omise_params.cannot_create_token +
          '<br/>' +
          mapApiResponseToTranslatedText(response.responseJSON.message)
      );
    } else if (response.status == 0) {
      showError(
        omise_params.cannot_create_token +
          '<br/>' +
          omise_params.cannot_connect_api +
          omise_params.retry_checkout
      );
    } else {
      showError(
        omise_params.cannot_create_token + '<br/>' + omise_params.retry_checkout
      );
    }
    $form.unblock();
  }

  /**
   * Return a translated localized text if found else return the same text.
   *
   * @param {string} message
   * @return {string} Translated message
   */
  function mapApiResponseToTranslatedText(message) {
    return omise_params[message] ? omise_params[message] : message;
  }

  /**
   *
   */
  function requestCardToken() {
    hideError();
    $form.block({
      message: null,
      overlayCSS: {
        background:
          '#fff url(' +
          wc_checkout_params.ajax_loader_url +
          ') no-repeat center',
        backgroundSize: '16px 16px',
        opacity: 0.6,
      },
    });

    const billingAddress = getBillingAddress();
    OmiseCard.requestCardToken(billingAddress);
  }

  /**
   * @return {Object} | {null} Billing address
   */
  function getBillingAddress() {
    const billingAddress = {};
    const billingAddressFields = [
      {
        key: 'country',
        field: 'billing_country',
      },
      {
        key: 'postal_code',
        field: 'billing_postcode',
      },
      {
        key: 'state',
        field: 'billing_state',
      },
      {
        key: 'city',
        field: 'billing_city',
      },
      {
        key: 'street1',
        field: 'billing_address_1',
      },
      {
        key: 'street2',
        field: 'billing_address_2',
      },
    ];

    for (const billing of billingAddressFields) {
      const billingField = document.getElementById(billing.field);

      // If the billing field is not present and the field
      // is billing address 2, skip to the next field
      if (!billingField && billing.field === 'billing_address_2') {
        continue;
      }

      // If any other field is not present or the value is empty,
      // return null to indicate billing address is not complete
      if (!billingField || billing.field === '') {
        return null;
      }

      // construct address object required for token
      billingAddress[billing.key] = billingField.value;
    }

    return billingAddress;
  }

  /**
   *
   * @param {Object} payload
   */
  function handleCreateOrder(payload) {
    $form.unblock();
    if (payload.token) {
      if (payload.remember) {
        $('.omise_save_customer_card').val(payload.remember);
      }
      $form.append(
        '<input type="hidden" class="omise_token" name="omise_token" value="' +
          payload.token +
          '"/>'
      );
      $form.submit();
    }
  }

  if (omise_params.secure_form_enabled) {
    $(document).on('updated_checkout', function () {
      showOmiseEmbeddedCardForm({
        element: document.getElementById('omise-card'),
        publicKey: omise_params.key,
        hideRememberCard: HIDE_REMEMBER_CARD,
        locale: LOCALE,
        theme: CARD_FORM_THEME ?? 'light',
        design: FORM_DESIGN,
        brandIcons: CARD_BRAND_ICONS,
        onSuccess: handleCreateOrder,
        onError: (error) => {
          showError(error);
          $form.unblock();
        },
      });
    });
  }

  $(function () {
    $('body').on('checkout_error', function () {
      $('.omise_token').remove();
    });

    $('form.checkout').unbind('checkout_place_order_omise');
    $('form.checkout').on('checkout_place_order_omise', function () {
      return omiseFormHandler();
    });

    /* Pay Page Form */
    $('form#order_review').on('submit', function () {
      return omiseFormHandler();
    });

    /* Both Forms */
    $('form.checkout, form#order_review').on(
      'change',
      '#omise_cc_form input',
      function () {
        $('.omise_token').remove();
      }
    );

    googlePay();
  });
})(jQuery);
