function showOmiseInstallmentForm({
  element,
  publicKey,
  onSuccess,
  onError,
  locale,
  amount,
}) {
  const noop = () => { }

  element.style.height = 500 + 'px'
  
  OmiseCard.configure({
    publicKey: publicKey,
    amount,
    element,
    iframeAppId: 'omise-checkout-installment-form',
    customCardForm: false,
    customInstallmentForm: true,
    locale: locale,
    defaultPaymentMethod: 'installment'
  });

  OmiseCard.open({
    onCreateSuccess: onSuccess ?? noop,
    onError: onError ?? noop
  });
}
