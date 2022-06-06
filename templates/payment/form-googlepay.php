<?php if (!empty($viewData['config'])) : ?>
    <fieldset id="omise-form-googlepay">
        <div id="googlepay-button-container">
            <?php echo $viewData['config']['script'] ?>
        </div>

        <p id="googlepay-text" class="omise-secondary-text">
            <?php _e('You will be prompted to select a credit card stored in your Google Account.', 'omise'); ?>
        </p>
    </fieldset>
<?php else : ?>
    <p>
        <?php echo __('Google Pay is currently not available.', 'omise'); ?>
    </p>
<?php endif; ?>
