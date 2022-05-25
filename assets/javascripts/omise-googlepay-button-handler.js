// const gpButton = document.createElement('google-pay-button')
//
// gpButton.paymentRequest = {
//     apiVersion: 2,
//     apiVersionMinor: 0,
//     allowedPaymentMethods: [
//         {
//             type: 'CARD',
//             parameters: {
//                 allowedAuthMethods: ['PAN_ONLY', 'CRYPTOGRAM_3DS'],
//                 allowedCardNetworks: ['MASTERCARD', 'VISA'],
//                 billingAddressRequired: true,
//             },
//             tokenizationSpecification: {
//                 type: 'PAYMENT_GATEWAY',
//                 parameters: {
//                     gateway: 'example',
//                     gatewayMerchantId: 'exampleGatewayMerchantId',
//                 },
//             },
//         },
//     ],
//     merchantInfo: {
//         merchantId: '12345678901234567890',
//         merchantName: 'Demo Merchant',
//     },
//     transactionInfo: {
//         totalPriceStatus: 'FINAL',
//         totalPriceLabel: 'Total',
//         totalPrice: '100.00',
//         currencyCode: 'USD',
//         countryCode: 'US',
//     },
// };
//
// gpButton.environment = 'TEST'
// gpButton.buttonType = 'short'
// gpButton.buttonColor = 'black'
//
// gpButton.addEventListener('loadpaymentdata', event => {
//     console.log('load payment data', event.detail);
// });
//
// console.log(gpButton)
//
// const gpDiv = document.getElementById('google-pay-button-container')
// gpDiv.appendChild(gpButton)
//
// console.log(gpDiv)

// gpButton.addEventListener('loadpaymentdata', event => {
//     console.log('load payment data', event.detail);
// });

console.log(params.key)
