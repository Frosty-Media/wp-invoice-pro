<?php

/**
 * Send Invoice as HTML Email
 *
 * @author Sawyer Hollenshead
 * @since 1.0.0
 *
 **/ 
global $post;


$from = wp_invoice_get_invoice_email();													// 1. Get From email
update_post_meta( $post->ID, 'invoice_sent', date_i18n( 'j/m/Y' ) );					// 2. Set sent custom field

$headers  = "From: " . $from . "\n";													// 3. Set Email Headers
$headers .= "Reply-To: " . $from . "\n";
$headers .= 'MIME-Version: 1.0' . "\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\n";

$to = wp_invoice_get_invoice_client_email();											// 4. Send email to ...
if ( wp_invoice_get_emailrecipients() == 'both' )
{
	$to .= ',' . $from;
}

$subject = wp_invoice_get_invoice_type() .
	' #' . wp_invoice_get_invoice_number() .
	' - ' . get_the_title();															// 5. Email Subject

if ( !$to )																				// 6. Quick validation check
{
	echo '<p class="error">' . __( 'Error: No recipient email address found', 'wp-invoice-pro' ) . '</p>';
	die;
}
if ( !$message )
{
	echo '<p class="error">' . __( 'Error: No message body found', 'wp-invoice-pro' ) . '</p>';	
	die;
}


/* Mail it!
-------------------------------------*/
if ( wp_mail( $to, $subject, $message, $headers ) ) 
{
	$edit_link = admin_url( 'post.php?post=' . $post->ID . '&action=edit&sent=success' );
	if ( $edit_link )
	{
		wp_redirect( $edit_link );
		exit;
	}
	else
	{
		echo '<p class="success">' . __( 'Email was successfully sent!', 'wp-invoice-pro' ) . '</p>';
	}
} 
else 
{
	// set sent custom field
	update_post_meta( $post->ID, 'invoice_sent', __( 'Not yet', 'wp-invoice-pro' ) );
	$edit_link = admin_url( 'post.php?post=' . $post->ID . '&action=edit&sent=fail' );
	if ( $edit_link )
	{
		wp_redirect( $edit_link );
		exit;
	}
	else
	{
		echo '<p class="error">' . __( 'Email failed to send', 'wp-invoice-pro' ) . '</p>';
	}
   
}