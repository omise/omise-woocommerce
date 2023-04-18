<?php if ($viewData['status']) : ?>
    <fieldset id="omise-form-atome">
        <?php _e('Atome phone number', 'omise'); ?><br />

        <p class="form-row form-row-wide omise-label-inline">
            <input id="omise_atome_phone_default" type="checkbox" name="omise_atome_phone_default" value="1" checked="checked" />
            <label for="omise_atome_phone_default"><?php _e('Same as Billing Detail', 'omise'); ?></label>
        </p>

        <p id="omise_atome_phone_field" class="form-row form-row-wide" style="display: none;">
            <span class="woocommerce-input-wrapper">
                <input id="omise_atome_phone_number" class="input-text" name="omise_atome_phone_number" type="tel" autocomplete="off" placeholder="+66123456789" />
            </span>
        </p>

        <p class="omise-secondary-text">
            <?php _e('The phone number will be used for creating Atome charge', 'omise'); ?>
        </p>
    </fieldset>

    <script type="text/javascript">
        document.getElementById('omise_atome_phone_default').addEventListener('change', (e) => {
            const phone_number_field_atome = document.getElementById('omise_atome_phone_field')
            phone_number_field_atome.style.display = e.target.checked ? "none" : "block";
        });
    </script>
<?php else : ?>
    <?php echo $viewData['message']; ?>
<?php endif; ?>
