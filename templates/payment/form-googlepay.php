<?php if (!empty($viewData['config'])) : ?>
    <fieldset id="omise-form-googlepay">
        <?php _e('Google Pay', 'omise'); ?><br/>

        <p id="omise_billing_default_field" class="form-row form-row-wide omise-label-inline">
            <input id="omise_billing_default" type="checkbox" name="omise_billing_default" value="1" checked="checked"/>
            <label for="omise_billing_default"><?php _e('Same as Billing Detail', 'omise'); ?></label>
        </p>

        <div id="googlepay-button-container">
            <?php echo $viewData['config']['script'] ?>
        </div>

        <p class="omise-secondary-text">
            <?php _e('You will be prompted to select a credit card stored in your Google Account.', 'omise'); ?>
        </p>
    </fieldset>

    <script type="text/javascript">
        var billing_default = document.getElementById('omise_billing_default');

        billing_default.addEventListener('change', (e) => {
            billing_field.style.display = e.target.checked ? "none" : "block";
        });
    </script>
<?php else: ?>
    <p>
        <?php echo __('Google Pay is currently not available.', 'omise'); ?>
    </p>
<?php endif; ?>