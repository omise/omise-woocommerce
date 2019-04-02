<fieldset id="omise-form-installment">
	<ul class="omise-banks-list">
		<!-- BAY -->
		<li class="item">
			<input id="installment_bay" type="radio" name="source[type]" value="installment_bay" />
			<label for="installment_bay">
				<div class="bank-logo bay"></div>
				<div class="bank-label">
					<span class="title"><?php _e( 'Krungsri', 'omise' ); ?></span><br/>
					<select class="installment-term-select-box">
						<option>Select term</option>
					</select>
				</div>
			</label>
		</li>

		<!-- FIRST_CHOICE -->
		<li class="item">
			<input id="installment_first_choice" type="radio" name="source[type]" value="installment_first_choice" />
			<label for="installment_first_choice">
				<div class="bank-logo first_choice"></div>
				<div class="bank-label">
					<span class="title"><?php _e( 'Krungsri First Choice', 'omise' ); ?></span><br/>
					<select class="installment-term-select-box">
						<option>Select term</option>
					</select>
				</div>
			</label>
		</li>

		<!-- KBANK -->
		<li class="item">
			<input id="installment_kbank" type="radio" name="source[type]" value="installment_kbank" />
			<label for="installment_kbank">
				<div class="bank-logo kbank"></div>
				<div class="bank-label">
					<span class="title"><?php _e( 'Kasikorn', 'omise' ); ?></span><br/>
					<select class="installment-term-select-box">
						<option>Select term</option>
					</select>
				</div>
			</label>
		</li>

		<!-- KTC -->
		<li class="item">
			<input id="installment_ktc" type="radio" name="source[type]" value="installment_ktc" />
			<label for="installment_ktc">
				<div class="bank-logo ktc"></div>
				<div class="bank-label">
					<span class="title"><?php _e( 'Krungthai', 'omise' ); ?></span><br/>
					<select class="installment-term-select-box">
						<option>Select term</option>
					</select>
				</div>
			</label>
		</li>

		<!-- BBL -->
		<li class="item">
			<input id="installment_bbl" type="radio" name="source[type]" value="installment_bbl" />
			<label for="installment_bbl">
				<div class="bank-logo bbl"></div>
				<div class="bank-label">
					<span class="title"><?php _e( 'Bangkok', 'omise' ); ?></span><br/>
					<select class="installment-term-select-box">
						<option>Select term</option>
					</select>
				</div>
			</label>
		</li>
	</ul>
</fieldset>
