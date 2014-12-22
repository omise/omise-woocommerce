var OmiseUtil = new function(){
	var _validate_card = function(card){
		var errors = [];
		if(!card.name || card.name==""){
			errors.push("Name is required");
		}
		
		if(!card.number || card.number==""){
			errors.push("Number is required");
		}
		
		if(!card.expiration_month || card.expiration_month==""){
			errors.push("Expiry month is required");
		}
		
		if(!card.expiration_year || card.expiration_year==""){
			errors.push("Expiry year is required");
		}
		
		if(!card.security_code || card.security_code==""){
			errors.push("Security code is required");
		}
		
		return errors;
	};
	
	return {
		validate_card: _validate_card
	}
}