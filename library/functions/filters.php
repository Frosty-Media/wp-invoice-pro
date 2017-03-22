<?php
/**
 * Filters
 *
 * @package     WP Invoice Pro
 * @subpackage  apply_filters
*/

/**
 * Get the Company Title by settings
 *
 * @access      public
 * @return      string
 */
function wp_invoice_pro_filter_company_title( $input ) {
	$settings = get_option( 'wp_invoice_settings', array() );
	$company = $settings['company_name'];
	
	return !empty( $company ) ? $company : $input;
}
add_filter( 'wp_invoice_company_title', 'wp_invoice_pro_filter_company_title', 7 ); // Filter early to allow external overwrite

/**
 * Get the Company holder name by settings
 *
 * @access      public
 * @return      string
 */
function wp_invoice_pro_filter_company_subtitle( $input ) {	
	$settings = get_option( 'wp_invoice_settings', array() );
	$name = $settings['name'];
	
	return !empty( $name ) ? $name : $input;
}
add_filter( 'wp_invoice_company_subtitle', 'wp_invoice_pro_filter_company_subtitle', 7 ); // Filter early to allow external overwrite

/**
 * Get the Company email by settings
 *
 * @access      public
 * @return      string
 */
function wp_invoice_pro_filter_company_email( $input ) {	
	$settings = get_option( 'wp_invoice_settings', array() );
	$email = $settings['from_email'];
	
	return !empty( $email ) ? $email : $input;
}
add_filter( 'wp_invoice_company_email', 'wp_invoice_pro_filter_company_email', 7 ); // Filter early to allow external overwrite