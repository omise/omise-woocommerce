(function ($) {
	const creditCardFormType = $('#woocommerce_omise_embedded_form_enabled');
	const cardFormTheme = $('#woocommerce_omise_card_form_theme');
	const cardFormThemeParent = cardFormTheme.closest("tr[valign='top']");

	Boolean(parseInt(creditCardFormType.val()))
		? cardFormThemeParent.show()
		: cardFormThemeParent.hide();

	// Add an event listener to the Product Type field
	creditCardFormType.on('change', function(e) {
		Boolean(parseInt($(this).val()))
			? cardFormThemeParent.show()
			: cardFormThemeParent.hide();
	});
})(jQuery);
