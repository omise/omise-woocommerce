import { render, screen } from '@testing-library/react';
import CreditCardPaymentMethod from './credit-card';
import omiseSettingFactory from '../../../../../tests/js/factories/omiseSettingFactory';

const ELEMENTS = {
  omiseCard: (container) => container.querySelector('#omise-card'),
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

  it('creates card token with billing address when onCheckoutValidation is triggered', () => {
    const settings = omiseSettingFactory.build();
    const billingAddress = {
      first_name: 'John',
      last_name: 'Doe',
      company: '',
      address_1: '123 Street',
      address_2: '',
      city: 'Bang Kapi',
      state: 'TH-10', // Bangkok
      postcode: '10240',
      country: 'TH',
      email: 'john@example.com',
      phone: '0891234567',
    };
    const getCartData = jest.fn().mockReturnValue({ billingAddress });
    const select = jest.fn().mockReturnValue({ getCartData });
    const originalOmiseCard = window.OmiseCard;
    const originalWp = window.wp;
    const getElementByIdSpy = jest.spyOn(document, 'getElementById');

    window.OmiseCard = { requestCardToken: jest.fn() };
    window.wp = {
      data: { select },
    };

    /**
     * Cannot find the element when rendering it,
     * Had to workaround by mocking the getElementById instead.
     */
    const mockOption = { innerText: 'Bangkok' };
    const mockSelectElement = { querySelector: jest.fn().mockReturnValue(mockOption) };
    getElementByIdSpy.mockImplementation((id) => (id === 'billing-state' ? mockSelectElement : null));

    render(
      <CreditCardPaymentMethod
        {...wcBlockProps}
        settings={settings}
      />
    );

    triggerCheckoutValidation();

    expect(select).toHaveBeenCalledWith('wc/store/cart');
    expect(getCartData).toHaveBeenCalled();
    expect(window.OmiseCard.requestCardToken).toHaveBeenCalledWith({
      email: 'john@example.com',
      billingAddress: {
        street1: '123 Street',
        street2: '',
        city: 'Bang Kapi',
        state: 'Bangkok',
        country: 'TH',
        postal_code: '10240',
        phone_number: '0891234567',
      }
    });

    window.OmiseCard = originalOmiseCard;
    window.wp = originalWp;
    getElementByIdSpy.mockRestore();
  });

  it('creates card token with billing address without state if state\'s name cannot be resolved', () => {
    const settings = omiseSettingFactory.build();
    const billingAddress = { state: 'TH-10' };
    const getCartData = jest.fn().mockReturnValue({ billingAddress });
    const select = jest.fn().mockReturnValue({ getCartData });
    const originalOmiseCard = window.OmiseCard;
    const originalWp = window.wp;

    window.OmiseCard = { requestCardToken: jest.fn() };
    window.wp = {
      data: { select },
    };

    render(
      <CreditCardPaymentMethod
        {...wcBlockProps}
        settings={settings}
      />
    );

    triggerCheckoutValidation();

    expect(select).toHaveBeenCalledWith('wc/store/cart');
    expect(getCartData).toHaveBeenCalled();
    expect(window.OmiseCard.requestCardToken).toHaveBeenCalledWith(expect.objectContaining(
      {
        billingAddress: expect.objectContaining({ state: undefined }),
      },
    ));

    window.OmiseCard = originalOmiseCard;
    window.wp = originalWp;
  });

  describe('Form submission', () => {
    let settings;
    let originalOmiseCard;
    let originalWp;

    beforeEach(() => {
      jest.useFakeTimers();
      settings = omiseSettingFactory.build();
      originalOmiseCard = window.OmiseCard;
      originalWp = window.wp;

      const select = jest.fn().mockReturnValue({
        getCartData: jest.fn().mockReturnValue({ billingAddress: {} })
      });

      window.OmiseCard = { requestCardToken: jest.fn() };
      window.wp = {
        data: { select },
      };
    });

    afterEach(() => {
      window.OmiseCard = originalOmiseCard;
      window.wp = originalWp;
      jest.useRealTimers();
    });

    it('clears the validation errors before submission', (done) => {
      render(
        <CreditCardPaymentMethod
          {...wcBlockProps}
          settings={settings}
        />
      );

      // Trigger errors when user fill in the form before checkout
      const mockCalls = window.showOmiseEmbeddedCardForm.mock.calls;
      const onErrorCallback = (mockCalls[0][0]).onError;
      onErrorCallback(['Please enter a valid card number']);

      triggerCheckoutValidation();
      triggerPaymentSetup(1000).then((response) => {
        // Submission should be successful even there are validation errors before.
        expect(response.type).toBe('success');
        expect(response.meta.paymentMethodData).toEqual({
          omise_save_customer_card: true,
          omise_token: { value: 'tokn_test_123456' },
          wc_block_payment: true,
        });
        done();
      }).catch((error) => {
        done(error);
      });

      const onSuccessCallback = (mockCalls[0][0]).onSuccess;
      onSuccessCallback({
        token: { value: 'tokn_test_123456' },
        remember: true
      });

      jest.advanceTimersByTime(1000);
    });

    it('emits success response when card token is created successfully', async () => {
      render(
        <CreditCardPaymentMethod
          {...wcBlockProps}
          settings={settings}
        />
      );

      triggerCheckoutValidation();

      const mockCalls = window.showOmiseEmbeddedCardForm.mock.calls;
      const onSuccessCallback = (mockCalls[0][0]).onSuccess;
      onSuccessCallback({
        token: { value: 'tokn_test_123456' },
        remember: false
      });

      const response = await triggerPaymentSetup();

      expect(response.type).toBe('success');
      expect(response.meta.paymentMethodData).toEqual({
        omise_save_customer_card: false,
        omise_token: { value: 'tokn_test_123456' },
        wc_block_payment: true,
      });
    });

    describe('when card form returns errors', () => {
      let onErrorCallback = null;

      beforeEach(() => {
         render(
          <CreditCardPaymentMethod
            {...wcBlockProps}
            settings={settings}
          />
        );

        triggerCheckoutValidation();

        const mockCalls = window.showOmiseEmbeddedCardForm.mock.calls;
        onErrorCallback = (mockCalls[0][0]).onError;
      });

      it('emits the correct error response when card form returns error array', async () => {
        onErrorCallback(['Please enter a valid card number']);

        await expect(triggerPaymentSetup()).resolves.toEqual({
          type: 'error',
          message: 'Please enter a valid card number',
        });
      });

      it('emits the correct error response when card form returns error string', async () => {
        onErrorCallback('Please enter a valid card number');

        await expect(triggerPaymentSetup()).resolves.toEqual({
          type: 'error',
          message: 'Please enter a valid card number',
        });
      });

      it('emits the correct error response when card form returns error in unexpected format', async () => {
        onErrorCallback({ success: false });

        await expect(triggerPaymentSetup()).resolves.toEqual({
          type: 'error',
          message: 'Something went wrong. Please review your card details and try again.',
        });
      });
    })
  })

  function triggerCheckoutValidation() {
    expect(wcBlockProps.eventRegistration.onCheckoutValidation).toHaveBeenCalledTimes(1);
    const validationCallback = wcBlockProps.eventRegistration.onCheckoutValidation.mock.calls[0][0];

    validationCallback();
  }

  function triggerPaymentSetup(advancedMs = 1000) {
    expect(wcBlockProps.eventRegistration.onPaymentSetup).toHaveBeenCalledTimes(1);
    const paymentSetupCallback = wcBlockProps.eventRegistration.onPaymentSetup.mock.calls[0][0];
    const responsePromise = paymentSetupCallback();

    jest.advanceTimersByTime(advancedMs);

    return responsePromise;
  }
});
