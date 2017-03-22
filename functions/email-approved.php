<?php
/**
 * Send e-mail when the quote has been approved
 *
 * @author Sawyer Hollenshead
 * @since 1.0.0
 *
 **/
 
global $post, $authordata;

$sitename = strtolower( $_SERVER['SERVER_NAME'] );
if ( substr( $sitename, 0, 4 ) == 'www.' ) {
	$sitename = substr( $sitename, 4 );
}

$from = 'wordpress@' . $sitename;	

$headers  = "From: " . $from . "\n";				
$headers .= "Reply-To: ".$from."\n";
$headers .= 'MIME-Version: 1.0' . "\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\n";

$to = get_the_author_meta( 'user_email', $authordata->ID );													
$subject = wp_invoice_get_invoice_type() . ' #' . wp_invoice_get_invoice_number() . ' - ' . WP_INVOICE_PRO()->the_title( get_the_title(), true ) . __( ' - has been approved', 'wp-invoice-pro' );

if ( !$to || !$message )
{
	wp_redirect( get_permalink( $post->ID ) );
	exit;
}

/* Mail it!
-------------------------------------*/
if ( wp_mail( $to, $subject, $message, $headers ) ) 
{
	update_post_meta( $post->ID, 'approval_email_sent', 'true' );
	
	if ( is_user_logged_in() ) {
		wp_redirect( admin_url( 'post.php?post=' . $post->ID . '&action=edit&sent=success' ) );
		exit;
	}
	else {
		wp_redirect( add_query_arg( 'approved', 'true', get_permalink( $post->ID ) ) );
		exit;
	}
}