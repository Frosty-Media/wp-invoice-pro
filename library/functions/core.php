<?php
/**
 * The core functions file for the WP Invoice Pro plugin. Functions defined here are generally
 * used across the entire plugin to make various tasks faster. This file should be loaded
 * prior to any other files because its functions are needed to run the plugin.
 *
 * @package wp_invoice_pro
 * @subpackage Functions
 * @author Austin Passy <austin@frostywebdesigns.com>
 * @copyright Copyright (c) 2012, Austin Passy
 * @link http://thefrosty.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
 
function wp_invoice_get_post_id() {
	global $post;
	
	return $post->ID;
}

/**
 * Get the currency from the settings page.
 * Default to US if not set.
 *
 * @return string
 * @since 1.0.0
 */
function wp_invoice_get_currency() {
	$settings = get_option('wp_invoice_settings');
	if ( $settings['currency'] ) {
		return $settings['currency'];
	} else {
		return 'US';
	}
}

/**
 * Get the currency code from the settings page.
 *
 * @return string
 */
function wp_invoice_get_currency_code() {
	$countries = wp_invoice_get_countries();
	return $countries[wp_invoice_get_currency()]['currency']['code'];
}

/**
 * Get the currency format.
 *
 * @return string
 * @since 1.0.0
 */
function wp_invoice_get_currency_format() {
	$countries = wp_invoice_get_countries();
	return $countries[ wp_invoice_get_currency() ]['currency']['format'];
}

/**
 * Replace the '?' from the country formant.
 *
 * @return string
 * @since 1.0.0
 */
function wp_invoice_currency_format( $input ) {
	$currency = wp_invoice_get_currency();
	$currency = strtolower( $currency ) . '_' . strtoupper( $currency );
	setlocale( LC_MONETARY, $currency );
	return str_replace( '?', $input, wp_invoice_get_currency_format() );
}

/**
 * Return the number format.
 *
 * @use filter your number_format to change the number of decimals,
 *		the decimal point or the thousands seperator.
 *
 * @return string
 * @since 1.0.0
 */
function wp_invoice_number_format( $number, $decimals = 2, $dec_point = '.', $thousands = ',' ) {
	
	return number_format(
		$number,
		apply_filters( 'wp_invoice_decimals', $decimals ),
		apply_filters( 'wp_invoice_decimal_point', $dec_point ),
		apply_filters( 'wp_invoice_thousands_separator', $thousands )
	);
}

/**
 * Default set of countries and their currency code and format.
 *
 * Add your own country code by creating a extenstion function 
 * in your themes functions.php OR a core plugin.
 *
 function my_country_invoice_filter( $countries ) {
	// Be sure the use a two letter code, the country name,
	// currency code and format WITH the question mark before or after.	
	$countries['MY'] = array( 'name' => 'My Country', 'currency' => array( 'code' => 'MYC', 'format' => '$?' ) ); 
	return $countries;
 }
 add_filter( 'wp_invoice_countries', 'my_country_invoice_filter' );
 *
 * @return array
 * @since 1.0.0
 */
