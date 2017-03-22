<?php

/**
 * Stripe Payment Gateway
 * @gateway Stripe
 *
 * @since 2.3.0
 * 
 **/
 
global $post, $payment_gateway_account;

$payment_gateway_fee = wp_invoice_get_option( 'payment_gateway_fee' ); 
$fee = !empty( $payment_gateway_fee ) && '0' !== $payment_gateway_fee ? true : false;

$stripe = wp_invoice_pro_load_stripe();

if ( !$stripe ) {
	return false;
}

Stripe::setApiKey( $stripe['secret_key'] );

$amount = wp_invoice_get_the_invoice_subtotal( $fee ); ?>

<form action="" method="post" id="wp-invoice-pro-stripe">
	<script src="https://checkout.stripe.com/checkout.js"></script>
	<script>
	(function($) {
		$(document).ready(function() {
			$('<div class="stripe-response"/>').appendTo('header#head');
			var $input	= $('<input type="hidden" name="stripeToken">');
			var $form	= $('form#wp-invoice-pro-stripe');
			
			var handler = StripeCheckout.configure({
				token: function(token) {
					$input.val(token.id);
					
					$form.append( $input );
				//	console.log( 'form: ' + $form.serialize() );				
			
					$.ajax({
						type 		: 'post',
						dataType	: 'json',
						url 		: '<?php echo admin_url('admin-ajax.php'); ?>?nonce=<?php echo wp_create_nonce( WP_INVOICE_BASENAME ); ?>',
						data 		: $form.serialize(),
						success		: function(response) {
							$('.stripe-response').html( '<p>'+response.message+'</p>' ).show();
							console.log( 'response.type: ' + response.type );
							
							if ( response.type == 'success' ) {
								$('.stripe-response p').addClass('success');
							}
							else {
								$('.stripe-response p').addClass('error');
							}
							
							$('button.stripe-button-el').removeProp('disabled');
							$('button.stripe-button-el img').hide();
							setTimeout( function() {
								window.location.replace( response.redirect );
							}, 3000 );
						}
					
					}); // ajax
				}
			});
			
			//$('#wp-invoice-pro-stripe button.stripe-button-el').on('click', function(e) {
			document.getElementById('wp-invoice-pro-stripe-button-el').addEventListener('click', function(e) {
				var $this = $(this);
				$this.prop('disabled',true);
				$this.find('img').toggle();
				
				// Open Checkout with further options
				handler.open({
					key				: '<?php echo $stripe['publishable_key']; ?>',
					name			: '<?php echo wp_invoice_get_option( 'company' ); ?>',
					description	: '<?php _e( 'Invoice', 'wp-invoice-pro' ); ?> #<?php wp_invoice_number(); ?> | <?php the_title(); ?>',
					amount			: <?php echo ( $amount * 100 ); ?>,
					email			: '<?php wp_invoice_client_email(); ?>',
					panelLabel		: '<?php _e( 'Pay with Card', 'wp-invoice-pro' ); ?>',
					opened			: function() {
						setTimeout( function() {
							$this.find('img').hide('slow');
						}, 600 );
					},
					closed			: function() {
						$this.removeProp('disabled');
					},
				});
				e.preventDefault();
				return false;
			});
			
		});
	})(jQuery);
	</script>
	<button id="wp-invoice-pro-stripe-button-el" class="stripe-button-el">
		<span><img src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>"><span><?php _e( 'Pay with Card', 'wp-invoice-pro' ); ?></span></span>
	</button>
	<?php
	$terms = get_the_terms( $post->ID , 'client' );
	if ( $terms ) {	
		$terms = array_values( $terms );
		echo '<input type="hidden" name="term_id" value="' . absint( $terms[0]->term_id ) . '">';
	} ?>
	<input type="hidden" name="invoice_id" value="<?php echo base64_encode( $post->ID ); ?>">
	<input type="hidden" name="action" value="wp_invoice_process_payment">
	<input type="hidden" name="redirect" value="<?php echo get_permalink( $post->ID ); ?>">
	<input type="hidden" name="amount" value="<?php echo base64_encode( $amount ); ?>">
	<input type="hidden" name="stripe_nonce" value="<?php echo wp_create_nonce( 'stripe-nonce' ); ?>">
</form>