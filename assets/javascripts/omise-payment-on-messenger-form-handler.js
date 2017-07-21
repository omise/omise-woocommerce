(function ($, undefined) {
  console.log('omise_cc_messenger_form : ready for payment')

  var checkoutForm = document.getElementById('omise_cc_messenger_form')
  checkoutForm.addEventListener('submit', submitHandler, false)

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
      console.log('Card validation has error')
      console.log(errors)
    } else {
      console.log('validate card : success')
      if (Omise) {
        // Note : Set for testing on staging
        // window.setRemoteUrl = 'https://vault-staging.omise.co'

        Omise.setPublicKey(omise_params.key)
        Omise.createToken('card', card, function (statusCode, response) {
          if (statusCode == 200) {
            console.log(response)
            checkoutForm.omise_token.value = response.id
            $('#omise_card_name').val('')
            $('#omise_card_number').val('')
            $('#omise_card_expiration_month').val('')
            $('#omise_card_expiration_year').val('')
            $('#omise_card_security_code').val('')
            checkoutForm.submit()
          } else {
            if (response.message) {
              console.log('Unable to process payment with Omise. ' + response.message)
            } else if (response.responseJSON && response.responseJSON.message) {
              console.log('Unable to process payment with Omise. ' + response.responseJSON.message)
            } else if (response.status == 0) {
              console.log('Unable to process payment with Omise. No response from Omise Api.')
            } else {
              console.log('Unable to process payment with Omise [ status=' + response.status + ' ]')
            }
          };
        })
      } else {
        console.log('Something wrong with connection to Omise.js. Please check your network connection')
      }
    }
  }
})(jQuery)