function wp_invoice_get_countries()  {
	
	$countries = array();
	$countries['CA'] = array( 'name' => 'Canada',				'currency' => array( 'code' => 'CAD', 'format' => '$?' ) ); 
	$countries['US'] = array( 'name' => 'USA',					'currency' => array( 'code' => 'USD', 'format' => '$?' ) ); 
	$countries['GB'] = array( 'name' => 'United Kingdom',		'currency' => array( 'code' => 'GBP', 'format' => '&pound;?' ) ); 
	$countries['AR'] = array( 'name' => 'Argentina', 			'currency' => array( 'code' => 'ARS', 'format' => '$?' ) );
	$countries['AW'] = array( 'name' => 'Aruba', 				'currency' => array( 'code' => 'AWG', 'format' => '&fnof;?' ) );
	$countries['AU'] = array( 'name' => 'Australia', 			'currency' => array( 'code' => 'AUD', 'format' => '$?' ) );
	$countries['AT'] = array( 'name' => 'Austria', 				'currency' => array( 'code' => 'EUR', 'format' => '&euro;?' ) );
	$countries['BB'] = array( 'name' => 'Barbados', 			'currency' => array( 'code' => 'BBD', 'format' => '$?' ) );
	$countries['BS'] = array( 'name' => 'Bahamas', 				'currency' => array( 'code' => 'BSD', 'format' => '$?' ) );
	$countries['BE'] = array( 'name' => 'Belgium',				'currency' => array( 'code' => 'EUR', 'format' => '? &euro;' ) );
	$countries['BR'] = array( 'name' => 'Brazil',				'currency' => array( 'code' => 'BRL', 'format' => 'R$?' ) );
	$countries['CL'] = array( 'name' => 'Chile',				'currency' => array( 'code' => 'CLP', 'format' => '$?' ) );
	$countries['CN'] = array( 'name' => 'China',				'currency' => array( 'code' => 'CNY', 'format' => '&yen;?' ) );
	$countries['CO'] = array( 'name' => 'Colombia',				'currency' => array( 'code' => 'COP', 'format' => '$?' ) );
	$countries['CR'] = array( 'name' => 'Costa Rica',			'currency' => array( 'code' => 'CRC', 'format' => '&#x20a2;?' ) );
	$countries['HR'] = array( 'name' => 'Croatia',				'currency' => array( 'code' => 'HRK', 'format' => '? kn' ) );
	$countries['CY'] = array( 'name' => 'Cyprus',				'currency' => array( 'code' => 'CYP', 'format' => '&pound;?' ) );
	$countries['DK'] = array( 'name' => 'Denmark',				'currency' => array( 'code' => 'DKK', 'format' => '? kr' ) ); 
	$countries['DO'] = array( 'name' => 'Dominican Republic',	'currency' => array( 'code' => 'DOP', 'format' => '$?' ) ); 
	$countries['EC'] = array( 'name' => 'Ecuador',				'currency' => array( 'code' => 'ESC', 'format' => '$?' ) ); 
	$countries['EG'] = array( 'name' => 'Egypt',				'currency' => array( 'code' => 'EGP', 'format' => '&pound;?' ) ); 
	$countries['EE'] = array( 'name' => 'Estonia',				'currency' => array( 'code' => 'EEK', 'format' => '? EEK' ) );
	$countries['FI'] = array( 'name' => 'Finland',				'currency' => array( 'code' => 'EUR', 'format' => '&euro;?' ) );
	$countries['FR'] = array( 'name' => 'France',				'currency' => array( 'code' => 'EUR', 'format' => '&euro;?' ) );
	$countries['DE'] = array( 'name' => 'Germany',				'currency' => array( 'code' => 'EUR', 'format' => '&euro;?' ) ); 
	$countries['GR'] = array( 'name' => 'Greece',				'currency' => array( 'code' => 'EUR', 'format' => '&euro;?' ) ); 
	$countries['GP'] = array( 'name' => 'Guadeloupe',			'currency' => array( 'code' => 'EUR', 'format' => '&euro;?' ) ); 
	$countries['GT'] = array( 'name' => 'Guatemala',			'currency' => array( 'code' => 'GTQ', 'format' => 'Q?' ) ); 
	$countries['HK'] = array( 'name' => 'Hong Kong',			'currency' => array( 'code' => 'HKD', 'format' => '$?' ) ); 
	$countries['HU'] = array( 'name' => 'Hungary',				'currency' => array( 'code' => 'HUF', 'format' => '? Ft' ) ); 
	$countries['IS'] = array( 'name' => 'Iceland',				'currency' => array( 'code' => 'ISK', 'format' => '? kr.' ) ); 
	$countries['IN'] = array( 'name' => 'India',				'currency' => array( 'code' => 'INR', 'format' => '&#x20a8;?' ) ); 
	$countries['ID'] = array( 'name' => 'Indonesia',			'currency' => array( 'code' => 'IDR', 'format' => 'Rp ?' ) ); 
	$countries['IE'] = array( 'name' => 'Ireland',				'currency' => array( 'code' => 'EUR', 'format' => '&euro;?' ) ); 
	$countries['IL'] = array( 'name' => 'Israel',				'currency' => array( 'code' => 'ILS', 'format' => '&#8362; ?' ) ); 
	$countries['IT'] = array( 'name' => 'Italy',				'currency' => array( 'code' => 'EUR', 'format' => '&euro;?' ) ); 
	$countries['JM'] = array( 'name' => 'Jamaica',				'currency' => array( 'code' => 'JMD', 'format' => '$?' ) ); 
	$countries['JP'] = array( 'name' => 'Japan',				'currency' => array( 'code' => 'JPY', 'format' => '&yen;?' ) ); 
	$countries['LV'] = array( 'name' => 'Latvia',				'currency' => array( 'code' => 'LVL', 'format' => '? Ls' ) ); 
	$countries['LT'] = array( 'name' => 'Lithuania',			'currency' => array( 'code' => 'LTL', 'format' => '? Lt' ) ); 
	$countries['LU'] = array( 'name' => 'Luxembourg',			'currency' => array( 'code' => 'EUR', 'format' => '&euro;?' ) ); 
	$countries['MY'] = array( 'name' => 'Malaysia',				'currency' => array( 'code' => 'MYR', 'format' => 'RM?' ) ); 
	$countries['MT'] = array( 'name' => 'Malta',				'currency' => array( 'code' => 'MTL', 'format' => '&euro;?' ) ); 
	$countries['MX'] = array( 'name' => 'Mexico',				'currency' => array( 'code' => 'MXN', 'format' => '$?' ) ); 
	$countries['NL'] = array( 'name' => 'Netherlands',			'currency' => array( 'code' => 'EUR', 'format' => '&euro;?' ) ); 
	$countries['NZ'] = array( 'name' => 'New Zealand',			'currency' => array( 'code' => 'NZD', 'format' => '$?' ) ); 
	$countries['NG'] = array( 'name' => 'Nigeria',				'currency' => array( 'code' => 'NGN', 'format' => '&#x20a6;?' ) );
	$countries['NO'] = array( 'name' => 'Norway',				'currency' => array( 'code' => 'NOK', 'format' => 'kr ?' ) ); 
	$countries['PK'] = array( 'name' => 'Pakistan',				'currency' => array( 'code' => 'PKR', 'format' => '&#x20a8;?' ) ); 
	$countries['PE'] = array( 'name' => 'Peru',					'currency' => array( 'code' => 'PEN', 'format' => 'S/. ?' ) ); 
	$countries['PH'] = array( 'name' => 'Philippines',			'currency' => array( 'code' => 'PHP', 'format' => 'Php ?' ) ); 
	$countries['PT'] = array( 'name' => 'Portugal',				'currency' => array( 'code' => 'EUR', 'format' => '&euro;?' ) ); 
	$countries['PR'] = array( 'name' => 'Puerto Rico',			'currency' => array( 'code' => 'USD', 'format' => '$?' ) ); 
	$countries['RO'] = array( 'name' => 'Romania',				'currency' => array( 'code' => 'ROL', 'format' => '? lei' ) );
	$countries['SG'] = array( 'name' => 'Singapore',			'currency' => array( 'code' => 'SGD', 'format' => '$?' ) ); 
	$countries['SK'] = array( 'name' => 'Slovakia',				'currency' => array( 'code' => 'EUR', 'format' => '&euro;?' ) ); 
	$countries['SI'] = array( 'name' => 'Slovenia',				'currency' => array( 'code' => 'EUR', 'format' => '&euro;?' ) ); 
	$countries['ZA'] = array( 'name' => 'South Africa',			'currency' => array( 'code' => 'ZAR', 'format' => 'R?' ) ); 
	$countries['KR'] = array( 'name' => 'South Korea',			'currency' => array( 'code' => 'KRW', 'format' => '&#x20a9;?' ) ); 
	$countries['ES'] = array( 'name' => 'Spain',				'currency' => array( 'code' => 'EUR', 'format' => '&euro;?' ) ); 
	$countries['VC'] = array( 'name' => 'St. Vincent',			'currency' => array( 'code' => 'XCD', 'format' => '$?' ) ); 
	$countries['SE'] = array( 'name' => 'Sweden',				'currency' => array( 'code' => 'SEK', 'format' => '? kr' ) ); 
	$countries['CH'] = array( 'name' => 'Switzerland',			'currency' => array( 'code' => 'CHF','format'=>"# CHF")); 
	$countries['TW'] = array( 'name' => 'Taiwan',				'currency' => array( 'code' => 'TWD', 'format' => 'NT$?' ) ); 
	$countries['TH'] = array( 'name' => 'Thailand',				'currency' => array( 'code' => 'THB', 'format' => '?&#xe3f;' ) ); 
	$countries['TT'] = array( 'name' => 'Trinidad and Tobago',	'currency' => array( 'code' => 'TTD', 'format' => 'TT$?' ) ); 
	$countries['TR'] = array( 'name' => 'Turkey',				'currency' => array( 'code' => 'TRL', 'format' => '? TL' ) ); 
	$countries['AE'] = array( 'name' => 'United Arab Emirates',	'currency' => array( 'code' => 'AED', 'format' => 'Dhs. ?' ) ); 
	$countries['UY'] = array( 'name' => 'Uruguay',				'currency' => array( 'code' => 'UYP', 'format' => '$?' ) ); 
	$countries['VE'] = array( 'name' => 'Venezuela',			'currency' => array( 'code' => 'VUB', 'format' => 'Bs. ?' ) ); 
	
	return apply_filters( 'wp_invoice_countries', $countries );
}

