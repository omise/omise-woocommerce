<?php if (!empty($viewData['fpx_banklist'])) : ?>
	<fieldset id="omise-form-installment">
		<div class="select-list-box">
			<label for="fpx-select-bank">Select Bank</label>			  
			<select class="default" id="fpx-select-bank" name="source[bank]">
				<option value="" disabled selected>-- Select your option --</option>
				<?php foreach ($viewData['fpx_banklist'] as $bank) : ?>
					<option	
						class="<?php echo $bank["code"];?>" 
						value="<?php echo $bank["code"]; ?>"
						<?php if (!$bank['active']) echo disabled ; ?>
					>
							<?php echo $bank["name"]; ?> 
							<?php if (!$bank['active']) echo " (offline)" ; ?>
					</option>
			 	<?php endforeach; ?>
			</select>
		</div>
		<div class="terms-and-conditions-block">
			<span>By clicking on the <b>"Palce Order"</b> button, you agree to FPX's 
				<a href="https://www.mepsfpx.com.my/FPXMain/termsAndConditions.jsp" target="_blank">
					Terms and Conditions
				</a>
			</span>
		</div>
	</fieldset>
<?php else : ?>
	<p>
		<?php echo __('FPX is currently not available.', 'omise'); ?>
	</p>
<?php endif; ?>

<script type="text/javascript">
	var selectElem = document.getElementById("fpx-select-bank");
	selectElem.addEventListener('change', function(e) {
	     selectElem.setAttribute("class", e.target.value);
    })
</script>
