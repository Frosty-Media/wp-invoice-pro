<?php
	
$the_detail		= NULL;
$detailCount		= NULL;

$detailTitle 		= NULL;
$detailDescription	= NULL;
$detailType 		= NULL;
$detailRate 		= NULL;
$detailDuration 	= NULL;
$detailSubtotal 	= NULL;	
	
/*--------------------------------------------------------------------------------------------
									Invoice_has_details
									
* This function is called before the detail loop. 
* Populates the detail's data array's
* Checks that there are details
* Then it returns either true or false.
--------------------------------------------------------------------------------------------*/
function wp_invoice_has_details()
{
	global $post, $detailCount, $detailTitle, $detailDescription, $detailType, $detailRate, $detailDuration, $detailSubtotal;
	$detailCount=0;
	
	$detailTitle 		= get_post_meta( $post->ID, 'detail_title', true );
	$detailDescription = get_post_meta( $post->ID, 'detail_description', true );
	$detailType 		= get_post_meta( $post->ID, 'detail_type', true );
	$detailRate 		= get_post_meta( $post->ID, 'detail_rate', true );
	$detailDuration 	= get_post_meta( $post->ID, 'detail_duration', true );
	$detailSubtotal 	= get_post_meta( $post->ID, 'detail_subtotal', true );
	
	if ( !empty( $detailTitle[0] ) )
	{
		return true;	
	}
	else
	{
		return false;	
	}
}


/*--------------------------------------------------------------------------------------------
									 Invoice_detail
									
* This function is called at the start of the detail loop. 
* It sets up the detail data and returns either true or false.
--------------------------------------------------------------------------------------------*/
function wp_invoice_detail()
{
	global $the_detail, $detailCount, $detailTitle, $detailDescription, $detailType, $detailRate, $detailDuration, $detailSubtotal;
	if ( !empty( $detailTitle[$detailCount] ) && $detailTitle[$detailCount] != '' )
	{
		$the_detail = array(
			$detailTitle[$detailCount],
			$detailDescription[$detailCount],
			$detailType[$detailCount],
			$detailRate[$detailCount],
			$detailDuration[$detailCount],
			$detailSubtotal[$detailCount]
		);
		
		$detailCount++;
		return true;
	}
	else
	{
		return false;	
	}
}


/*--------------------------------------------------------------------------------------------
									 the_detail_title
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_the_detail_title()
{
	global $the_detail;
	return $the_detail[0];
}

function wp_invoice_the_detail_title()
{
	echo wp_invoice_get_the_detail_title();
}

/*--------------------------------------------------------------------------------------------
									the_detail_description
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_the_detail_description()
{
	global $the_detail;
	return $the_detail[1];
}
function wp_invoice_the_detail_description()
{
	echo nl2br( wp_invoice_get_the_detail_description() );
}


/*--------------------------------------------------------------------------------------------
									the_detail_type
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_the_detail_type()
{
	global $the_detail;
	return $the_detail[2];
}

function wp_invoice_the_detail_type()
{
	echo wp_invoice_get_the_detail_type();
}


/*--------------------------------------------------------------------------------------------
									the_detail_rate
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_the_detail_rate()
{
	global $the_detail;
	return $the_detail[3];
}
function wp_invoice_the_detail_rate()
{
	echo wp_invoice_format_amount( wp_invoice_get_the_detail_rate() );
}


/*--------------------------------------------------------------------------------------------
									the_detail_duration
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_the_detail_duration()
{
	global $the_detail;
	return $the_detail[4];
}
function wp_invoice_the_detail_duration()
{
	echo wp_invoice_get_the_detail_duration();
}


/*--------------------------------------------------------------------------------------------
									the_detail_subtotal
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_the_detail_subtotal()
{
	global $the_detail;
	return number_format( $the_detail[5], 2, '.', '' ); 
}
function wp_invoice_the_detail_subtotal()
{
	echo wp_invoice_format_amount( wp_invoice_get_the_detail_subtotal() );
}



/*--------------------------------------------------------------------------------------------
									invoice_template_url
--------------------------------------------------------------------------------------------*/
function get_invoice_template_url()
{
	if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'wp-invoice/invoice.php' ) )
	{
		return trailingslashit( get_stylesheet_directory_uri() ) . 'wp-invoice';
		
	}
	else
	{
		return trailingslashit( WP_INVOICE_URL ) . 'template';
	}
}

function invoice_template_url()
{
	echo get_invoice_template_url();
}