/**
 * Get connected users
 *
 * @since 1.0.0
 * @return array
 */
function wp_invoice_get_users( $args = array() ) {
	
	$defaults = array(
		'blog_id' 		=> $GLOBALS['blog_id'],
		'role' 			=> '',
		'meta_key' 		=> '',
		'meta_value' 	=> '',
		'meta_compare' 	=> '',
		'meta_query' 	=> array(),
		'include' 		=> array(),
		'exclude' 		=> array(),
		'orderby' 		=> 'nicename',
		'order' 		=> 'ASC',
		'offset'		=> '',
		'search' 		=> '',
		'number' 		=> '',
		'count_total' 	=> false,
		'fields' 		=> 'all',
		'who' 			=> '',
	);
	
	/**
	 *  Parse incomming $args into an array and merge it with $defaults
	 */ 
	$args = wp_parse_args( $args, $defaults );
	
	extract( $args, EXTR_OVERWRITE );
	
	$users = get_users( $args );
	
	return $users;
}

/**
 * Get first connected user
 *
 * @since 1.0.0
 * @return array
 */
function wp_invoice_get_user( $post_id = null ) {
	global $wp_invoice_client;
	
	if ( is_null( $post_id ) ) {
		$post_id = wp_invoice_get_post_id();
	}	
	
	$wp_invoice_client->the_meta();
	$user_id = $wp_invoice_client->get_the_value('user');
	$user = get_user_by( 'id', $user_id );
	
	if ( empty( $user ) ) {
		$user = wp_get_current_user();
	}
	
	return $user;
}

