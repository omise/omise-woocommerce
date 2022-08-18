<fieldset id="omise-form-installment">
	<div class="fpx-select-bank">
		<label for="fpx-select-bank">Select Bank</label>			  
		<select class="fpx-bank-logo default" id="duitnow-obw-select-bank" name="source[bank]">
			<option value="" disabled selected>-- Select your option --</option>
			<?php foreach ($viewData['duitnow_obw_banklist'] as $bank) : ?>
				<option
					class="<?= $bank['code'] ?>" 
					value="<?= $bank['code'] ?>"
				>
					<?= $bank['name'] ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
</fieldset>

<script type="text/javascript">
	var selectElem = document.getElementById("duitnow-obw-select-bank");
	selectElem.addEventListener('change', function(e) {
	     selectElem.setAttribute("class", e.target.value);
    })
</script>