/*--------------------------------------------------------------------------------------------
									invoice_number		
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_invoice_number( $post_id = null )
{
	if ( is_null( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}
	
	return get_post_meta( $post->ID, 'invoice_number', true ) ? get_post_meta( $post_id, 'invoice_number', true ) : wp_invoice_get_next_invoice_number();	
}

function wp_invoice_number() 
{
	echo wp_invoice_get_invoice_number();
}

function wp_invoice_get_next_invoice_number()
{
	$newNumber = 0;
	$invoices  = get_posts( array( 'post_type' => 'invoice', 'numberposts' => '-1' ) );
	foreach( $invoices as $invoice )
	{
		$tempNumber = intval( get_post_meta( $invoice->ID, 'invoice_number', true ) );
		if ( $tempNumber > $newNumber) { $newNumber = $tempNumber; }
	}
	$newNumber += 1;
	
	return $newNumber;
}

/*--------------------------------------------------------------------------------------------
									wp_invoice_get_invoice_type
--------------------------------------------------------------------------------------------*/
function wp_invoice_type( $post_id = NULL )
{
	if ( !$post_id ) {
		global $post;
		$post_id = $post->ID;
	}
	echo wp_invoice_get_invoice_type( $post_id );
}

function wp_invoice_get_invoice_type( $post_id = NULL )
{
	if ( !$post_id ) {
		global $post;
		$post_id = $post->ID;
	}
	return get_post_meta( $post_id, 'invoice_type', true ) ? get_post_meta( $post_id, 'invoice_type', true ) : __( 'Invoice', 'wp-invoice-pro' );
}

