<?php

/**
 * PayPal Plugin for WP Invoice Pro
 *
 * @class paypal
 * @version 1.0.0
 * @since 1.0.0
 */
 
class wp_invoice_paypal {
	
	var $user,
		$user_id,
		$user_name,
		$user_data;
	
	function __construct( $post_id = null ) {
		global $wp_invoice_breakdown, $wp_invoice_detail;
		
		if ( is_null( $post_id ) ) {
			$post_id = wp_invoice_get_post_id();
		}
		
		$this->user			= wp_invoice_get_user( $post_id ); //print_r ( $this->user->data );

		$this->user_name	= ( 0 != $this->user->ID ) ? $this->user->data->display_name : null;
		
		$this->user_name	= empty( $this->user_name ) ? $this->user->data->user_nicename : $this->user_name;
		
		$this->user_id		= ( 0 != $this->user->ID ) ? $this->user->data->ID : null;
		
		$this->user_data	= wp_invoice_get_user_meta( null, $this->user_id );
		
		?>        
        <style type="text/css">
			form.paypal { display: block; float: left; margin-left: 5px; margin-top: 3px; }
			form.paypal input[type="image"] { width: 86px; height: 21px; cursor: pointer; background: url('https://www.paypal.com/en_US/i/btn/btn_paynow_SM.gif') 0px 0px; display: block; overflow: hidden; white-space: nowrap; text-indent: 200px; }
		</style>
        
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="paypal">
            <input type="hidden" name="cmd" value="_xclick">
            
            <input type="hidden" name="business" value="<?php echo wp_invoice_get_payment_gateway_account(); ?>">
            
            <input type="hidden" name="item_name" value="<?php echo wp_invoice_get_type( $post_id ); ?> #<?php echo wp_invoice_get_number( $post_id ); ?> | <?php the_title(); ?>">
            
            <input type="hidden" name="amount" value="<?php echo wp_invoice_number_format( $wp_invoice_breakdown->get_the_value('subtotal') ); ?>">
            
            <!--<input type="hidden" name="tax_rate" value="<?php echo $wp_invoice_detail->get_the_value('invoice_tax'); ?>">-->
            
            <input type="hidden" name="tax" value="<?php echo wp_invoice_number_format( $wp_invoice_breakdown->get_the_value('tax') ); ?>">
            
            <input type="hidden" name="quantity" value="1">
            
            <input type="hidden" name="currency_code" value="<?php echo wp_invoice_get_currency_code(); ?>">
            
            <input type="hidden" name="first_name" value="<?php echo esc_attr( $this->user_name ); ?>">
            
            <input type="hidden" name="no_shipping" value="1">
            
            <input type="hidden" name="return" value="<?php echo add_query_arg( 'return', 'true', get_permalink( $post_id ) ); ?>">
            
            <input type="hidden" name="cancel_return" value="<?php echo add_query_arg( 'cancel', 'true', get_permalink( $post_id ) ); ?>">
            
            <input type="hidden" name="notify_url" value="<?php echo add_query_arg( 'paid', 'true', get_permalink( $post_id ) ); ?>">
            
            <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_paynow_SM.gif" border="0" name="submit">
        </form>
        
        <?php	
	}
	
};

?>