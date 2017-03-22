<?php

/**
 * Send Invoice as HTML Email
 *
 * @since 1.0
 */
class wp_invoice_email {
	
	var $settings;
	
	public function __construct( $parent, $message ) {
		global $post, $wp_invoice_detail;
		
		$this->message	= $message;
		$this->settings	= $parent->invoice->settings;
		
		$title_error	= __( 'WP Invoice &rsaquo; Error', 'wp-invoice' );
		$title_success	= __( 'WP Invoice &rsaquo; Success', 'wp-invoice' );
		
		//echo '<pre>' . print_r( $this, true ) . '</pre>'; exit;
		
		/**
		 * Mail headers
		 */
		$from = $this->settings['from_email'];																// 1. Get From email
		update_post_meta( $post->ID, $wp_invoice_detail->get_the_name('sent'), date_i18n('m/d/Y') );		// 2. Set sent custom field
		
		$headers[] 	= 'From: ' . $from;																		// 3. Set Email Headers
		$headers[] 	= 'Reply-To: ' . $from;
		
		$user		= wp_invoice_get_user( $post->ID );														// 4. Send email to ...
		$to			= $user->data->user_email;
		
		if ( 'both' == $this->settings['send_invoice'] ) {
			$to .= ',' . $from;
		}
		
		$subject = 	wp_invoice_get_type( $post->ID ) . ' # ' .
					wp_invoice_get_number( $post->ID ) . ' - ' .
					get_the_title();																		// 5. Email Subject
		
		if ( !$to ) {																						// 6. Quick validation check
			wp_die( '<p class="error">' . __( 'Error: No recipient email address found', 'wp-invoice' ) . '</p>', $title_error );
		}
		
		if ( !$this->message ) {
			wp_die( '<p class="error">' . __( 'Error: No message body found', 'wp-invoice' ) . '</p>', $title_error );
		}
		
		/**
		 * Send the Mail
		 */
		if ( wp_mail( $to, $subject, $message, $headers ) ) {
			$edit_link = add_query_arg( array( 'post' => $post->ID, 'action' => 'edit', 'sent' => 'success' ), admin_url( 'post.php' ) );
			
			if ( $edit_link ) {
				wp_redirect( $edit_link );
				update_post_meta( $post->ID, $wp_invoice_detail->get_the_name('sent'), date_i18n( get_option( 'date_format' ) ) );
				exit;
			} else {
				wp_die( '<p class="success">' . __( 'Email was successfully sent!', 'wp-invoice' ) . '</p>', $title_success, array( 'back_link' => true ) );
			}
		} else {
			// Set sent custom field
			update_post_meta( $post->ID, $wp_invoice_detail->get_the_name('sent'), esc_attr__( 'Not yet', 'wp-invoice' ) );
			
			$edit_link = add_query_arg( array( 'post' => $post->ID, 'action' => 'edit', 'sent' => 'fail' ), admin_url( 'post.php' ) );
			
			if ( $edit_link ) {
				wp_redirect( $edit_link ); exit;
			} else {
				wp_die( '<p class="error">' . __( 'Email failed to send', 'wp-invoice' ) . '</p>', $title_error );
			}		   
		} // wp_mail()
		
	}
	
};