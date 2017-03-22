<?php
/**
 * Plugin Name: WP Invoice Pro
 * Plugin URI: http://extendd.com/plugin/wordpress-invoice-pro
 * Description: An online invoice solution for web developers. Manage, print and email invoices through WordPress and customize with php + html + css.
 * Version: 1.1.5
 * Author: Austin Passy
 * Author URI: http://austinpassy.com
 * Text Domain: wp-invoice-pro
 *
 * @copyright 2012 - 2013
 * @author Austin Passy
 * @link http://frostywebdesigns.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @class wp_invoice_pro
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'wp_invoice_pro' ) ) {
	
function WP_INVOICE_PRO() {
	$GLOBALS["wp_invoice_pro"] = new wp_invoice_pro;
}
add_action( 'plugins_loaded', 'WP_INVOICE_PRO' );

class wp_invoice_pro { 

	/**
	 * @var string
	 */
	var $version,
		$name,
		$basename,
		$plugin_url,
		$plugin_path,
		$siteurl,
		$invoice,
		$post_type,
		$api_key,
		$stats,
		$settings;
		
	private $plugin_id,
			$plugin_name;

	/**
	 * wp_invoice_pro constructor.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
		
		$this->version		= '1.1.5';
		$this->name			= 'WP Invoice Pro';
		$this->basename		= plugin_basename( __FILE__ );
		$this->plugin_url	= plugin_dir_url( __FILE__ );
		$this->plugin_path	= plugin_dir_path( __FILE__ );
		$this->siteurl		= get_site_url();
		$this->post_type	= 'wp-invoice';
		$this->settings_name= 'wp_invoice_settings';
		$this->settings		= get_option( $this->settings_name, array() );
		
		/* Updates */
		$this->plugin_id 	= 'extendd_wordpress_invoice_pro';
		$this->plugin_name 	= 'WordPress Invoice Pro';

		/* Define version constant */
		define( 'WP_INVOICE_PRO_VERSION', $this->version );
		
		/* Activation & Deactivation */
		register_activation_hook( __FILE__, array( $this, 'init_user_roles' ) );
		register_activation_hook( __FILE__, 'flush_rewrite_rules' );
		register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
		
		/* Extendd */
		$this->extendd_settings();
		
		/* Include required files */
		$this->includes();

		/* Actions */
		add_action( 'init', array( $this, 'init' ), 0 );
		
		/* Loaded action */
		do_action( 'wp_invoice_pro_loaded' );
	}
	
	/**
	 * Upgrade script
	 *
	 * @since	11/30/2012
	 */
	function extendd_settings() {
		if ( !class_exists( 'extendd_settings_api' ) ) {
			include( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'library/classes/extendd-settings.php' );
		}
		add_filter( 'extendd_add_settings_sections', 	array( $this, 'add_settings_section' ) );
		add_filter( 'extendd_add_settings_fields',		array( $this, 'add_settings_fields' ) );
				
		$options = get_option( $this->plugin_id, array() );
		if ( isset( $options ) && ( !empty( $options['license_key'] ) && 'valid' === $options['license_active'] ) ) {
			if ( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
				// load our custom updater
				include( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'library/classes/EDD_SL_Plugin_Updater.php' );
			}
		
			$edd_updater = new EDD_SL_Plugin_Updater( 'http://extendd.com/', __FILE__,
				array(
					'version'   => WP_INVOICE_PRO_VERSION, // current version number
					'license'   => trim( $options['license_key'] ), // license key
					'item_name' => $this->plugin_name, // name of this plugin in the Easy Digital Downloads system
					'author'    => 'Austin Passy' // author of this plugin
				)
			);
		}
	}
	
	/**
	 * Returns new settings section for this plugin
	 *
	 * @return 	array settings fields
	 */
	function add_settings_section( $sections ) {
		$sections[] = array(
			'id' 		=> $this->plugin_id,
			'title' 	=> $this->plugin_name, //Must match EDD post_title!
			'basename'	=> plugin_basename( __FILE__ ),
			'version'   => WP_INVOICE_PRO_VERSION,
		);
		return $sections;
	}
	
	/**
	 * Returns new settings fields for this plugin
	 *
	 * @return 	array settings fields
	 */
	function add_settings_fields( $settings_fields ) {
		$settings_fields[$this->plugin_id] = array(
			array(
				'name' 			=> 'license_key',
				'label' 		=> __( 'License Key', 'wp-invoice' ),
				'desc' 			=> sprintf( __( 'Enter your license for %s to receive automatic updates', 'wp-invoice' ), $this->plugin_name ),
				'type' 			=> 'text',
				'default' 		=> '',
				'placeholder'	=> __( 'Enter your license key', 'wp-invoice' )
			),
			array(
				'name' 			=> 'license_active',
				'label' 		=> '',
				'desc' 			=> '',
				'type' 			=> 'hidden',
				'default' 		=> ''
			),
		);	
		return $settings_fields;
	}

	/**
	 * Include required core files.
	 *
	 * @access 	private
	 * @return 	void
	 */
	function includes() {
		require_once( trailingslashit( $this->plugin_path ) . 'admin/admin.php' );
		require_once( trailingslashit( $this->plugin_path ) . 'library/classes/post-type.php' );
		require_once( trailingslashit( $this->plugin_path ) . 'library/functions/users.php' );
		require_once( trailingslashit( $this->plugin_path ) . 'library/functions/core.php' );
		require_once( trailingslashit( $this->plugin_path ) . 'library/functions/filters.php' );
		require_once( trailingslashit( $this->plugin_path ) . 'library/functions/shortcodes.php' );
	}
	
	/**
	 * Initiate required classes and set up actions.
	 *
	 * @access 	private
	 * @return 	void
	 */
	function init() {
		
		$this->invoice		= new wp_invoice_post_type(	$this );
		$this->settings		= new wp_invoice_settings(	$this );
		
		/* Scritps & Styles */
		add_action( 'admin_enqueue_scripts',	array( $this, 'admin_scripts' ) );
		add_action( 'wp_enqueue_scripts',		array( $this, 'frontend_scripts' ) );
		
		/* wp_mail */
		add_filter( 'wp_mail_content_type', 	array( $this, 'set_content_type' ) );
		
		/* Protected titles */
		add_filter( 'protected_title_format', 	array( $this, 'protected_title_format' ) );
	}

	/**
	 * Register Admin Scripts and Styles.
	 * 
	 * @return	void
	 */
	function admin_scripts() {		
		/* Scripts */
		wp_register_script( 'wp-invoice-admin', plugins_url( 'library/js/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-sortable' ), $this->version, false );
		
		/* Styles  */
		wp_register_style( 'wp-invoice-admin', plugins_url( 'library/css/admin.css', __FILE__ ), null, $this->version, 'screen' );
	}
	
	/**
	 * Register Scripts and Style needed for this plugin.
	 * 
	 * @return	void
	 */
	function frontend_scripts() {
		if ( is_admin() ) return;
		
		/* Scripts */
		wp_register_script( 'prefixfree', plugins_url( 'library/js/prefixfree.min.js', __FILE__ ), null, '1.0.7', false );
		wp_register_script( $this->post_type, plugins_url( 'library/js/functions.js', __FILE__ ), array( 'jquery' ), $this->version, true );
		
		if ( $this->post_type == get_post_type() || ( isset( $_GET['post_type'] ) && $this->post_type == $_GET['post_type'] ) ) {		
			wp_enqueue_script( 'prefixfree' );
			wp_enqueue_script( $this->post_type );	
		}
	}
	
	/**
	 * Always set content type to HTML
	 *
	 * @param 	string
	 * @return 	string
	 */
	function set_content_type( $content_type ) {
		// Only convert if the message is text/plain and the template is ok
		if ( 'text/plain' == $content_type ) {
			add_action( 'phpmailer_init', array( &$this, 'send_html' ) );
			return $content_type = 'text/html';
		}
		return $content_type;
	}
	
	/**
	 * Add the email template and set it multipart
	 *
	 * @param 	object $phpmailer
	 */
	function send_html( $phpmailer ) {
		// Set the original plain text message
		$phpmailer->AltBody = wp_specialchars_decode( $phpmailer->Body, ENT_QUOTES );
		$phpmailer->Body = make_clickable( $phpmailer->Body );
	}	
	
	/**
	 * Remove 'Pretected' from the title
	 *
	 * @return 	string
	 */
	function protected_title_format( $title ) {
		if ( $this->post_type == get_post_type() ) {
			$title = '%s';
		}
		return $title;
	}
	
	/**
	 * Activation & Deactivation
	 * 
	 * @return 	void
	 */
	function flush_rewrite_rules() {
		flush_rewrite_rules( false );
	}
	
	/**
	 * Init user roles.
	 *
	 * @access 	public
	 * @return 	void
	 */
	function init_user_roles() {
		global $wp_roles;

		if ( class_exists('WP_Roles') ) if ( !isset( $wp_roles ) ) $wp_roles = new WP_Roles();

		if ( is_object( $wp_roles ) ) {

			// Customer role
			add_role( 'client', __( 'Client', 'wp-invoice' ), array(
			    'read'				=> true,
				'view_wp_invoice'	=> true,
			) );

			// Main Shop capabilities for admin
			$wp_roles->add_cap( 'administrator', 'view_wp_invoice' );
		}
	}
	
}

};