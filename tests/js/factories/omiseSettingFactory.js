const build = (attrs) => ({
  user_logged_in: true,
  card_form_theme: "light",
  card_icons: [
    "jcb",
    "mastercard",
    "visa"
  ],
  form_design: {
    font: { name: "Poppins", size: 16 },
    input: { height: "44px" },
  },
  name: "omise",
  title: "Credit / Debit Card",
  description: "",
  features: [
    "products",
    "refunds"
  ],
  locale: "en_US",
  public_key: "pkey_test_12345",
  is_active: true,
  ...attrs,
});

export default { build };
