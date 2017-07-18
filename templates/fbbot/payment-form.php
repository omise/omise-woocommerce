<div>
    <form id="omise_cc_messenger_form" action="<?php echo get_site_url(); ?>/wp-json/omisemsgbot/v1/messenger_checkout" method="POST">
        <?php
            $product = Omise_Messenger_Bot_WCProduct::create( $product_id );
        ?>
        <label for="product_name">Product : <?php echo $product->name; ?></label>
        <label for="product_price">Price : <?php echo $product->price.' '.$product->currency; ?></label>

        <label for="omise_card_name"><?php echo Omise_Util::translate( 'Name', 'Card holder name at payment form' ); ?> <span class="required">*</span></label>
        <input id="omise_card_name" class="input-text" type="text" maxlength="255" autocomplete="off" placeholder="<?php echo Omise_Util::translate( 'Name', 'Placeholder for card holder name' ); ?>" name="omise_card_name">

        <label for="customer_email"><?php echo Omise_Util::translate( 'Email Address', 'Customer Email' ); ?> <span class="required">*</span></label>
        <input id="customer_email" class="input-text" type="text" maxlength="255" autocomplete="off" placeholder="Email" name="customer_email">

        <label for="omise_card_number"><?php echo Omise_Util::translate( 'Card Number', 'Card number at payment form'); ?> <span class="required">*</span></label>
        <input id="omise_card_number" class="input-text" type="text" maxlength="20" autocomplete="off" placeholder="<?php echo Omise_Util::translate( 'Card Number', 'Placeholder for card number'); ?>" name="omise_card_number">

        <label for="omise_card_expiration_month"><?php echo Omise_Util::translate( 'Expiration Month', 'Expiration month at payment form' ); ?> <span class="required">*</span></label>
        <input id="omise_card_expiration_month" class="input-text" type="text" autocomplete="off" placeholder="<?php echo Omise_Util::translate( 'MM', 'Placeholder for expiration month' ); ?>" name="omise_card_expiration_month">
        
        <label for="omise_card_expiration_year"><?php echo Omise_Util::translate( 'Expiration Year', 'Expiration year at payment form' ); ?> <span class="required">*</span></label>
        <input id="omise_card_expiration_year" class="input-text" type="text" autocomplete="off" placeholder="<?php echo Omise_Util::translate( 'YYYY', 'Placeholder for expiration year' ); ?>" name="omise_card_expiration_year">
        
        <label for="omise_card_security_code"><?php echo Omise_Util::translate( 'Security Code', 'Security Code at payment form'); ?> <span class="required">*</span></label>
        <input id="omise_card_security_code" class="input-text" type="password" autocomplete="off" placeholder="Security Code" name="omise_card_security_code">
        
        <input type="submit" value="Pay : <?php echo $product->price.' '.$product->currency; ?>">
        <input type="hidden" name="messenger_id" value=<?php echo $messenger_id; ?>></input>
        <input type="hidden" name="product_id" value=<?php echo $product_id; ?>></input>
        <input type="hidden" name="omise_token" />
    </form>
</div>