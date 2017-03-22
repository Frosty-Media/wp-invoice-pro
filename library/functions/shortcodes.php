<?php
/**
 * Shortcodes
 *
 * @package     WP Invoice Pro
 * @subpackage  Shortcodes
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the client/user display name
 *
 * @access      public
 * @return      string
 */
function wp_invoice_client_shortcode( $atts, $content = null ) {
	
	$user = wp_invoice_get_user( get_the_ID() );
	
	return $user->data->display_name;
}
add_shortcode( 'wp-invoice-client', 'wp_invoice_client_shortcode' );

/**
 * Get the Invoice type
 *
 * @access      public
 * @return      string
 */
function wp_invoice_type_shortcode( $atts, $content = null ) {
		
	$type = wp_invoice_get_type( get_the_ID() );
	
	return ucfirst( $type );
}
add_shortcode( 'wp-invoice-type', 'wp_invoice_type_shortcode' );

/**
 * Get the Invoice title
 *
 * @access      public
 * @return      string
 */
function wp_invoice_title_shortcode( $atts, $content = null ) {

	$title = preg_replace( array( '#Protected:#', '#Private:#' ), array( '', '' ), get_the_title() );
	
	return $title;
}
add_shortcode( 'wp-invoice-title', 'wp_invoice_title_shortcode' );

/**
 * Get the Invoice sender name
 *
 * @access      public
 * @return      string
 */
function wp_invoice_name_shortcode( $atts, $content = null ) {
	$settings = get_option( 'wp_invoice_settings', array() );
	$name = $settings['name'];
	
	return !empty( $name ) ? $name : '';
}
add_shortcode( 'wp-invoice-name', 'wp_invoice_name_shortcode' );

/**
 * Get the Invoice sender company
 *
 * @access      public
 * @return      string
 */
function wp_invoice_company_shortcode( $atts, $content = null ) {
	$settings = get_option( 'wp_invoice_settings', array() );
	$company = $settings['company_name'];
	
	return !empty( $company ) ? $company : '';
}
add_shortcode( 'wp-invoice-company', 'wp_invoice_company_shortcode' );
