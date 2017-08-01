<div>
    <form id="omise_cc_messenger_form" action="<?php echo get_site_url(); ?>/wp-json/omisemsgbot/v1/callback_fbbot_checkout" method="POST">
        <?php
            $product = Omise_FBBot_WCProduct::create( $product_id );
        ?>
        <div id="notification_content">
            <ul></ul>
        </div>
        <label for="product_name">Product : <?php echo $product->name; ?></label>
        <label for="product_price">Price : <?php echo $product->price.' '.$product->currency; ?></label>

        <label for="omise_card_name"><?php echo __( 'Name', 'Card holder name at payment form' ); ?> <span class="required">*</span></label>
        <input id="omise_card_name" class="input-text" type="text" maxlength="255" autocomplete="off" placeholder="<?php echo __( 'Name', 'Placeholder for card holder name' ); ?>" name="omise_card_name">

        <label for="customer_email"><?php echo __( 'Email Address', 'Customer Email' ); ?> <span class="required">*</span></label>
        <input id="customer_email" class="input-text" type="text" maxlength="255" autocomplete="off" placeholder="Email" name="customer_email">

        <label for="omise_card_number"><?php echo __( 'Card Number', 'Card number at payment form'); ?> <span class="required">*</span></label>
        <input id="omise_card_number" class="input-text" type="text" maxlength="20" autocomplete="off" placeholder="<?php echo __( 'Card Number', 'Placeholder for card number'); ?>" name="omise_card_number">

        <label for="omise_card_expiration_month"><?php echo __( 'Expiration Month', 'Expiration month at payment form' ); ?> <span class="required">*</span></label>
        <input id="omise_card_expiration_month" class="input-text" type="text" autocomplete="off" placeholder="<?php echo __( 'MM', 'Placeholder for expiration month' ); ?>" name="omise_card_expiration_month">
        
        <label for="omise_card_expiration_year"><?php echo __( 'Expiration Year', 'Expiration year at payment form' ); ?> <span class="required">*</span></label>
        <input id="omise_card_expiration_year" class="input-text" type="text" autocomplete="off" placeholder="<?php echo __( 'YYYY', 'Placeholder for expiration year' ); ?>" name="omise_card_expiration_year">
        
        <label for="omise_card_security_code"><?php echo __( 'Security Code', 'Security Code at payment form'); ?> <span class="required">*</span></label>
        <input id="omise_card_security_code" class="input-text" type="password" autocomplete="off" placeholder="Security Code" name="omise_card_security_code">
        
        <input type="submit" value="Pay : <?php echo $product->price.' '.$product->currency; ?>">
        <input type="hidden" name="messenger_id" value=<?php echo $messenger_id; ?>></input>
        <input type="hidden" name="product_id" value=<?php echo $product_id; ?>></input>
        <input type="hidden" name="omise_token"></input>
    </form>
</div>