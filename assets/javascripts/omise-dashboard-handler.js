(function ( $, undefined ) {
	var transferBoxWrapper = document.getElementById( 'Omise-TransferBoxWrapper' );
	var transferBox        = document.getElementById( 'Omise-TransferBox' );

	function showTransferPopup() {
		transferBoxWrapper.style.opacity    = "1";
		transferBoxWrapper.style.visibility = "visible";
		transferBox.style.webkitTransform   = "scale(1)";
		transferBox.style.MozTransform      = "scale(1)";
		transferBox.style.msTransform       = "scale(1)";
		transferBox.style.OTransform        = "scale(1)";
		transferBox.style.transform         = "scale(1)";
		transferBox.style.opacity           = "1";

		document.body.style.overflow        = "hidden";
		return
	}

	function hideTransferPopup() {
		transferBoxWrapper.style.opacity    = "0";
		transferBoxWrapper.style.visibility = "hidden";
		transferBox.style.webkitTransform   = "scale(.1)";
		transferBox.style.MozTransform      = "scale(.1)";
		transferBox.style.msTransform       = "scale(.1)";
		transferBox.style.OTransform        = "scale(.1)";
		transferBox.style.transform         = "scale(.1)";
		transferBox.style.opacity           = "0";

		document.body.style.overflow        = "";
		return
	}

	$( '#Omise-BalanceTransferTab' ).on( 'click', function() {
		showTransferPopup();
	});

	$( '#Omise-TransferCancelAction').on( 'click', function() {
		hideTransferPopup();
	});

	$( document ).on( 'click', '.Omise-Element.RadioBox:not(.SELECTED)', function() {
		var $this       = $( this );
		var radioButton = $this.find( 'input[type=radio]' );
			radioButton.prop( "checked", ! radioButton.prop( "checked" ) );

		if ( radioButton.attr( 'id' ) === "transfer_type_full" ) {
			$( "#omise_transfer_amount" ).removeAttr( 'required' );
			$( "#omise_transfer_amount" ).val( '' );
		} else {
			$( "#omise_transfer_amount" ).attr( 'required', 'required' );
		}

		var radioButtonDivs = $( '.Omise-Element.RadioBox' );
		jQuery.each( radioButtonDivs, function( k, v ) {
			var $this = $( v );
				$this.toggleClass( 'SELECTED' );
		});
	});

	$("#Omise-TransferForm").submit(function(e){
		e.preventDefault();

		amount_field = $( "#omise_transfer_amount" );
		if ( amount_field.prop('required') && amount_field.val() == "" ) {
			alert( "Please specify transfer amount !" );
		} else {
			this.submit();
		}
	});
})(jQuery);
