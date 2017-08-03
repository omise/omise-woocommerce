(function ($, undefined) {
  var checkout_form = document.getElementById('omise_cc_messenger_form')

  if (checkout_form) {
    checkout_form.addEventListener('submit', submitHandler, false)
    console.log('omise_cc_messenger_form : ready for payment')
  }

  var notification_content = $('#notification_content')

  function submitHandler (event) {
    event.preventDefault()

    var omise_card_name = $('#omise_card_name').val(),
      omise_card_number = $('#omise_card_number').val(),
      omise_card_expiration_month = $('#omise_card_expiration_month').val(),
      omise_card_expiration_year = $('#omise_card_expiration_year').val(),
      omise_card_security_code = $('#omise_card_security_code').val()

    var card = {
      'name': omise_card_name,
      'number': omise_card_number,
      'expiration_month': omise_card_expiration_month,
      'expiration_year': omise_card_expiration_year,
      'security_code': omise_card_security_code
    }

    var errors = OmiseUtil.validate_card(card)
    if (errors.length > 0) {
      showError('Card validation has error')
      console.log(errors)
    } else {
      hideError()
      if (Omise) {
        Omise.setPublicKey(omise_params.key)
        Omise.createToken('card', card, function (statusCode, response) {
          if (statusCode == 200) {
            checkout_form.omise_token.value = response.id
            $('#omise_card_name').val('')
            $('#omise_card_number').val('')
            $('#omise_card_expiration_month').val('')
            $('#omise_card_expiration_year').val('')
            $('#omise_card_security_code').val('')
            checkout_form.submit()
            $('#customer_email').val('')
          } else {
            var error_message = ''
            if (response.message) {
              error_message = 'Unable to process payment with Omise. ' + response.message
            } else if (response.responseJSON && response.responseJSON.message) {
              error_message = 'Unable to process payment with Omise. ' + response.responseJSON.message
            } else if (response.status == 0) {
              error_message = 'Unable to process payment with Omise. No response from Omise Api.'
            } else {
              error_message = 'Unable to process payment with Omise [ status=' + response.status + ' ]'
            }

            showError(error_message)
          };
        })
      } else {
        console.log('Something wrong with connection to Omise.js. Please check your network connection')
      }
    }
  }

  function showError (message) {
    console.log(message)
    if (!message) {
      return
    }

    $('.woocommerce-error, input.omise_token').remove()

    $ulError = $('<ul>').addClass('woocommerce-error')

    if ($.isArray(message)) {
      $.each(message, function (i, v) {
        $ulError.append($('<li>' + v + '</li>'))
      })
    } else {
      $ulError.html('<li>' + message + '</li>')
    }

    notification_content.prepend($ulError)
    $('html, body').animate({
      scrollTop: 0
    }, 'slow')
  }

  function hideError () {
    $('.woocommerce-error').remove()
  }
})(jQuery)