/**
 * Get user meta
 *
 * @since 1.0.0
 * @return array
 */
function wp_invoice_get_user_meta( $args, $user_id ) {
		
	$defaults = array(
		'company' 	=> 'client_company',
		'address_1' => 'client_address_1', 
		'address_2' => 'client_address_2', 
		'city' 		=> 'client_city',
		'state' 	=> 'client_state', 
		'postcode' 	=> 'client_postcode', 
		'country' 	=> 'client_country'
	);
	
	/**
	 *  Parse incomming $args into an array and merge it with $defaults
	 */ 
	$args = wp_parse_args( $args, $defaults );
	
	/**
	 * OPTIONAL: Declare each item in $args as its own variable i.e. $type, $before.
	 */ 
	extract( $args, EXTR_SKIP );
	
	//print_r( $args ); return;
	
	$user_meta = array();
	
	foreach ( $args as $label => $value ) {
		$user_meta[$label] = get_user_meta( $user_id, $value, true );
	}
	
	return $user_meta;
}

/**
 *
 * @return string
 */
function wp_invoice_get_status( $post_id = null ) {
	global $wp_invoice_detail;
	
	if ( is_null( $post_id ) ) {
		$post_id = wp_invoice_get_post_id();
	}
	
	$wp_invoice_detail->the_meta();

	$invoice_sent = $wp_invoice_detail->get_the_value('sent');
	$invoice_paid = $wp_invoice_detail->get_the_value('paid');
	
	if ( !empty( $invoice_paid ) && !empty( $invoice_sent ) ) {
		return __( 'Paid', 'wp-invoice' );
	} elseif ( !empty( $invoice_sent ) ) {
		$invoice_sent = explode( '/', $invoice_sent );
		$invoice_sent = intval( $invoice_sent[2] ) . '-' . intval( $invoice_sent[0] ) . '-' . intval( $invoice_sent[1] );

		$days = wp_invoice_date_diff( $invoice_sent, date_i18n( 'Y-m-d' ) );
		if ( $days == 0 ) {
			return __( 'Sent today', 'wp-invoice' );
		} elseif ( $days == 1 ) {
			return __( 'Sent 1 day ago', 'wp-invoice' );
		} else {
			return __( 'Sent ', 'wp-invoice' ) . $days . __( ' days ago', 'wp-invoice' );
		} 
	} else {
		return __( 'Not sent yet', 'wp-invoice' );
	}
}

