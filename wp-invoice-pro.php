<?php
/**
 * Plugin Name: WP Invoice Pro
 * Plugin URI: http://frosty.media/plugins/wordpress-invoice-pro
 * Description: An online invoice solution for web developers. Manage, print and email invoices through WordPress and customize with php + html + css.
 * Version: 2.5.0
 * Author: Austin Passy
 * Author URI: http://austin.passy.co
 * Text Domain: wp-invoice-pro
 *
 * @copyright 2012 - 2015
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
 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
 
define( 'WP_INVOICE_FILE', 		__FILE__ 					);
define( 'WP_INVOICE_BASENAME', 	plugin_basename( __FILE__ ) );
define( 'WP_INVOICE_URL', 		plugin_dir_url( __FILE__ ) 	);
define( 'WP_INVOICE_DIR', 		plugin_dir_path( __FILE__ ) );

class wp_invoice_pro { 

	/** Singleton *************************************************************/
	private static $instance;
	
	var $name,
		$dir,
		$path,
		$siteurl,
		$wpadminurl,
		$version,
	
		$invoice,
		$comments,
		$client,
		$stats,
		$options;
		
	private $plugin_id,
			$plugin_name;
			
	/**
	 * Main Instance
	 *
	 * @staticvar 	array 	$instance
	 * @return 		The one true instance
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
			self::$instance->frosty_media();
			self::$instance->includes();
			self::$instance->init();
		}
		return self::$instance;
	}
	
	/* Empty constructor */
	function __construct() {}
	
	/* Includes */
	function includes() {
		
		// functions
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'functions/functions.php'				);
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'functions/loop.php'						);
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'functions/process-stripe.php'			);
		if ( is_admin() ) {
			require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'functions/upload-functions.php'	);
		}
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'functions/remove-actions-filters.php'	);
		
		// classes
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'classes/wp-invoice-post-type.php'		);
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'classes/wp-invoice-comments.php'		);
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'classes/wp-invoice-client.php'			);
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'classes/wp-invoice-settings.php' 		);
	}
	
	/**
	 * Initiate the plugin
	 */
	function init() {
		
		// set class variables
		$this->name			= __( 'WP Invoice Pro', 'wp-invoice-pro' );
		$this->dir				= WP_INVOICE_DIR;
		$this->path			= WP_INVOICE_URL;
		$this->siteurl			= home_url();
		$this->wpadminurl		= admin_url();
		$this->version			= '2.5.0';
		
		$this->invoice			= new WP_Invoice_Post_Type( $this );
		$this->comments		= new WP_Invoice_Comments( $this );
		$this->client			= new WP_Invoice_Client( $this );
		$this->options			= new WP_Invoice_Settings( $this );
		$this->options->set_prefix( 'wp_invoice_pro' );
		$this->options->set_domain( 'wp-invoice-pro' );
		$this->options->set_version( $this->version );
		
		$this->plugin_id 		= 'wordpress_invoice_pro';
		$this->plugin_name 	= 'WordPress Invoice Pro';
		
		// Core
		add_action( 'admin_enqueue_scripts',		array( $this, 'admin_scripts' ) );
		add_action( 'wp_enqueue_scripts',		array( $this, 'invoice_scripts' ) );
		
		// Prevent canonical URL auto direct - bad for encrypted invoices @since 2.1.4
		add_action( 'init',						array( $this, 'redirect_canonical' ), 99 );
		
		// Templates
		add_action( 'template_redirect',			array( $this, 'invoice_template' ), 9 ); // After WP_Invoice_Post_Type template_redirect
//		add_filter( 'single_template', 			array( $this, 'single_template' ) );
		
		/* Add your sidebars function to the 'widgets_init' action hook. */
		add_action( 'widgets_init',				array( $this, 'register_sidebar' ), 99 );
		
		/* Settings */
		add_action( 'admin_init',					array( $this, 'admin_init' ), 9 );
		add_action( 'admin_menu',					array( $this, 'admin_menu' ), 9 );
		
		add_action( 'init',						array( $this, 'shortcodes' ), 10 );
		
		/* Restricted site access */
		add_filter( 'restricted_site_access_is_restricted',	array( $this, 'unrestricted' ) );
		add_filter( 'the_title',									array( $this, 'the_title' ) );
		
		// Activation
		add_action( 'init', 									array( $this, 'client_metadata_setup' ) );
		register_activation_hook( __FILE__,					function() { flush_rewrite_rules(); } );
		register_deactivation_hook( __FILE__, 				function() { flush_rewrite_rules(); } );
		
		return true;
	}
	
	/**
	 * Load required actions, filters and classes for Frosty.Media plugins
	 *
	 */
	function frosty_media() {
			
		add_action( 'admin_init', array( $this, 'fm_add_plugin_license' ) );
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'classes/class-frosty-media-requires.php' );
	}
		
	/**
	 * Add our license data.
	 */
	public function fm_add_plugin_license() {
		if ( !class_exists( 'Frosty_Media_Licenses' ) )
			return;
			
		global $frosty_media_licenses;
		
		$plugin = array(
			'id' 			=> $this->plugin_id,
			'title' 		=> $this->plugin_name, //Must match EDD post_title!
			'version'		=> $this->version,
			'file'			=> __FILE__,
			'basename'		=> plugin_basename( __FILE__ ),
			'download_id'	=> '91',
			'author'		=> 'Austin Passy' // author of this plugin
		);
		
		$frosty_media_licenses->add_plugin( $plugin );		
	}
	
	/**
	 *
	 **/
	function redirect_canonical() {
		if ( 'invoice' === get_post_type() ) {
			remove_filter( 'template_redirect', 'redirect_canonical' );
		}
	}

	/**
	 * Adds Style + Javascript to admin head
	 *
	 * @author Sawyer
	 * @since 1.0.0
	 * @Todo - only add to wp_invoice_invoice admin pages
	 * 
	 **/
	function admin_scripts( $hook ) {
    	if ( ( 'edit.php' || 'post-new.php' ) != $hook )
			return;
		
		if ( 'invoice' == get_post_type() || isset( $_GET['post_type'] ) && $_GET['post_type'] == 'invoice' ) {
			wp_enqueue_script( 'wp-invoice-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-sortable' ), $this->version, false );
			wp_enqueue_style( 'wp-invoice-admin', plugins_url( 'css/admin.css', __FILE__ ), null, $this->version, 'screen' );
		}
	}

	/**
	 * Adds Style + Javascript to invoice
	 * 
	 **/
	function invoice_scripts() {
    	if ( is_admin() )
			return;
		
		global $wp_query;
		
		if ( 'invoice' == get_post_type() || 'invoice' == get_query_var( 'post_type' ) ) {
			wp_enqueue_script( 'modernizr', plugins_url( 'js/modernizr-2.7.1.min.js', __FILE__ ), null, '2.7.1', false );
//			wp_enqueue_script( 'prefixfree', plugins_url( 'js/prefixfree.min.js', __FILE__ ), null, '1.0.7', false );
			wp_enqueue_script( 'wp-invoice', plugins_url( 'js/functions.js', __FILE__ ), array( 'jquery' ), $this->version, true );
			wp_localize_script( 'wp-invoice', 'wp_invoice', array(
				'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
				'template'	=> get_template(),
				'post_id'	=> intval( $wp_query->get_queried_object_id() ),
				'nonce'		=> wp_create_nonce( WP_INVOICE_BASENAME )
			) );
			if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
				wp_enqueue_script( 'comment-reply' );
			
			wp_dequeue_style( 'style' );
			wp_enqueue_style( 'wp-invoice-style', plugins_url( 'css/style.css', __FILE__ ), null, $this->version, 'screen' );
			wp_enqueue_style( 'wp-invoice-print', plugins_url( 'css/print.css', __FILE__ ), null, $this->version, 'print' );
		}
	}
	
	/**
	 * Archive Template
	 *
	 * @since 2.0.0
	 */
	function invoice_template( $template ) {
		
		if ( 'invoice' === get_query_var('post_type' ) || 'invoice' === get_post_type() || is_post_type_archive( 'invoice' ) ) {
			
			// 1. find single.php template file
			$single_template = trailingslashit( get_stylesheet_directory() ) . 'wp-invoice/single.php';			
			if ( !file_exists( $single_template ) )
				$single_template = trailingslashit( WP_INVOICE_DIR ) . 'template/single.php';
				
			// 2. find index.php template file
			$archive_template = trailingslashit( get_stylesheet_directory() ) . 'wp-invoice/index.php';			
			if ( !file_exists( $archive_template ) )
				$archive_template = trailingslashit( WP_INVOICE_DIR ) . 'template/index.php';
			
			if ( is_singular() ) {
				global $post;
				$this->invoice->invoice_security();
				include( $single_template );
				die;
			}
			else {
				include( $archive_template );
				die;
			}
		}
		
		return $template;
	}
	
	/**
	 * Single Template
	 *
	 * @since 2.0.0
	 */
	function single_template( $single_template ) {
		global $post;
		
		if ( $post->post_type == 'invoice' )
			$single_template = trailingslashit( WP_INVOICE_DIR ) . 'template/single.php';
			
		return $single_template;
	}
	
	/**
	 * Register Invoice Sidebar(s)
	 *
	 * @since 2.0.0
	 */
	function register_sidebar() {
		register_sidebar( array(
			'name'			=> 	_x( 'Invoice Sidebar', 'sidebar', 'wp-invoice-pro' ),
			'description' 	=> 	__( 'The bottom sidebar located on the invoice/quote archive page next to the search form.', 'wp-invoice-pro' ),
			'id'			=> 'invoice-sidebar',
			'before_widget'	=> '<div id="%1$s" class="widget %2$s widget-%2$s"><div class="widget-wrap widget-inside">',
			'after_widget'	=> '</div></div>',
			'before_title'	=> '<h3 class="widget-title">',
			'after_title'	=> '</h3>'
		));
	}
	
	
	/** 
	 * Registers settings section and fields
 	 */
    function admin_init() {
				
        $sections = array(
            array(
                'id'	=> 'wp_invoice_pro_settings',
                'title' => __( 'General Settings', 'wp-invoice-pro' )
            ),
        );
		
		// Country arra()
		$country = array();
		$countries = wp_invoice_get_countries();
		foreach( $countries as $key => $value )
			$country[$key] = $value['name'] . ' (' . $value['currency']['code'] . ')';
		
		// Gateway array()
		$gateway = array( 'None' => __( 'None', 'wp-invoice-pro' ) );
		$gateways = $this->get_payment_gateways();
		foreach( $gateways as $value )
			$gateway[$value] = $value;
			
        $fields = array(
            'wp_invoice_pro_settings' => array(
                array(
                    'name' 		=> 'currency',
                    'label' 	=> __( 'Currency', 'wp-invoice-pro' ),
                    'desc' 		=> __( 'This is used throughout the theme', 'wp-invoice-pro' ),
                    'type' 		=> 'select',
                    'options' 	=> $country,
					'default'	=> 'US',
                ),
                array(
                    'name' 		=> 'tax',
                    'label' 	=> __( 'Tax', 'wp-invoice-pro' ),
                    'desc' 		=> __( 'Enter Tax Amount (5% = .05).', 'wp-invoice-pro' ),
                    'type' 		=> 'text',
					'size'		=> 'small',
                    'default' 	=> '',
					'sanitize_callback' => 'floatval',
					'default'	=> '0.00',
                ),
                array(
                    'name' 		=> 'emailrecipients',
                    'label' 	=> __( 'Send Invoice', 'wp-invoice-pro' ),
                    'desc' 		=> __( 'Select invoice recipients.', 'wp-invoice-pro' ),
                    'type' 		=> 'select',
                    'options' 	=> array(
						'client'=> __( 'Send Invoice to Client Only', 'wp-invoice-pro' ),
						'both'	=> __( 'Send Invoice to Client &amp; Me', 'wp-invoice-pro' ),
					),
                ),
				array(
					'name' 		=> 'email_content',
                    'label' 	=> __( 'Email Content', 'wp-invoice-pro' ),
                    'desc' 		=> __( 'This is what will display above the invoice/quote link when you e-mail it through the admin screen.', 'wp-invoice-pro' ),
					'type' 		=> 'textarea',
					'sanitize_callback' => 'stripslashes_deep', //Allow HTML
				),
                array(
                    'name' 		=> 'permalink',
                    'label' 	=> __( 'Permalinks<br><span class="description">Encoded is more secure.</span>', 'wp-invoice-pro' ),
                    'desc' 		=> null,
                    'type' 		=> 'radio',
                    'options' 	=> array(
						'encoded'	=> __( 'Encoded', 'wp-invoice-pro' ),
						'standard'	=> __( 'Standard', 'wp-invoice-pro' ),
					),
					'default'	=> 'encoded',
                ),
                array(
                    'name' 		=> 'content_editor',
                    'label' 	=> __( 'Content Editor<br><span class="description">Add content to your invoice.</span>', 'wp-invoice-pro' ),
                    'desc' 		=> null,
                    'type' 		=> 'radio',
                    'options' 	=> array(
						'enabled'	=> __( 'Enabled', 'wp-invoice-pro' ),
						'disabled'	=> __( 'Disabled', 'wp-invoice-pro' ),
					),
					'default'	=> 'enabled',
                ),
                array(
                    'name' 		=> 'payment_gateway',
                    'label' 	=> __( 'Payment Gateway', 'wp-invoice-pro' ),
                    'desc' 		=> __( 'Let clients pay invoice\'s online.', 'wp-invoice-pro' ),
                    'type' 		=> 'select',
                    'options' 	=> $gateway,
                ),
                array(
                    'name' 		=> 'payment_gateway_account',
                    'label' 	=> __( 'Email', 'wp-invoice-pro' ),
                    'desc' 		=> __( 'Enter your account email.', 'wp-invoice-pro' ),
                    'type' 		=> 'text',
					'size'		=> 'medium',
                    'default' 	=> '',
                ),
                array(
                    'name' 		=> 'payment_gateway_fee',
                    'label' 	=> __( 'Fee', 'wp-invoice-pro' ),
                    'desc' 		=> __( 'Additional process fee. (2.9% = 0.029).', 'wp-invoice-pro' ),
                    'type' 		=> 'text',
					'size'		=> 'small',
                    'default' 	=> '0.00',
					'sanitize_callback' => 'floatval',
                ),
                array(
                    'name' 		=> 'paypal_page_style',
                    'label' 	=> __( 'PayPal Page Style', 'wp-invoice-pro' ),
                    'desc' 		=> __( 'Enter the name of the page style to use, or leave blank for default', 'wp-invoice-pro' ),
                    'type' 		=> 'text',
					'size'		=> 'medium',
                    'default' 	=> '',
                ),
                array(
                    'name' 		=> 'stripe_test_live',
                    'label' 		=> __( 'Stripe live/test', 'wp-invoice-pro' ),
                    'desc' 		=> __( 'Select (on) for test payments. Off (unselected) for live.', 'wp-invoice-pro' ),
                    'type' 		=> 'checkbox',
                ),
                array(
                    'name' 		=> 'stripe_secret_test',
                    'label' 		=> __( 'Stripe Secret Key (test)', 'wp-invoice-pro' ),
                    'desc' 		=> sprintf( __( 'Enter your <a href="%s" target="_blank">Stripe</a> test Secret Key.', 'wp-invoice-pro' ), 'https://stripe.com/' ),
                    'type' 		=> 'text',
					'size'			=> 'medium',
                    'default' 	=> '',
                ),
                array(
                    'name' 		=> 'stripe_secret_test',
                    'label' 	=> __( 'Stripe Secret Key (test)', 'wp-invoice-pro' ),
                    'desc' 		=> sprintf( __( 'Enter your <a href="%s" target="_blank">Stripe</a> test Secret Key.', 'wp-invoice-pro' ), 'https://stripe.com/' ),
                    'type' 		=> 'text',
					'size'		=> 'large',
                    'default' 	=> '',
                ),
                array(
                    'name' 		=> 'stripe_publishable_test',
                    'label' 	=> __( 'Stripe Publishable Key (test)', 'wp-invoice-pro' ),
                    'desc' 		=> sprintf( __( 'Enter your <a href="%s" target="_blank">Stripe</a> test Publishable Key.', 'wp-invoice-pro' ), 'https://stripe.com/' ),
                    'type' 		=> 'text',
					'size'		=> 'large',
                    'default' 	=> '',
                ),
                array(
                    'name' 		=> 'stripe_secret',
                    'label' 	=> __( 'Stripe Secret Key', 'wp-invoice-pro' ),
                    'desc' 		=> sprintf( __( 'Enter your <a href="%s" target="_blank">Stripe</a> Secret Key.', 'wp-invoice-pro' ), 'https://stripe.com/' ),
                    'type' 		=> 'text',
					'size'		=> 'large',
                    'default' 	=> '',
                ),
                array(
                    'name' 		=> 'stripe_publishable',
                    'label' 	=> __( 'Stripe Publishable Key', 'wp-invoice-pro' ),
                    'desc' 		=> sprintf( __( 'Enter your <a href="%s" target="_blank">Stripe</a> Publishable Key.', 'wp-invoice-pro' ), 'https://stripe.com/' ),
                    'type' 		=> 'password',
					'size'		=> 'large',
                    'default' 	=> '',
                ),
                array(
                    'name' 		=> 'company',
                    'label' 	=> __( 'Company Name', 'wp-invoice-pro' ),
                    'desc' 		=> __( 'Displayed on the invoice and in the automated e-mail (if you decide to send it).', 'wp-invoice-pro' ),
                    'type' 		=> 'text',
					'size'		=> 'medium',
                    'default' 	=> '',
                ),
				array(
					'name' 		=> 'company_address',
                    'label' 	=> __( 'Address', 'wp-invoice-pro' ),
                    'desc' 		=> '',
					'type' 		=> 'textarea',
					'sanitize_callback' => 'stripslashes_deep', //Allow HTML
					'default'	=> '1234 Frosty Web Designs lane.
					Santa Monica, CA 90403
					http://frostywebdesigns.com',
				),
                array(
                    'name' 		=> 'company_phone',
                    'label' 	=> __( 'Company Phone', 'wp-invoice-pro' ),
                    'desc' 		=> '',
                    'type' 		=> 'text',
					'size'		=> 'medium',
                    'default' 	=> '',
                ),
                array(
                    'name' 		=> 'email',
                    'label' 	=> __( 'Email', 'wp-invoice-pro' ),
                    'desc' 		=> __( 'Also appears as "sent from" in emails.', 'wp-invoice-pro' ),
                    'type' 		=> 'text',
					'size'		=> 'medium',
                    'default' 	=> '',
                ),
                
			),
        );
		
        //set sections and fields
        $this->options->set_sections( $sections );
		$this->options->set_fields( $fields );

        //initialize them
        $this->options->admin_init();
		
		add_action( 'wp_invoice_pro_settings_sidebars', array( $this, 'sidebar' ) );
		
		return $this;
//		wp_die( "Bork! <br><pre>" . print_r( $this->settings_api, true ) . "</pre>" );
    }

    /**
	 * Register the plugin page
	 */
    function admin_menu() {
		$submenu = add_submenu_page( 'edit.php?post_type=invoice', __( 'Options', 'wp-invoice-pro' ), __( 'Options', 'wp-invoice-pro' ), 'manage_options', 'options', array( $this, 'plugin_page' ) );
		
		add_action( 'admin_footer-' . $submenu, array( $this->options, 'inline_jquery' ) );
    }
 
	/**
	 * Display the plugin settings options page
	 */
    function plugin_page() {
        echo '<div class="wrap">';

        $this->options->show_navigation();
        $this->options->show_forms();
		?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	var Select = $('select[name="wp_invoice_pro_settings[payment_gateway]"]'),
		// PayPal
		Account_email = $('input[name="wp_invoice_pro_settings[payment_gateway_account]"]'),
		PayPal_style = $('input[name="wp_invoice_pro_settings[paypal_page_style]"]'),
		
		// Stripe
		Stripe_test_live = $('input[name="wp_invoice_pro_settings[stripe_test_live]"]'),
		Stripe_secret = $('input[name="wp_invoice_pro_settings[stripe_secret]"]'),
		Stripe_publish = $('input[name="wp_invoice_pro_settings[stripe_publishable]"]');
		Stripe_secret_t = $('input[name="wp_invoice_pro_settings[stripe_secret_test]"]'),
		Stripe_publish_t = $('input[name="wp_invoice_pro_settings[stripe_publishable_test]"]');
		
	function wp_invoice_payment_gateway_switch() {	
		var Gateway = Select.val();
		
		if ( 'None' === Gateway ) {
			//PayPal
			$(Account_email).parent().parent().hide();
			$(PayPal_style).parent().parent().hide();
			
			//Stripe
			$(Stripe_test_live).parent().parent().parent().hide();
			$(Stripe_secret).parent().parent().hide();
			$(Stripe_publish).parent().parent().hide();
			$(Stripe_secret_t).parent().parent().hide();
			$(Stripe_publish_t).parent().parent().hide();
		}
		else if ( 'PayPal' === Gateway ) {
			//PayPal
			$(Account_email).parent().parent().show();
			$(PayPal_style).parent().parent().show();
			
			//Stripe
			$(Stripe_test_live).parent().parent().parent().hide();
			$(Stripe_secret).parent().parent().hide();
			$(Stripe_publish).parent().parent().hide();
			$(Stripe_secret_t).parent().parent().hide();
			$(Stripe_publish_t).parent().parent().hide();
		}
		else if ( 'Stripe' === Gateway ) {
			//PayPal
			$(Account_email).parent().parent().show();
			$(PayPal_style).parent().parent().hide();
			
			//Stripe
			$(Stripe_test_live).parent().parent().parent().show();
			$(Stripe_secret).parent().parent().show();
			$(Stripe_publish).parent().parent().show();
			$(Stripe_secret_t).parent().parent().show();
			$(Stripe_publish_t).parent().parent().show();
		}
	}
	
	function wp_invoice_stripe_test_live_toggle(el) {
		el = typeof el === "undefined" ? Stripe_test_live : el;
		
		if ( $(el).is(':checked') ) {
			$(Stripe_secret).parent().parent().hide();
			$(Stripe_publish).parent().parent().hide();
			$(Stripe_secret_t).parent().parent().show();
			$(Stripe_publish_t).parent().parent().show();
		}
		else {
			$(Stripe_secret).parent().parent().show();
			$(Stripe_publish).parent().parent().show();
			$(Stripe_secret_t).parent().parent().hide();
			$(Stripe_publish_t).parent().parent().hide();
		}
		
	}
	
	$(Select).chosen().on('change', function() {
		wp_invoice_payment_gateway_switch();
	});
	
	$(Stripe_test_live).on('change', function() {
		wp_invoice_stripe_test_live_toggle( $(this) );
	});
	
	wp_invoice_payment_gateway_switch();
	wp_invoice_stripe_test_live_toggle();
});
</script><?php
        echo '</div>';
		
		if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV && WP_DEBUG ) {
			/**
			echo '<pre data-id="">' . print_r( get_option( 'wp_invoice_pro_settings' ), true ) . '</pre>';
			// */
		}
		
    }

	/**
	 * Sidebar info about this plugin
	 *
	 * @since	2.0
	 * @return	string
	 */
	function sidebar( $args ) {
		$content  = '<ul class="social">';
		$content .= '<li class="donate"><span class="genericon genericon-user"></span>&nbsp;Care to <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=X4JPT57AWMTYW">' . __( 'buy me a beer', 'wp-invoice-pro' ) . '</a>?</li>';
		$content .= '<li class="share"><span class="genericon genericon-twitter"></span>&nbsp;<a href="https://twitter.com/intent/tweet?text=Check out WordPress Invoice Pro by @WPExtendd http://extendd.com/plugin/wordpress-invoice-pro/">' . __( 'Share this plugin on twitter', 'wp-invoice-pro' ) . '</a></li>';
		$content .= '<li class="addons"><span class="genericon genericon-link"></span>&nbsp;<a href="http://extendd.com/plugins/">' . __( 'More plugins on Extendd.com', 'wp-invoice-pro' ) . '</a></li>';
		$content .= '</ul>';
		
		$this->options->postbox( 'wp_invoice_sidebar', sprintf( __( '<a href="%s">%s</a> | <code>version %s</code>', 'wp-invoice-pro' ), 'http://extendd.com/plugin/wordpress-invoice-pro', ucwords( str_replace( '-', ' ', 'wp-invoice-pro' ) ), $this->version ), $content, true );
	}
	
	/**
	 * Get Payment Gateways
	 *
	 * @since 2.0.1
	 *
	 **/
	function get_payment_gateways() {
		$plugins = array();
		$gateways_path = trailingslashit( $this->dir ) . 'functions/gateways/';
		
		if ( !$gateways_path ) return;
		
		$files = array_diff( scandir( $gateways_path ), array( '.', '..', '_notes' ) ); 
		if ( $files )
		{
			foreach( $files as $file )
			{
				if ( is_dir( $gateways_path . $file ) ) break;						// cancel out the folders
				
				$file_contents = file_get_contents( $gateways_path . $file );		// 1. Reads file
				
				preg_match( '|@gateway (.*)$|mi', $file_contents, $matches );		// 2. Finds Tempalte Name, stores in $matches
				
				if ( !empty( $matches[1] ) )
				{
					$plugins[] = $matches[1]; 										// 3. Adds array ([name] => array(path, dir) ) 
				}
				
			}
		}
		return $plugins;
	}
		
	/**
	 * Setup the table for additonal fields in the client taxonomy
	 *
	 * @since 1.0.0
	 */
	function client_metadata_setup() {
		global $pagenow;
		
		if ( is_admin() && isset( $_GET['activate'] ) && $pagenow == 'plugins.php' ) {
			global $wpdb;
			
			$charset_collate = '';  
			if ( ! empty( $wpdb->charset ) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty( $wpdb->collate ) )
				$charset_collate .= " COLLATE $wpdb->collate";
		  
			$tables = $wpdb->get_results("show tables like '{$wpdb->prefix}taxonomymeta'");
			
			if ( !count( $tables ) ) {
				$wpdb->query( "
					CREATE TABLE {$wpdb->prefix}taxonomymeta (
					meta_id bigint(20) unsigned NOT NULL auto_increment,
					taxonomy_id bigint(20) unsigned NOT NULL default '0',
					meta_key varchar(255) default NULL,
					meta_value longtext,
					PRIMARY KEY  (meta_id),
					KEY taxonomy_id (taxonomy_id),
					KEY meta_key (meta_key)
					) $charset_collate;"
				);
				
				wp_redirect( admin_url( 'edit.php?post_type=invoice&page=options' ) );
				exit;
			}
		}
	}	
		
	/**
	 * Add shortcodes
	 *
	 * @since 2.1.2
	 */
	function shortcodes() {
		add_shortcode( 'wp-invoice-password',	array( $this, 'get_invoice_password' ), 10, 2 );
	}	
		
	/**
	 * Add 'wp_invoice_password_shortcode'
	 *
	 * @since 2.1.2
	 */
	function get_invoice_password( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'id' => get_the_ID(),
		), $atts ) );
		
		if ( post_password_required( $id ) ) {
			$invoice = get_post( $id );
			return $invoice->post_password;
		}
		else {
			$invoice = get_post( $id );
			return !empty( $invoice->post_password ) ? $invoice->post_password : null;
		}
	}
	
	/**
	 * Filter plugin Restricted Site Access
	 *
	 * @ref		http://wordpress.org/plugins/restricted-site-access/faq/
	 * @since	2.0.1
	 */
	function unrestricted( $is_restricted ) {
		global $wp;
		
		// check query variables to see if this is the feed
		if ( !empty( $wp->query_vars['post_type'] ) && 'invoice' == $wp->query_vars['post_type'] )
			$is_restricted = false;
	
		return $is_restricted;
	}	
	
	/**
	 * Filter the_title
	 *
	 * @since	2.1.2
	 */
	function the_title( $title, $bypass = false ) {
		if ( 'invoice' === get_post_type() || $bypass ) {
			$title = esc_attr( $title );
		
			$findthese = array(
				'#Protected:#',
				'#Private:#'
			);
		
			$replacewith = array(
				'', // What to replace "Protected:" with
				'' // What to replace "Private:" with
			);
		
			$title = preg_replace( $findthese, $replacewith, $title );
		}
		
		return $title;
	}
	
	/**
	 * Helper function
	 */
	protected function print_r( $input ) {
		echo '<pre>';
		print_r( $input );
		echo '</pre>';
	}
	
} // end: wp_invoice_pro

/**
 * Load plugin on plugins_loaded
 */
function WP_INVOICE_PRO() {
	return wp_invoice_pro::instance();
}
add_action( 'plugins_loaded', 'WP_INVOICE_PRO' );