/*--------------------------------------------------------------------------------------------
									wp_invoice_get_invoice_sent
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_invoice_sent()
{
	global $post;
	return get_post_meta( $post->ID, 'invoice_sent', true ) ? get_post_meta( $post->ID, 'invoice_sent', true ) : __( 'Not yet', 'wp-invoice-pro' );
}

function wp_invoice_get_invoice_sent_pretty()
{
	$sent	= wp_invoice_get_invoice_sent();
	$months = array( '', __( 'Jan', 'wp-invoice-pro' ), __( 'Feb', 'wp-invoice-pro' ), __( 'Mar', 'wp-invoice-pro' ), __( 'Apr', 'wp-invoice-pro' ), __( 'May', 'wp-invoice-pro' ), __( 'Jun', 'wp-invoice-pro' ), __( 'Jul', 'wp-invoice-pro' ), __( 'Aug', 'wp-invoice-pro' ), __( 'Sep', 'wp-invoice-pro' ), __( 'Oct', 'wp-invoice-pro' ), __( 'Nov', 'wp-invoice-pro' ), __( 'Dec', 'wp-invoice-pro' ) );
	if ( $sent == __( 'Not yet', 'wp-invoice-pro' ) )
	{
		return $sent;	
	}
	else
	{
		$sent = explode( '/',$sent );
		return $months[intval( $sent[1] )] . ' ' . $sent[0] . ', ' . $sent[2];
	}
}

function wp_invoice_sent()
{
	echo wp_invoice_get_invoice_sent();
}

function wp_invoice_has_sent( $post_id )
{
	$invoice_sent = get_post_meta( $post_id, 'invoice_sent', true ) ? get_post_meta( $post_id, 'invoice_sent', true ) : __( 'Not yet', 'wp-invoice-pro' );
	if ( $invoice_sent != __( 'Not yet', 'wp-invoice-pro' ) )
	{
		return true;	
	}
	else
	{
		return false;	
	}
}


/*--------------------------------------------------------------------------------------------
									wp_invoice_get_invoice_paid
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_invoice_paid()
{
	global $post;
	return get_post_meta( $post->ID, 'invoice_paid', true ) ? get_post_meta( $post->ID, 'invoice_paid', true ) : __( 'Not yet', 'wp-invoice-pro' );
}

function wp_invoice_get_invoice_paid_pretty()
{
	$sent = wp_invoice_get_invoice_paid();
	$months = array( '',__( 'Jan', 'wp-invoice-pro' ), __( 'Feb', 'wp-invoice-pro' ), __( 'Mar', 'wp-invoice-pro' ), __( 'Apr', 'wp-invoice-pro' ), __( 'May', 'wp-invoice-pro' ), __( 'Jun', 'wp-invoice-pro' ), __( 'Jul', 'wp-invoice-pro' ), __( 'Aug', 'wp-invoice-pro' ), __( 'Sep', 'wp-invoice-pro' ), __( 'Oct', 'wp-invoice-pro' ), __( 'Nov', 'wp-invoice-pro' ), __( 'Dec', 'wp-invoice-pro' ) );
	if ( $sent == __( 'Not yet', 'wp-invoice-pro' ) )
	{
		return $sent;	
	}
	else 
	{
		$sent = explode( '/', $sent );
		return $months[intval( $sent[1] )] . ' ' . $sent[0] . ', ' . $sent[2];
	}
}

function wp_invoice_has_paid( $post_id )
{
	$invoice_paid = get_post_meta( $post_id, 'invoice_paid', true ) ? get_post_meta( $post_id, 'invoice_paid', true ) : __( 'Not yet', 'wp-invoice-pro' );
	if ( $invoice_paid != __( 'Not yet', 'wp-invoice-pro' ) )
	{
		return true;	
	}
	else
	{
		return false;	
	}
}

/*--------------------------------------------------------------------------------------------
									get_invoice_status
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_invoice_status( $post_id = NULL ) 
{
	if ( !$post_id ) {
		global $post;
		$post_id = $post->ID;
	}
	$invoice_paid = get_post_meta( $post_id, 'invoice_paid', true );
	$invoice_sent = get_post_meta( $post_id, 'invoice_sent', true );
	if ( $invoice_paid && $invoice_paid != __( 'Not yet', 'wp-invoice-pro' ) )
	{
		return __( 'Paid', 'wp-invoice-pro' );
	}
	elseif ( $invoice_sent && $invoice_sent != __( 'Not yet', 'wp-invoice-pro' ) )
	{
		$invoice_sent = explode( '/', $invoice_sent );
		$invoice_sent = intval( $invoice_sent[2] ) . '-' . intval( $invoice_sent[1] ) . '-' . intval( $invoice_sent[0] );

		$days = wp_invoice_date_diff( $invoice_sent, date_i18n( 'Y-m-d' ) );
		if ( $days == 0 ) return __( 'Sent today', 'wp-invoice-pro' );
		elseif ( $days == 1) return __( 'Sent 1 day ago', 'wp-invoice-pro' );
		else return __( 'Sent ', 'wp-invoice-pro' ) . $days . __( ' days ago', 'wp-invoice-pro' );
	}
	else
	{
		return __( 'Not sent yet', 'wp-invoice-pro' );
	}
}
function wp_invoice_status() 
{
	echo wp_invoice_get_invoice_status();	
}

function wp_invoice_date_diff( $start, $end ) 
{
	$start_ts	= strtotime( $start );
	$end_ts		= strtotime( $end );
	$diff		= $end_ts - $start_ts;
	return round( $diff / 86400 );
}

/*--------------------------------------------------------------------------------------------
									get_invoice_approval
--------------------------------------------------------------------------------------------*/	
function wp_invoice_get_quote_approved( $post_id = NULL ) 
{
	if ( !$post_id ) {
		global $post;
		$post_id = $post->ID;
	}
	$quote_approved = get_post_meta( $post_id, 'quote_approved', true );
	if ( $quote_approved )
	{
		return $quote_approved;
	}
	else
	{
		return __( 'Not yet', 'wp-invoice-pro' );
	}
}
function wp_invoice_quote_approved() 
{
	$approved = wp_invoice_get_quote_approved();
	if ( $approved && $approved != __( 'Not yet', 'wp-invoice-pro' ) ) {
		echo $approved;
	} else {
		echo __( 'Not yet', 'wp-invoice-pro' );
	}
}

function wp_invoice_get_quote_approved_pretty()
{
	$approved 	= wp_invoice_get_quote_approved();
	$months 	= array( '',__( 'Jan', 'wp-invoice-pro' ), __( 'Feb', 'wp-invoice-pro' ), __( 'Mar', 'wp-invoice-pro' ), __( 'Apr', 'wp-invoice-pro' ), __( 'May', 'wp-invoice-pro' ), __( 'Jun', 'wp-invoice-pro' ), __( 'Jul', 'wp-invoice-pro' ), __( 'Aug', 'wp-invoice-pro' ), __( 'Sep', 'wp-invoice-pro' ), __( 'Oct', 'wp-invoice-pro' ), __( 'Nov', 'wp-invoice-pro' ), __( 'Dec', 'wp-invoice-pro' ) );
	if ( $approved && $approved == __( 'Not yet', 'wp-invoice-pro' ) )
	{
		return __( 'Not yet', 'wp-invoice-pro' );	
	}
	else 
	{
		$approved = explode( '/', $approved );
		return $months[intval( $approved[1] )] . ' ' . $approved[0] . ', ' . $approved[2];
	}
}

/*--------------------------------------------------------------------------------------------
										Currency		
--------------------------------------------------------------------------------------------*/
function wp_invoice_currency()
{
	echo wp_invoice_get_currency();
}

