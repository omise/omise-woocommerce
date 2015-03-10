(function ( $, undefined ) {
	$("#omise_transfer_full_amount").change(function(){
		if($(this).is(":checked")){
			$("#omise_transfer_specific_amount").hide();
			$("#omise_transfer_amount").removeAttr('required');
			$("#omise_transfer_amount").val("");
		}else{
			$("#omise_transfer_specific_amount").show();
			$("#omise_transfer_amount").attr('required', 'required');
		}
	})
	
	$("#omise_create_transfer_form").submit(function(e){
		e.preventDefault();
		amount_field = $("#omise_transfer_amount");
		if(amount_field.prop('required') && amount_field.val()==""){
			alert("Please specify transfer amount !");
		}else{
			this.submit();
		}
	});
})(jQuery);