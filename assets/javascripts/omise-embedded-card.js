function showOmiseEmbeddedCardForm({
  element,
  publicKey,
  theme,
  design,
  onSuccess,
  onError,
  locale,
  hideRememberCard,
  brandIcons
}) {
  const noop = () => { }
  const iframeHeightMatching = {
    '40px': 258,
    '44px': 270,
    '48px': 282,
    '52px': 295,
  }
  const { input, font, checkbox } = design;

  let iframeElementHeight = iframeHeightMatching[input.height]
  
  if (hideRememberCard) {
    iframeElementHeight = iframeElementHeight - 25
  }
  element.style.height = iframeElementHeight + 'px'

  let fontName = font.name
  const isCustomFontSet = font.name.toLowerCase().trim() === OMISE_CUSTOM_FONT_OTHER.toLowerCase()
  const isCustomFontEmpty = font.custom_name.trim() === ''

  if (isCustomFontSet && !isCustomFontEmpty) {
    fontName = font.custom_name.trim()
  }

  OmiseCard.configure({
    publicKey: publicKey,
    element,
    customCardForm: true,
    locale: locale,
    customCardFormTheme: theme,
    customCardFormHideRememberCard: hideRememberCard ?? false,
    customCardFormBrandIcons: brandIcons ?? null,
    style: {
      fontFamily: fontName,
      fontSize: font.size,
      input: {
        height: input.height,
        borderRadius: input.border_radius,
        border: `1.2px solid ${input.border_color}`,
        focusBorder: `1.2px solid ${input.active_border_color}`,
        background: input.background_color,
        color: input.text_color,
        labelColor: input.label_color,
        placeholderColor: input.placeholder_color,
      },
      checkBox: {
        textColor: checkbox.text_color,
        themeColor: checkbox.theme_color,
        border: `1.2px solid ${input.border_color}`,
      }
    },
  });

  OmiseCard.open({
    onCreateTokenSuccess: onSuccess ?? noop,
    onError: onError ?? noop
  });
}