function wp_invoice_get_currency()
{
	$wp_invoice_currency = wp_invoice_get_option( 'currency' );	
	if ( $wp_invoice_currency )
	{
		return $wp_invoice_currency;
	}
	else
	{
		return 'US'; // USA is default	
	}
}

function wp_invoice_currency_code()
{
	echo wp_invoice_get_currency_code();
}

function wp_invoice_get_currency_code()
{
	$countries = wp_invoice_get_countries();
	return $countries[wp_invoice_get_currency()]['currency']['code'];
}

function wp_invoice_currency_format()
{
	echo wp_invoice_get_currency_format();
}

function wp_invoice_get_currency_format()
{
	$countries = wp_invoice_get_countries();
	return $countries[wp_invoice_get_currency()]['currency']['format'];
}

function wp_invoice_format_amount( $amount )
{
	return str_replace( '#', $amount, wp_invoice_get_currency_format() );
}

function wp_invoice_get_countries() 
{
	$countries = array();
	$countries['CA'] = array('name'=>'Canada','currency'=>array('code'=>'CAD','format'=>'$#' ) ); 
	$countries['US'] = array('name'=>'USA','currency'=>array('code'=>'USD','format'=>'$#' ) ); 
	$countries['GB'] = array('name'=>'United Kingdom','currency'=>array('code'=>'GBP','format'=>'£#' ) ); 
	$countries['DZ'] = array('name'=>'Algeria','currency'=>array('code'=>'DZD','format'=>'# د.ج' ) ); 
	$countries['AR'] = array('name'=>'Argentina','currency'=>array('code'=>'ARS','format'=>'$#' ) );
	$countries['AW'] = array('name'=>'Aruba','currency'=>array('code'=>'AWG','format'=>'ƒ#' ) );
	$countries['AU'] = array('name'=>'Australia','currency'=>array('code'=>'AUD','format'=>'$#' ) );
	$countries['AT'] = array('name'=>'Austria','currency'=>array('code'=>'EUR','format'=>'€#' ) );
	$countries['BB'] = array('name'=>'Barbados','currency'=>array('code'=>'BBD','format'=>'$#' ) );
	$countries['BS'] = array('name'=>'Bahamas','currency'=>array('code'=>'BSD','format'=>'$#' ) );
	$countries['BH'] = array('name'=>'Bahrain','currency'=>array('code'=>'BHD','format'=>'ب.د #' ) );
	$countries['BE'] = array('name'=>'Belgium','currency'=>array('code'=>'EUR','format'=>'# €' ) );
	$countries['BR'] = array('name'=>'Brazil','currency'=>array('code'=>'BRL','format'=>'R$#' ) );
	$countries['BG'] = array('name'=>'Bulgaria','currency'=>array('code'=>'BGN','format'=>'# лв.' ) );
	$countries['CL'] = array('name'=>'Chile','currency'=>array('code'=>'CLP','format'=>'$#' ) );
	$countries['CN'] = array('name'=>'China','currency'=>array('code'=>'CNY','format'=>'¥#' ) );
	$countries['CO'] = array('name'=>'Colombia','currency'=>array('code'=>'COP','format'=>'$#' ) );
	$countries['CR'] = array('name'=>'Costa Rica','currency'=>array('code'=>'CRC','format'=>'₡#' ) );
	$countries['HR'] = array('name'=>'Croatia','currency'=>array('code'=>'HRK','format'=>'# kn' ) );
	$countries['CY'] = array('name'=>'Cyprus','currency'=>array('code'=>'CYP','format'=>'£#' ) );
	$countries['CZ'] = array('name'=>'Czech Republic','currency'=>array('code'=>'CZK','format'=>'# Kč' ) );
	$countries['DK'] = array('name'=>'Denmark','currency'=>array('code'=>'DKK','format'=>'# kr' ) ); 
	$countries['DO'] = array('name'=>'Dominican Republic','currency'=>array('code'=>'DOP','format'=>'$#' ) ); 
	$countries['EC'] = array('name'=>'Ecuador','currency'=>array('code'=>'ESC','format'=>'$#' ) ); 
	$countries['EG'] = array('name'=>'Egypt','currency'=>array('code'=>'EGP','format'=>'£#' ) );
	$countries['EE'] = array('name'=>'Estonia','currency'=>array('code'=>'EEK','format'=>'# EEK' ) );
	$countries['FI'] = array('name'=>'Finland','currency'=>array('code'=>'EUR','format'=>'€#' ) );
	$countries['FR'] = array('name'=>'France','currency'=>array('code'=>'EUR','format'=>'€#' ) );
	$countries['DE'] = array('name'=>'Germany','currency'=>array('code'=>'EUR','format'=>'€#' ) ); 
	$countries['GR'] = array('name'=>'Greece','currency'=>array('code'=>'EUR','format'=>'€#' ) ); 
	$countries['GP'] = array('name'=>'Guadeloupe','currency'=>array('code'=>'EUR','format'=>'€#' ) ); 
	$countries['GT'] = array('name'=>'Guatemala','currency'=>array('code'=>'GTQ','format'=>'Q#' ) ); 
	$countries['HK'] = array('name'=>'Hong Kong','currency'=>array('code'=>'HKD','format'=>'$#' ) ); 
	$countries['HU'] = array('name'=>'Hungary','currency'=>array('code'=>'HUF','format'=>'# Ft' ) ); 
	$countries['IS'] = array('name'=>'Iceland','currency'=>array('code'=>'ISK','format'=>'# kr.' ) ); 
	$countries['IN'] = array('name'=>'India','currency'=>array('code'=>'INR','format'=>'₨#' ) ); 
	$countries['ID'] = array('name'=>'Indonesia','currency'=>array('code'=>'IDR','format'=>'Rp #' ) ); 
	$countries['IE'] = array('name'=>'Ireland','currency'=>array('code'=>'EUR','format'=>'€#' ) ); 
	$countries['IL'] = array('name'=>'Israel','currency'=>array('code'=>'ILS','format'=>'₪ #' ) ); 
	$countries['IT'] = array('name'=>'Italy','currency'=>array('code'=>'EUR','format'=>'€#' ) ); 
	$countries['JM'] = array('name'=>'Jamaica','currency'=>array('code'=>'JMD','format'=>'$#' ) ); 
	$countries['JP'] = array('name'=>'Japan','currency'=>array('code'=>'JPY','format'=>'¥#' ) ); 
	$countries['LV'] = array('name'=>'Latvia','currency'=>array('code'=>'LVL','format'=>'# Ls' ) ); 
	$countries['LT'] = array('name'=>'Lithuania','currency'=>array('code'=>'LTL','format'=>'# Lt' ) ); 
	$countries['LU'] = array('name'=>'Luxembourg','currency'=>array('code'=>'EUR','format'=>'€#' ) ); 
	$countries['MY'] = array('name'=>'Malaysia','currency'=>array('code'=>'MYR','format'=>'RM#' ) ); 
	$countries['MT'] = array('name'=>'Malta','currency'=>array('code'=>'MTL','format'=>'€#' ) ); 
	$countries['MX'] = array('name'=>'Mexico','currency'=>array('code'=>'MXN','format'=>'$#' ) ); 
	$countries['NL'] = array('name'=>'Netherlands','currency'=>array('code'=>'EUR','format'=>'€#' ) ); 
	$countries['NZ'] = array('name'=>'New Zealand','currency'=>array('code'=>'NZD','format'=>'$#' ) ); 
	$countries['NG'] = array('name'=>'Nigeria','currency'=>array('code'=>'NGN','format'=>'₦#' ) );
	$countries['NO'] = array('name'=>'Norway','currency'=>array('code'=>'NOK','format'=>'kr #' ) ); 
	$countries['PK'] = array('name'=>'Pakistan','currency'=>array('code'=>'PKR','format'=>'₨#' ) ); 
	$countries['PE'] = array('name'=>'Peru','currency'=>array('code'=>'PEN','format'=>'S/. #' ) ); 
	$countries['PH'] = array('name'=>'Philippines','currency'=>array('code'=>'PHP','format'=>'Php #' ) ); 
	$countries['PL'] = array('name'=>'Poland','currency'=>array('code'=>'PLZ','format'=>'# zł' ) ); 
	$countries['PT'] = array('name'=>'Portugal','currency'=>array('code'=>'EUR','format'=>'€#' ) ); 
	$countries['PR'] = array('name'=>'Puerto Rico','currency'=>array('code'=>'USD','format'=>'$#' ) ); 
	$countries['RO'] = array('name'=>'Romania','currency'=>array('code'=>'ROL','format'=>'# lei' ) );
	$countries['RU'] = array('name'=>'Russia','currency'=>array('code'=>'RUB','format'=>'# руб' ) ); 
	$countries['SG'] = array('name'=>'Singapore','currency'=>array('code'=>'SGD','format'=>'$#' ) ); 
	$countries['SK'] = array('name'=>'Slovakia','currency'=>array('code'=>'EUR','format'=>'€#' ) ); 
	$countries['SI'] = array('name'=>'Slovenia','currency'=>array('code'=>'EUR','format'=>'€#' ) ); 
	$countries['ZA'] = array('name'=>'South Africa','currency'=>array('code'=>'ZAR','format'=>'R#' ) ); 
	$countries['KR'] = array('name'=>'South Korea','currency'=>array('code'=>'KRW','format'=>'₩#' ) ); 
	$countries['ES'] = array('name'=>'Spain','currency'=>array('code'=>'EUR','format'=>'€#' ) ); 
	$countries['VC'] = array('name'=>'St. Vincent','currency'=>array('code'=>'XCD','format'=>'$#' ) ); 
	$countries['SE'] = array('name'=>'Sweden','currency'=>array('code'=>'SEK','format'=>'# kr' ) ); 
	$countries['CH'] = array('name'=>'Switzerland','currency'=>array('code'=>'CHF','format'=>"# CHF") ); 
	$countries['TW'] = array('name'=>'Taiwan','currency'=>array('code'=>'TWD','format'=>'NT$#' ) ); 
	$countries['TH'] = array('name'=>'Thailand','currency'=>array('code'=>'THB','format'=>'#฿' ) ); 
	$countries['TT'] = array('name'=>'Trinidad and Tobago','currency'=>array('code'=>'TTD','format'=>'TT$#' ) ); 
	$countries['TR'] = array('name'=>'Turkey','currency'=>array('code'=>'TRL','format'=>'# TL' ) ); 
	$countries['UA'] = array('name'=>'Ukraine','currency'=>array('code'=>'UAH','format'=>'# ₴' ) ); 
	$countries['AE'] = array('name'=>'United Arab Emirates','currency'=>array('code'=>'AED','format'=>'Dhs. #' ) ); 
	$countries['UY'] = array('name'=>'Uruguay','currency'=>array('code'=>'UYP','format'=>'$#' ) ); 
	$countries['VE'] = array('name'=>'Venezuela','currency'=>array('code'=>'VUB','format'=>'Bs. #' ) ); 
	
	return apply_filters( 'wp_invoice_countries', $countries );
}


