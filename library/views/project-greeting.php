<div class="project-greeting">

	<p><?php _e( 'Enter your greeting text to be output before your email body.', 'wp-invoice' ); ?></p>
        
    <p class="greeting">
    <?php
    	$mb->the_field('greeting');
	
		$args = array(
			'media_buttons'	=> false,
			'textarea_rows'	=> 6,
			'tabindex'		=> 4,
			'tinymce'		=> false,
			'textarea_name' => $mb->get_the_name(),
		);
		
		$value		= $mb->get_the_value();
		$default	= apply_filters( 'wp_invoice_default_invoice_email_greeting',
						"Dear [wp-invoice-client],\n\nHere is your [wp-invoice-type] for [wp-invoice-title].\n\n[wp-invoice-name]\n\n[wp-invoice-company]",
						$post->ID ); 
		$content	= !empty( $value ) ? $value : $default;
	
		wp_editor( $content, $mb->get_the_name(), $args );
	?>
    </p>
    
	<p><?php printf( __( 'Available shortcodes to enter in the greeting text: <code>%s</code>, <code>%s</code>, <code>%s</code>, <code>%s</code>, <code>%s</code>.', 'wp-invoice' ),
					'[wp-invoice-client]',
					'[wp-invoice-type]',
					'[wp-invoice-title]',
					'[wp-invoice-name]',
					'[wp-invoice-company]'
					); ?></p>

</div>