/**
 *
 */
function wp_invoice_date_diff( $start, $end ) {
	$std  = strtotime( $start );
	$end  = strtotime( $end );
	$diff = $end - $std;	
	return round( $diff / 86400 );
}

/**
 *
 */
function wp_invoice_is_status_late( $post_id ) {
	$status = wp_invoice_get_status( $post_id );
	
	if ( $status == __( 'Paid', 'wp-invoice' ) || $status == __( 'Not sent yet', 'wp-invoice' ) )
		return false;
	
	if ( $status == __( 'Sent today', 'wp-invoice' ) || $status == __( 'Sent 1 day ago', 'wp-invoice' ) )
		return false;
	
	$days = explode( ' ', $status );
	
	//print_r( $days ); return;
	if ( intval( $days[1] ) >= intval( 30 ) )
		return '30';
	elseif ( intval( $days[1] ) >= intval( 60 ) )
		return '60';
	elseif ( intval( $days[1] ) >= intval( 90 ) )
		return '90';
}

/**
 *
 */
function wp_invoice_get_type( $post_id = null, $text = null ) {
	global $wp_invoice_detail;
	
	if ( is_null( $post_id ) ) {
		$post_id = wp_invoice_get_post_id();
	}
	
	$wp_invoice_detail->the_meta();
	$type = $wp_invoice_detail->get_the_value('type');
	$out = is_null( $text ) ? __( 'Invoice', 'wp-invoice' ) : $text;
	
	return $type ? $type : $out;
}

/**
 *
 */
function wp_invoice_get_number( $post_id = null, $text = null ) {
	global $wp_invoice_detail;
	
	if ( is_null( $post_id ) ) {
		$post_id = wp_invoice_get_post_id();
	}
	
	$wp_invoice_detail->the_meta();
	$number = $wp_invoice_detail->get_the_value('number');
	$out = is_null( $text ) ? __( 'Not Set', 'wp-invoice' ) : $text;
	
	return $number ? $number : $out;
}