/*--------------------------------------------------------------------------------------------
											Tax	
--------------------------------------------------------------------------------------------*/
function wp_invoice_tax()
{
	global $post;
	$post_id = empty( $post ) ? '' : $post->ID;
	echo wp_invoice_get_wp_invoice_tax( $post_id );
}

function wp_invoice_get_wp_invoice_tax( $invoice_id = NULL )
{
	if ( get_post_meta( $invoice_id, 'invoice_tax', true ) )
	{
		return get_post_meta( $invoice_id, 'invoice_tax', true );
	}
	elseif ( wp_invoice_get_option( 'tax' ) )
	{
		return wp_invoice_get_option( 'tax' );
	}
	else
	{
		return '0.00';	
	}
}

function wp_invoice_has_tax()
{
	global $post;
	if ( wp_invoice_get_wp_invoice_tax( $post->ID ) == '0.00' )
	{
		return false;	
	}
	else
	{
		return true;	
	}
}

/*--------------------------------------------------------------------------------------------
										Email Recipients
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_emailrecipients()
{
	$wp_invoice_emailrecipients = wp_invoice_get_option( 'emailrecipients' );	
	if ( $wp_invoice_emailrecipients )
	{
		return $wp_invoice_emailrecipients;
	}
	else
	{
		return 'client';	
	}
}


/*--------------------------------------------------------------------------------------------
										Invoice Permalinks
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_permalink()
{
	$wp_invoice_permalink = wp_invoice_get_option( 'permalink' );	
	if ( $wp_invoice_permalink )
	{
		return $wp_invoice_permalink;
	}
	else
	{
		return 'encoded';	
	}
}


/*--------------------------------------------------------------------------------------------
									Invoice Content Editor
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_content_editor()
{
	$wp_invoice_content_editor = wp_invoice_get_option( 'content_editor' );	
	if ( $wp_invoice_content_editor )
	{
		return $wp_invoice_content_editor;
	}
	else
	{
		return 'enabled';	
	}
}


/*--------------------------------------------------------------------------------------------
									Invoice Email
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_invoice_email()
{
	$wp_invoice_email 	= wp_invoice_get_option( 'email' );	
	$current_user 		= wp_get_current_user();
	if ( $wp_invoice_email )
	{
		return $wp_invoice_email;
	}
	elseif ( $current_user )
	{
		return $current_user->user_email;
	}
	else
	{
		return '';	
	}
}

function wp_invoice_email()
{
	echo wp_invoice_get_invoice_email();
}

/*--------------------------------------------------------------------------------------------
									Payment Gateways
--------------------------------------------------------------------------------------------*/

