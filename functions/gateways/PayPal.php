<?php

/**
 * PayPal Payment Gateway
 * @gateway PayPal
 *
 * @since 1.0.0
 * 
 **/
 
global $post, $payment_gateway_account;

$payment_gateway_fee = wp_invoice_get_option( 'payment_gateway_fee' ); 
$fee = !empty( $payment_gateway_fee ) && '0' !== $payment_gateway_fee ? true : false; ?>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="pay-form">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="business" value="<?php echo $payment_gateway_account; ?>">
    <input type="hidden" name="item_name" value="<?php _e( 'Invoice', 'wp-invoice-pro' ); ?> #<?php wp_invoice_number(); ?> | <?php the_title(); ?>">
    <input type="hidden" name="amount" value="<?php echo wp_invoice_get_the_invoice_subtotal( $fee ); ?>">
    <input type="hidden" name="tax" value="<?php echo wp_invoice_get_the_invoice_tax(); ?>">
    <input type="hidden" name="quantity" value="1">
    <input type="hidden" name="currency_code" value="<?php wp_invoice_currency_code(); ?>">
    <input type="hidden" name="first_name" value="<?php wp_invoice_client(); ?>">
    <input type="hidden" name="no_shipping" value="1">
    <input type="hidden" name="return" value="<?php the_permalink(); ?>">
    <input type="hidden" name="cancel_return" value="<?php the_permalink(); ?>">
    <input type="hidden" name="notify_url" value="<?php echo add_query_arg( 'diap', 'yes', get_permalink( $post->ID ) ); ?>">
    <input type="hidden" name="page_style" value="<?php wp_invoice_paypal_page_style(); ?>">
    <input type="submit" value="<?php _e( 'Pay Now', 'wp-invoice-pro' ); ?>" name="submit" class="pay">
</form>