/**
 *
 */
function wp_invoice_get_greeting( $post_id = null ) {
	global $wp_invoice_greeting;
	
	if ( is_null( $post_id ) ) {
		$post_id = wp_invoice_get_post_id();
	}
	
	$wp_invoice_greeting->the_meta();
	$greet = $wp_invoice_greeting->get_the_value('greeting');
	
	return $greet ? $greet : false;
}

/**
 * Get the template URL
 *
 * If the invoice file exists in the current parent theme use that
 * directory, else use the plugin directory path.
 *
 * @return string
 */
function wp_invoice_get_template_url() {
	if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'wp-invoice/invoice.php') ) {
		return trailingslashit( get_stylesheet_directory_uri() ) . 'wp-invoice';		
	} else {
		global $wp_invoice_pro;
		return trailingslashit( $wp_invoice_pro->plugin_url ) . 'template';
	}
}
	
/**
 * Get Payment Gateways
 *
 */
function wp_invoice_get_payment_gateways() {
	global $wp_invoice_pro;
	
	$plugins = array();
	$gateways_path = trailingslashit( $wp_invoice_pro->plugin_path ) . 'library/gateways/';
	
	if ( !$gateways_path ) return;
	
	$files = array_diff( scandir( $gateways_path ), array( '.', '..', '_notes' ) ); 
	
	if ( $files ) {
		foreach( $files as $file ) :
			if ( is_dir( $gateways_path . $file ) ) { break; }					// cancel out the folders
			$file_contents = file_get_contents( $gateways_path . $file );		// 1. Reads file
			preg_match( '|@class (.*)$|mi', $file_contents, $matches );			// 2. Finds Temaplte Name, stores in $matches
			//return $matches;
			if ( !empty( $matches[1] ) ) {
				$plugins[$file] = $matches[1];									// 3. Adds array ([name] => array(path, dir)) 
			}
		endforeach;		
	}
	return $plugins;															// 4. plugin name => plugin file
}

/**
 * Returns an array of files found in the gateway folder
 *
 */
function wp_invoice_get_payment_gateway() {
	$settings = get_option('wp_invoice_settings');
	$payment_gateway = $settings['payment_gateway'];
	
	if ( isset( $payment_gateway ) && $payment_gateway ) {
		return $payment_gateway;
	} else {
		return __( 'None', 'wp-invoice' );
	}
}

function wp_invoice_get_payment_gateway_account() {
	$settings = get_option('wp_invoice_settings');
	$payment_gateway_account = $settings['payment_gateway_account'];
	
	if ( isset( $payment_gateway_account ) && $payment_gateway_account ) {
		return $payment_gateway_account;
	} else {
		return '';	
	}
}
	
/**
 * Payment gateway button output
 *
 */
function wp_invoice_payment_gateway_button( $post_id = null ) {
	global $wp_invoice_pro;
	
	if ( is_null( $post_id ) ) {
		$post_id = wp_invoice_get_post_id();
	}
	
	$payment_gateway_name = wp_invoice_get_payment_gateway();
	$payment_gateway_account = wp_invoice_get_payment_gateway_account();
	
	if ( wp_invoice_get_status( $post_id ) == __( 'Paid', 'wp-invoice' ) )
		return false;
	if ( $payment_gateway_name == __( 'None', 'wp-invoice' ) )
		return false;
	if ( $payment_gateway_account == '' )
		return false;
	
	$info = pathinfo( $payment_gateway_name );
	$payment_gateway_class = basename( $payment_gateway_name, '.' . $info['extension'] );
	$payment_gateway_class = 'wp_invoice_' . $payment_gateway_class;
	
	require_once( trailingslashit( $wp_invoice_pro->plugin_path ) . 'library/gateways/' . $payment_gateway_name );
	$gateway = new $payment_gateway_class( $post_id );
}

?>