/**
 * wp_invoice_payment_gateway
 *
 * @since 1.0.0
 *
 * Returns an array of files found in the gateway folder
 **/
function wp_invoice_get_payment_gateway()
{
	$wp_invoice_payment_gateway = wp_invoice_get_option( 'payment_gateway' );	
	if ( $wp_invoice_payment_gateway )
	{
		return $wp_invoice_payment_gateway;
	}
	else
	{
		return 'None';	
	}
}

function wp_invoice_payment_gateway()
{
	echo wp_invoice_get_payment_gateway();
}
	
/**
 * wp_invoice_payment_gateway_account
 *
 * @since 1.0.0
 *
 **/
function wp_invoice_get_payment_gateway_account()
{
	$wp_invoice_payment_gateway_account = wp_invoice_get_option( 'payment_gateway_account' );	
	if ( $wp_invoice_payment_gateway_account )
	{
		return $wp_invoice_payment_gateway_account;
	}
	else
	{
		return '';	
	}
}
function wp_invoice_payment_gateway_account()
{
	echo wp_invoice_get_payment_gateway_account();
}

/**
 * wp_invoice_paypal_page_style
 * Set the Page Style for PayPal Purchase page
 *
 * @since 2.0.2
 * @return string
 */
function wp_invoice_get_paypal_page_style()
{
	$wp_invoice_paypal_page_style = wp_invoice_get_option( 'paypal_page_style' );	
	if ( $wp_invoice_paypal_page_style )
	{
		$page_style = trim( $wp_invoice_paypal_page_style );
	}
	else
	{
		$page_style = 'PayPal';
	}
	return apply_filters( 'wp_invoice_paypal_page_style', $page_style );
}
function wp_invoice_paypal_page_style()
{
	echo wp_invoice_get_paypal_page_style();
}

