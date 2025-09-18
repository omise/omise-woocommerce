<?php
$is_qrcode_expired = $viewData['is_qrcode_expired'] === 'true';

function display_class( $visible ) {
	return $visible ? 'display:block' : 'display:none';
}
?>

<div class="omise omise-paynow-details">
	<div class="omise omise-paynow-logo"></div>
	<div class="omise omise-paynow-qrcode" style="<?php echo display_class( ! $is_qrcode_expired ); ?>">
		<p><?php echo __( 'Scan the QR code to pay', 'omise' ); ?></p>
		<img src="<?php echo $viewData['qrcode']; ?>" alt="Omise QR code ID: <?php echo $viewData['qrcode_id']; ?>">
	</div>
	<div class="omise-paynow-payment-status">
		<div class="pending" style="<?php echo display_class( ! $is_qrcode_expired ); ?>">
			<?php echo __( 'Payment session will time out in: <span id="timer"></span>', 'omise' ); ?>
		</div>
		<div class="completed" style="display:none">
			<div class="green-check"></div>
			<?php echo __( 'We\'ve received your payment.', 'omise' ); ?>
		</div>
		<div class="timeout" style="<?php echo display_class( $is_qrcode_expired ); ?>">
			<?php echo __( 'Payment session timed out.', 'omise' ); ?>
		</div>
	</div>
</div>

<script type="text/javascript">
	var classPaymentPending = document.getElementsByClassName("pending");
	var classPaymentCompleted = document.getElementsByClassName("completed");
	var classPaymentTimeout = document.getElementsByClassName("timeout");
	var classQrImage = document.querySelector(".omise.omise-paynow-qrcode");

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

	const displayTimeout = function () {
		classPaymentPending[0].style.display = "none";
		classPaymentTimeout[0].style.display = "block";
		classQrImage.style.display = "none";
	};

	const refreshPaymentInterval = function (intervalTimeInSeconds) {
		const interval = setInterval(function () {
			refreshPaymentStatus(interval);
		}, intervalTimeInSeconds * 1000);

		return interval;
	};

	window.onload = function () {
		const display = document.querySelector('#timer');
		const isExpired = '<?php echo $viewData['is_qrcode_expired']; ?>' === 'true';
		const refreshIntervalInSeconds = 10; // Refresh every 10 seconds
		const maxIntervalTimeInSeconds = 10 * 60; // Stop refreshing after 10 minutes

		if ( isExpired ) {
			displayTimeout();
			return;
		}

		const interval = refreshPaymentInterval(refreshIntervalInSeconds);
		setTimeout( () => clearInterval(interval), maxIntervalTimeInSeconds * 1000 );

		window.addEventListener('beforeunload', function() {
			clearInterval(interval);
		});
	};
</script>
