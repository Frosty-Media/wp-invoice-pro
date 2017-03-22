<?php
/**
 * Upload Functions
 *
 * @package     EDD
 * @subpackage  Admin/Upload
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Change Downloads Upload Directory
 *
 * Hooks the wp_invoice_set_upload_dir filter when appropriate. This function works by
 * hooking on the WordPress Media Uploader and moving the uploading files that
 * are used for EDD to an edd directory under wp-content/uploads/ therefore,
 * the new directory is wp-content/uploads/edd/{year}/{month}. This directory is
 * provides protection to anything uploaded to it.
 *
 * @since 1.0
 * @global $pagenow
 * @return void
 */
function wp_invoice_change_downloads_upload_dir() {
	global $pagenow;

	if ( ! empty( $_REQUEST['post_id'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) ) {
		if ( 'invoice' == get_post_type( $_REQUEST['post_id'] ) ) {
			wp_invoice_create_protection_files( true );
			add_filter( 'upload_dir', 'wp_invoice_set_upload_dir' );
		}
	}
}
add_action( 'admin_init', 'wp_invoice_change_downloads_upload_dir', 999 );

/**
 * Set Upload Directory
 *
 * Sets the upload dir to edd. This function is called from
 * edd_change_downloads_upload_dir()
 *
 * @since 1.0
 * @return array Upload directory information
 */
function wp_invoice_set_upload_dir( $upload ) {

	// Override the year / month being based on the post publication date, if year/month organization is enabled
	if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
		// Generate the yearly and monthly dirs
		$time = current_time( 'mysql' );
		$y = substr( $time, 0, 4 );
		$m = substr( $time, 5, 2 );
		$upload['subdir'] = "/$y/$m";
	}

	$upload['subdir'] = '/wp-invoice-pro' . $upload['subdir'];
	$upload['path']   = $upload['basedir'] . $upload['subdir'];
	$upload['url']    = $upload['baseurl'] . $upload['subdir'];
	return $upload;
}


/**
 * Creates blank index.php and .htaccess files
 *
 * This function runs approximately once per month in order to ensure all folders
 * have their necessary protection files
 *
 * @since 1.1.5
 *
 * @param bool $force
 * @param bool $method
 */

function wp_invoice_create_protection_files( $force = false, $method = 'direct' ) {
	if ( false === get_transient( 'wp_invoice_check_protection_files' ) || $force ) {

		$upload_path = wp_invoice_get_upload_dir();

		// Make sure the /edd folder is created
		wp_mkdir_p( $upload_path );

		// Top level .htaccess file
		$rules = wp_invoice_get_htaccess_rules( $method );
		if ( wp_invoice_htaccess_exists() ) {
			$contents = @file_get_contents( $upload_path . '/.htaccess' );
			if ( $contents !== $rules || ! $contents ) {
				// Update the .htaccess rules if they don't match
				@file_put_contents( $upload_path . '/.htaccess', $rules );
			}
		} elseif( wp_is_writable( $upload_path ) ) {
			// Create the file if it doesn't exist
			@file_put_contents( $upload_path . '/.htaccess', $rules );
		}

		// Top level blank index.php
		if ( ! file_exists( $upload_path . '/index.php' ) && wp_is_writable( $upload_path ) ) {
			@file_put_contents( $upload_path . '/index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
		}

		// Now place index.php files in all sub folders
		$folders = wp_invoice_scan_folders( $upload_path );
		foreach ( $folders as $folder ) {
			// Create index.php, if it doesn't exist
			if ( ! file_exists( $folder . 'index.php' ) && wp_is_writable( $folder ) ) {
				@file_put_contents( $folder . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
			}
		}
		// Check for the files once per day
		set_transient( 'wp_invoice_check_protection_files', true, 3600 * 24 );
	}
}
add_action( 'admin_init', 'wp_invoice_create_protection_files' );

/**
 * Checks if the .htaccess file exists in wp-content/uploads/edd
 *
 * @since 1.8
 * @return bool
 */
function wp_invoice_htaccess_exists() {
	$upload_path = wp_invoice_get_upload_dir();

	return file_exists( $upload_path . '/.htaccess' );
}

/**
 * Retrieve the absolute path to the file upload directory without the trailing slash
 *
 * @since  1.8
 * @return string $path Absolute path to the EDD upload directory
 */
function wp_invoice_get_upload_dir() {
	$wp_upload_dir = wp_upload_dir();
	wp_mkdir_p( $wp_upload_dir['basedir'] . '/wp-invoice-pro' );
	$path = $wp_upload_dir['basedir'] . '/wp-invoice-pro';

	return apply_filters( 'wp_invoice_get_upload_dir', $path );
}

/**
 * Scans all folders inside of /uploads/edd
 *
 * @since 1.1.5
 * @return array $return List of files inside directory
 */
function wp_invoice_scan_folders( $path = '', $return = array() ) {
	$path = $path == ''? dirname( __FILE__ ) : $path;
	$lists = @scandir( $path );

	if ( ! empty( $lists ) ) {
		foreach ( $lists as $f ) {
			if ( is_dir( $path . DIRECTORY_SEPARATOR . $f ) && $f != "." && $f != ".." ) {
				if ( ! in_array( $path . DIRECTORY_SEPARATOR . $f, $return ) )
					$return[] = trailingslashit( $path . DIRECTORY_SEPARATOR . $f );

				wp_invoice_scan_folders( $path . DIRECTORY_SEPARATOR . $f, $return);
			}
		}
	}

	return $return;
}

/**
 * Retrieve the .htaccess rules to wp-content/uploads/edd/
 *
 * @since 1.6
 *
 * @param bool $method
 * @return mixed|void The htaccess rules
 */
function wp_invoice_get_htaccess_rules( $method = false ) {

	switch( $method ) :

		case 'redirect' :
			// Prevent directory browsing
			$rules = "Options -Indexes";
			break;

		case 'direct' :
		default :
			// Prevent directory browsing and direct access to all files, except images (they must be allowed for featured images / thumbnails)
			$rules = "Options -Indexes\n";
			$rules .= "<IfModule mod_rewrite.c>\n";
			    $rules .= "RewriteEngine on\n";
			    $rules .= "RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?" . str_replace( array( 'http://', 'https://', 'www' ), '', home_url() ) . " [NC]\n";
			    $rules .= "RewriteRule \.(jpg|jpeg|png|gif)$ - [NC,F,L]\n";
			$rules .= "</IfModule>\n";
			break;

	endswitch;
	$rules = apply_filters( 'wp_invoice_protected_directory_htaccess_rules', $rules, $method );
	return $rules;
}


// For installs on pre WP 3.6
if( ! function_exists( 'wp_is_writable' ) ) {

	/**
	 * Determine if a directory is writable.
	 *
	 * This function is used to work around certain ACL issues
	 * in PHP primarily affecting Windows Servers.
	 *
	 * @see win_is_writable()
	 *
	 * @since 3.6.0
	 *
	 * @param string $path
	 * @return bool
	 */
	function wp_is_writable( $path ) {
	        if ( 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) ) )
	                return win_is_writable( $path );
	        else
	                return @is_writable( $path );
	}
}