/*--------------------------------------------------------------------------------------------
									the_invoice_total
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_the_invoice_subtotal( $addon = false )
{
	global $post;
	return wp_invoice_get_invoice_subtotal( $post->ID, $addon );
}
function the_invoice_subtotal()
{
	global $post;
	echo wp_invoice_format_amount( wp_invoice_get_invoice_subtotal( $post->ID ) );
}

function wp_invoice_get_the_invoice_tax()
{
	global $post;
	return wp_invoice_get_invoice_tax( $post->ID );
}
function the_invoice_tax()
{
	global $post;
	echo wp_invoice_format_amount( wp_invoice_get_invoice_tax( $post->ID ) );
}

function get_the_invoice_total()
{
	global $post;
	return wp_invoice_get_invoice_total( $post->ID );
}
function the_invoice_total()
{
	global $post;
	echo wp_invoice_format_amount( wp_invoice_get_invoice_total( $post->ID ) );
}

/*--------------------------------------------------------------------------------------------
											Invoice Subtotal
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_invoice_subtotal( $invoice_id, $addon = false )
{
	$total = 0.00;
	$detailSubtotal = get_post_meta( $invoice_id, 'detail_subtotal', true );
	$payment_gateway_fee = wp_invoice_get_option( 'payment_gateway_fee' );
	if ( $detailSubtotal )
	{
		foreach( $detailSubtotal as $subtotal )
		{
			$total += floatval( $subtotal );
		}
		if ( $addon && !empty( $payment_gateway_fee ) )
		{
			$fee = ( $total * floatval( $payment_gateway_fee ) );
			$total += $fee;
		}
	}
	return number_format( $total, 2, '.', '' );
}

/*--------------------------------------------------------------------------------------------
											Invoice Tax
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_invoice_tax( $invoice_id )
{
	$total = floatval( wp_invoice_get_invoice_subtotal( $invoice_id ) * wp_invoice_get_wp_invoice_tax( $invoice_id ) );
	return number_format( $total, 2, '.', '' ); 
}

/*--------------------------------------------------------------------------------------------
											Invoice Total
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_invoice_total( $invoice_id )
{
	$total = floatval( wp_invoice_get_invoice_subtotal( $invoice_id ) + wp_invoice_get_invoice_tax( $invoice_id ) );
	return number_format( $total, 2, '.', '' ); 
}

/**
 * wp_invoice_payment_gateway_button
 *
 * @since 1.0.0
 *
 * Creates the chosen payment gateway button
 **/
function wp_invoice_payment_gateway_button()
{
	$wp_invoice = WP_INVOICE_PRO();
	$wp_invoice->invoice->wp_invoice_payment_gateway_button();
}

/**
 * Company details
 *
 * @since 2.0.0
 *
 * Get options that were set in the Invoice >> Options page
 **/ 
