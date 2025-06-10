import { render, screen } from '@testing-library/react';
import CreditCardPaymentMethod from './credit-card';
import omiseSettingFactory from '../../../../../tests/js/factories/omiseSettingFactory';

const ELEMENTS = {
  omiseCard: (container) => container.querySelector('#omise-card'),
  rememberCardInput: (container) =>
    container.querySelector('input[name=omise_save_customer_card]'),
  tokenInput: (container) =>
    container.querySelector('input[name=omise_token]'),
};

describe('Credit Card', () => {
  const wcBlockProps = {
    eventRegistration: {
      onPaymentSetup: jest.fn().mockReturnValue(jest.fn()),
      onCheckoutValidation: jest.fn().mockReturnValue(jest.fn()),
    },
    emitResponse: {
      responseTypes: {
        SUCCESS: 'success',
        FAIL: 'failure',
        ERROR: 'error',
      },
    },
  };

  const originalShowOmiseEmbeddedCardForm = window.showOmiseEmbeddedCardForm;

  beforeAll(() => {
    window.showOmiseEmbeddedCardForm = jest.fn();
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  afterAll(() => {
    window.showOmiseEmbeddedCardForm = originalShowOmiseEmbeddedCardForm;
  });

  it('renders the skeleton for displaying Omise credit card', () => {
    const { container } = render(
      <CreditCardPaymentMethod
        {...wcBlockProps}
        settings={omiseSettingFactory.build()}
      />
    );

    expect(ELEMENTS.omiseCard(container)).toBeVisible();
    expect(ELEMENTS.rememberCardInput(container)).toBeInTheDocument();
    expect(ELEMENTS.tokenInput(container)).toBeInTheDocument();
  });

  it('renders the description', () => {
    const description = 'This is a description';
    const settingsWithDescription = omiseSettingFactory.build({
      description,
    });

    render(
      <CreditCardPaymentMethod
        {...wcBlockProps}
        settings={settingsWithDescription}
      />
    );

    expect(screen.queryByText(description)).toBeVisible();
  });

  it('triggers the showOmiseEmbeddedCardForm with correct config', () => {
    const settings = omiseSettingFactory.build({
      publicKey: 'pkey_test_12345',
      locale: 'th',
      user_logged_in: false,
      card_form_theme: 'dark',
      card_brand_icons: ['visa', 'mastercard'],
      form_design: {
        font: { size: 20 },
      },
    });

    render(
      <CreditCardPaymentMethod
        {...wcBlockProps}
        settings={settings}
      />
    );

    expect(window.showOmiseEmbeddedCardForm).toHaveBeenCalledTimes(1);
    expect(window.showOmiseEmbeddedCardForm).toHaveBeenCalledWith(
      expect.objectContaining({
        publicKey: 'pkey_test_12345',
        locale: 'th',
        hideRememberCard: true,
        theme: 'dark',
        brandIcons: ['visa', 'mastercard'],
        design: {
          font: { size: 20 },
        },
      })
    );
  });
});
