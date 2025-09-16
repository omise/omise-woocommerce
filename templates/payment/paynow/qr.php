<div class="omise omise-paynow-details">
	<div class="omise omise-paynow-logo"></div>
	<p><?php echo __( 'Scan the QR code to pay', 'omise' ); ?></p>
	<div class="omise omise-paynow-qrcode">
		<img src="<?php echo $viewData['qrcode']; ?>" alt="Omise QR code ID: <?php echo $viewData['qrcode_id']; ?>">
	</div>
	<div class="omise-paynow-payment-status">
		<div class="pending">
			<?php echo __( 'Payment session will time out in: <span id="timer"></span>', 'omise' ); ?>
		</div>
		<div class="completed" style="display:none">
			<div class="green-check"></div>
			<?php echo __( 'We\'ve received your payment.', 'omise' ); ?>
		</div>
		<div class="timeout" style="display:none">
			<?php echo __( 'Payment session timed out.', 'omise' ); ?>
		</div>
	</div>
</div>

<script type="text/javascript">
	var classPaymentPending = document.getElementsByClassName("pending");
	var classPaymentCompleted = document.getElementsByClassName("completed");
	var classPaymentTimeout = document.getElementsByClassName("timeout");
	var classQrImage = document.querySelector(".omise.omise-paynow-qrcode > img");

	const refreshPaymentStatus = function (intervalIterator) {
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.addEventListener("load", function () {
			if (this.status == 200) {
				var chargeState = JSON.parse(this.responseText);
				if (chargeState.status == "processing") {
					classQrImage.style.display = "none";
					classPaymentPending[0].style.display = "none";
					classPaymentCompleted[0].style.display = "block";
					clearInterval(intervalIterator);
				}
			} else if (this.status == 403) {
				clearInterval(intervalIterator);
			}
		});
		xmlhttp.open('GET', '<?php echo $viewData['get_order_status_url']; ?>', true);
		xmlhttp.send();
	};

	const refreshPaymentInterval = function (intervalTimeInSeconds) {
		const interval = setInterval(function () {
			console.log('Interval executed');
			refreshPaymentStatus(interval);

			if (timer == 0) {
				classPaymentPending[0].style.display = "none";
				classPaymentTimeout[0].style.display = "block";
				classQrImage.style.display = "none";
				clearInterval(interval);
			}
		}, intervalTimeInSeconds * 1000);

		return interval;
	};

	window.onload = function () {
		const display = document.querySelector('#timer');
		const refreshIntervalInSeconds = 10; // refresh every 10 seconds
		const maxIntervalTimeInSeconds = 10 * 60; // stop refreshing after 10 minutes

		const interval = refreshPaymentInterval(refreshIntervalInSeconds);
		setTimeout( () => clearInterval(interval), maxIntervalTimeInSeconds * 1000 );
	};
</script>
