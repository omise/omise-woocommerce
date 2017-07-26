<fieldset id="omise-form-internetbanking">
	<ul>
		<!-- SCB -->
		<li class="item">
			<input id="internet_banking_scb" type="radio" name="omise-offsite" value="internet_banking_scb" />
			<label for="internet_banking_scb">
				<div class="omise-form-internetbanking-logo-box scb">
					<img src="<?php echo plugins_url( '../../assets/images/scb.svg', __FILE__ ); ?>" />
				</div>
				<div class="omise-form-internetbanking-label-box">
					<span class="title"><?php _e( 'Siam Commercial Bank', 'omise' ); ?></span><br/>
					<span class="rate secondary-text"><?php _e( 'Fee: 15 THB (same zone), 30 THB (out zone)', 'omise' ); ?></span>
				</div>
			</label>
		</li>

		<!-- KTB -->
		<li class="item">
			<input id="internet_banking_ktb" type="radio" name="omise-offsite" value="internet_banking_ktb" />
			<label for="internet_banking_ktb">
				<div class="omise-form-internetbanking-logo-box ktb">
					<img src="<?php echo plugins_url( '../../assets/images/ktb.svg', __FILE__ ); ?>" />
				</div>
				<div class="omise-form-internetbanking-label-box">
					<span class="title"><?php _e( 'Krungthai Bank', 'omise' ); ?></span><br/>
					<span class="rate secondary-text"><?php _e( 'Fee: 15 THB (same zone), 15 THB (out zone)', 'omise' ); ?></span>
				</div>
			</label>
		</li>

		<!-- BAY -->
		<li class="item">
			<input id="internet_banking_bay" type="radio" name="omise-offsite" value="internet_banking_bay" />
			<label for="internet_banking_bay">
				<div class="omise-form-internetbanking-logo-box bay">
					<img src="<?php echo plugins_url( '../../assets/images/bay.svg', __FILE__ ); ?>" />
				</div>
				<div class="omise-form-internetbanking-label-box">
					<span class="title"><?php _e( 'Krungsri Bank', 'omise' ); ?></span><br/>
					<span class="rate secondary-text"><?php _e( 'Fee: 15 THB (same zone), 15 THB (out zone)', 'omise' ); ?></span>
				</div>
			</label>
		</li>

		<!-- BBL -->
		<li class="item">
			<input id="internet_banking_bbl" type="radio" name="omise-offsite" value="internet_banking_bbl" />
			<label for="internet_banking_bbl">
				<div class="omise-form-internetbanking-logo-box bbl">
					<img src="<?php echo plugins_url( '../../assets/images/bbl.svg', __FILE__ ); ?>" />
				</div>
				<div class="omise-form-internetbanking-label-box">
					<span class="title"><?php _e( 'Bangkok Bank', 'omise' ); ?></span><br/>
					<span class="rate secondary-text"><?php _e( 'Fee: 10 THB (same zone), 20 THB (out zone)', 'omise' ); ?></span>
				</div>
			</label>
		</li>
	</ul>
</fieldset>
