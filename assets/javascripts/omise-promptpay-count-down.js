(function () {
    let countDownInterval;

    function calculateCountdown() {
        const currentDateTime = new Date();
        const expiresAtDateTime = new Date(omise.qr_expires_at);
        const difference = expiresAtDateTime - currentDateTime;
        const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((difference % (1000 * 60)) / 1000);
        return { hours, minutes, seconds };
    }

    function padZero(num) {
        return num.toString().padStart(2, '0');
    }

    function updateCountdown(fromInterval = true) {
        const countdownDisplay = document.getElementById('countdown');
        if(!countdownDisplay) {
            return;
        }

        const { hours, minutes, seconds } = calculateCountdown();

        if (hours + minutes + seconds < 0) {
            // To prevent infinite loading, we need to reload and clear interval 
            // only when it is from setInterval function.
            if (fromInterval) {
                clearInterval(countDownInterval)
                window.location.reload()
            }
            return;
        }

        countdownDisplay.innerHTML = `${padZero(hours)}:${padZero(minutes)}:${padZero(seconds)}`;
        if (!countDownInterval) {
            countDownInterval = setInterval(updateCountdown, 1000);
        }
    }

    updateCountdown(false)
})()