function wp_invoice_get_option( $val = false, $section = 'wp_invoice_pro_settings' )
{
	$options = get_option( $section );
	if ( isset( $options[$val] ) ) {
		return $options[$val];
	}
	return false;
}

/* Echo settings */
function wp_invoice_option( $val = false, $section = 'wp_invoice_pro_settings' )
{
	$options = get_option( $section );
	if ( isset( $options[$val] ) ) {
		echo $options[$val];
	}
}

/*--------------------------------------------------------------------------------------------
											Return Menu
--------------------------------------------------------------------------------------------*/
function wp_invoice_get_menu( $id = false, $key = false, $verify = false ) {
	
	$continue	= true;
	
	// Verify that the key matches
	if ( $verify ) {
		if ( $id && $key == wp_invoice_get_key( $id ) ) {
			$continue = true;
		} else {
			$continue = false;
		}
	}
	
	// Check for a cookie
	if ( isset( $_COOKIE['wp_invoice_client_id'] ) && !$id ) {
		$id = $_COOKIE['wp_invoice_client_id'];
	}
	
	$menu = '<nav><ul>';
			
	if ( $id && $continue ) {
	
		// Set a current class name if we're viewing the Dashboard
		if ( is_home() )
			$li_class = 'dashboard-link current-menu-item';
		else
			$li_class = 'dashboard-link';	
		
		$menu .= '<li class="'.$li_class.'"><a href="' . add_query_arg( array( 'key' => wp_invoice_get_key( $id ), 'client_id' => $id ), get_post_type_archive_link( 'invoice' ) ) . '">' . __( 'Dashboard', 'wp-invoice-pro' ) . '</a></li>';
	}
	
	$menu .= wp_nav_menu( array( 'container' => '', 'theme_location' => 'main_menu' , 'items_wrap' => '%3$s', 'fallback_cb' => false, 'echo' => 0 ) ) . '</ul>
	</nav>
	<div class="clearfix"></div>
	</header>
	
	<section id="page" role="main">';
	
	echo $menu;		
}

/* wp_invoice_get_key()
 *
 * Takes an ID and encrypts it using a pre-defined SALT. 
 * Returns either the key or false
 *
 * @since 1.0.0
 * @author Sawyer Hollenshead
 */
function wp_invoice_get_key( $id = false ) {
	if ( $id ) {
		/* Never change the SALT unless you want 
		 * to screw up all previous URLS you've sent
		 */
		$key = crypt( $id, 'wp-invoice-pro' );
		return $key;
	} else {
		return false;
	}
}

/* wp_invoice_setcookie()
 *
 * Takes an ID and encrypts it using a pre-defined SALT. 
 * Returns either the key or false
 *
 * @since 2.0.0
 */
function wp_invoice_setcookie( $name = 'wp_invoice_client_id', $value = '', $time = DAY_IN_SECONDS, $path = '/' ) {
	$current_user	= wp_get_current_user();
	$set_cookie  	= true;
	
	// User is logged in
	if ( 0 != $current_user->ID ) {
		$set_cookie = false;
	
		// User can't edit other users
		if ( !current_user_can( 'edit_users' ) )
			$set_cookie = true;
	}
	
	if ( !empty( $value ) && $set_cookie ) {
		setcookie( $name, $value, time() + $time, $path, home_url() );
	}
}

/* wp_invoice_get_search_form()
 *
 * Search for Invoice.
 *
 * @since 2.1.3
 */
function wp_invoice_get_search_form( $sidebar = false ) {		
	ob_start(); ?>	
	<section class="page-entry error-form">
		<h2><?php _e( 'Try Doing a Search', 'wp-invoice-pro' ); ?></h2>
		<form method="get" action="/">
			<label for="client_id"><?php _e( 'Client ID:', 'wp-invoice-pro' ); ?></label>
			<input type="text" name="client_id" />
			
			<label for="key"><?php _e( 'Access Key:', 'wp-invoice-pro' ); ?></label>
			<input type="text" name="key" />
			
            <input type="hidden" name="post_type" value="invoice" />
			<input class="btn" type="submit" value="<?php _e( 'Search', 'wp-invoice-pro' ); ?>" />
		</form>
		<?php if ( is_active_sidebar( 'invoice-sidebar' ) && $sidebar ) : ?>
        <aside id="invoice-sidebar">
			<?php dynamic_sidebar( 'invoice-sidebar' ); ?>
        </aside>
		<?php endif; ?>
	</section><?php
	
	return ob_get_clean();
}

/* Echo search form */
function wp_invoice_search_form() {
	echo wp_invoice_get_search_form();
}