(() => {
    "use strict";

    const React = window.React;
    const { useEffect, useState } = window.wp.element;
    const { __ } = window.wp.i18n;
    const { decodeEntities } = window.wp.htmlEntities;
    const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
    const { getSetting } = window.wc.wcSettings;

    const settings = getSetting("omise_mobilebanking_data", {});
    const label = decodeEntities(settings.title) || "No title set";

    const Label = (props) => {
        const { PaymentMethodLabel } = props.components;
        return React.createElement(PaymentMethodLabel, { text: label });
    };

    const MobileBankingPaymentMethod = (props) => {
        const { eventRegistration, emitResponse } = props;
        const { onPaymentSetup } = eventRegistration;
        const description = decodeEntities(settings.description || "");
        const data = settings.data || {};
        const backends = data.backends || [];
        const isUpaEnabled = !!data.is_upa_enabled;
        const noPaymentMethods = __("There are no payment methods available.", "omise");
        const [selectedBank, setSelectedBank] = useState(null);

        const onMobileBankSelected = (event) => {
            setSelectedBank(event.target.value);
        };

        useEffect(() => {
            const unsubscribe = onPaymentSetup(async () => {
                if (isUpaEnabled) {
                    return {
                        type: emitResponse.responseTypes.SUCCESS,
                        meta: {
                            paymentMethodData: { "omise-offsite": "mobile_banking" },
                        },
                    };
                }

                if (!selectedBank) {
                    return {
                        type: emitResponse.responseTypes.ERROR,
                        message: __("Select a bank", "omise"),
                    };
                }

                try {
                    return {
                        type: emitResponse.responseTypes.SUCCESS,
                        meta: {
                            paymentMethodData: { "omise-offsite": selectedBank },
                        },
                    };
                } catch (error) {
                    return {
                        type: emitResponse.responseTypes.ERROR,
                        message: error.message,
                    };
                }
            });

            return () => unsubscribe();
        }, [
            emitResponse.responseTypes.ERROR,
            emitResponse.responseTypes.SUCCESS,
            onPaymentSetup,
            selectedBank,
            isUpaEnabled,
        ]);

        const backendSelection =
            !isUpaEnabled &&
            (backends.length === 0
                ? React.createElement("p", null, noPaymentMethods)
                : React.createElement(
                    "fieldset",
                    { key: "omise-form-mobilebanking" + backends.length, id: "omise-form-mobilebanking" },
                    React.createElement(
                        "ul",
                        { className: "omise-banks-list" },
                        backends.map((backend, i) =>
                            React.createElement(
                                "li",
                                { key: backend.name + i, className: "item mobile-banking" },
                                React.createElement(
                                    "div",
                                    null,
                                    React.createElement("input", {
                                        id: backend.name,
                                        type: "radio",
                                        name: "omise-offsite",
                                        value: backend.name,
                                        onChange: onMobileBankSelected,
                                    }),
                                    React.createElement(
                                        "label",
                                        { htmlFor: backend.name },
                                        React.createElement("div", {
                                            className: `mobile-banking-logo ${backend.provider_logo}`,
                                        }),
                                        React.createElement(
                                            "div",
                                            { className: "mobile-banking-label" },
                                            React.createElement("span", { className: "title" }, backend.provider_name),
                                            React.createElement("br", null)
                                        )
                                    )
                                )
                            )
                        )
                    )
                ));

        return React.createElement(
            React.Fragment,
            null,
            description && React.createElement("p", null, description),
            backendSelection
        );
    };

    registerPaymentMethod({
        name: settings.name || "",
        label: React.createElement(Label, null),
        content: React.createElement(MobileBankingPaymentMethod, null),
        edit: React.createElement(MobileBankingPaymentMethod, null),
        canMakePayment: () => settings.is_active,
        ariaLabel: label,
        supports: {
            features: settings.supports,
        },
    });
})();
