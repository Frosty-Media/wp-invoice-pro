<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Extendd License Settings API wrapper class
 *
 * @updated		10/15/2014
 */
class extendd_settings_api {
	
	var $version = '1.3.0';

    /**
     * Singleton instance
     *
     * @var object
     */
    private static $_instance;

    /**
     * Settings sections array
     *
     * @var array
     */
    private $settings_sections = array();
	
	/**
     * Settings sections array
     *
     * @var array
     */
    private $settings_sidebars = array();

    /**
     * Settings fields array
     *
     * @var array
     */
    private $settings_fields = array();
	
	/**
	 * API URL
     *
     * @var string
     */
	private $api_url; 
	
	/**
	 * Settings page
	 *
	 * @var	string
	 */
	private $settings_page;
	
	/**
	 * Constructor
	 *
	 */
    public function __construct() {
		$this->add_sections();
		$this->add_fields();
		
		/* API URL */
		$this->api_url 		= untrailingslashit( apply_filters( 'extendd_settings_api_url', 'https://extendd.com' ) );
		$this->settings_page 	= apply_filters( 'extendd_settings_settings_page', 'extendd_license_settings' );
 		
		/* Remove each plugin for wp.org */
		add_filter( 'http_request_args',					array( $this, 'hide_plugin_from_wp_repo'	), 5, 2 );
		
		add_action( 'admin_menu', 						array( $this, 'admin_menu' 					) );
		add_action( 'admin_init', 						array( $this, 'admin_init' 					) );
		add_action( 'admin_init', 						array( $this, 'validate_license' 			) ); // Move to load-$page on admin_menu hook.
		add_action( 'admin_notices',						array( $this, 'admin_notices' 				) );
		add_action( 'extendd_settings_sidebars', 		array( $this, 'extendd_version_sidebar'		), 1 ); // Lowest priority to load first
		add_action( 'extendd_settings_sidebars', 		array( $this, 'extendd_plugins_sidebar'		) );
		add_action( 'wp_ajax_extendd-check-license-'
		. $this->settings_page,							array( $this, 'check_license' ) );	
    }
	
	/**
	 * Return the one true instance.
	 */
    public static function instance() {
		if ( ! isset( self::$_instance ) && ! ( self::$_instance instanceof extendd_settings_api ) ) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
	
	/**
	 * Lets hide our plugins from the WordPress repo
	 * in case there is a duplicate plugin with same name.
	 *
	 * @since		1.0.14 (3/20/13)
	 * @updated	1.3.0 (10/15/14)
	 * @ref			http://markjaquith.wordpress.com/2009/12/14/excluding-your-plugin-or-theme-from-update-checks/
	 */
	function hide_plugin_from_wp_repo( $r, $url ) {
		
		$update_check = (bool) strpos( $url, '//api.wordpress.org/plugins/update-check/1.1/' );
		
		// If it's not a plugin update request, bail.
		if ( false === $update_check ) {
 			return $r;
 		}
		
 		// Decode the JSON response
 		$plugins = json_decode( $r['body']['plugins'] );
		
		// Loop through each plugin and remove the active plugin from the check
		foreach ( $this->settings_sections as $plugin ) {
			if ( !isset( $plugin['basename'] ) )
				continue;
			unset( $plugins->plugins->$plugin['basename'] );
			unset( $plugins->active[ array_search( $plugin['basename'], $plugins->active ) ] );
		}
		
 		// Encode the updated JSON response
 		$r['body']['plugins'] = json_encode( $plugins );

 		return $r;
	}
	
	/**
	 * Current plugin has update
	 *
	 * @updated	1.3.0 (10/15/14)
	 * @return		array|bool
	 */
	private function has_update() {
		if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
		//	set_site_transient( 'update_plugins', null );
		}
		$plugins	= get_site_transient( 'update_plugins' );
		$update 	= array();
		
		foreach ( $this->settings_sections as $plugin ) {
			if ( !isset( $plugin['basename'] ) )
				continue;
			if ( !isset( $plugin['version'] ) )
				continue;
			if ( !isset( $plugins->response[ $plugin['basename'] ]->slug ) || !in_array( basename( $plugin['basename'], '.php' ), (array) $plugins->response[ $plugin['basename'] ]->slug ) )
				continue;
			$update[ $plugin['title'] ] = version_compare( $plugin['version'], $plugins->response[ $plugin['basename'] ]->new_version, '<' );
		}
		
		if ( !empty( $update ) ) {
			$update['count'] = count( $update );
			return $update;
		}
		return false;
    }
	
	/**
	 * Output current plugin updates
	 *
	 * @return	string
	 */
	function output_update_count() {
		$has_update = $this->has_update();
		
		return !empty( $has_update ) ? ' <span id="extendd-update" title="' . esc_attr__( 'Update Available', 'extendd' ) . '" class="update-plugins count-' . $has_update['count'] . '"><span class="plugin-count">' . $has_update['count'] . '</span></span>' : '';
	}
	
	/**
     * Add the menu
     *
     * @return	void
     */
    function admin_menu() {
		global $extendd_options_page;	
		$extendd_options_page = add_options_page(
			__( 'Extendd Settings API', 'extendd' ),
			sprintf( __( 'Extendd Settings%s', 'extendd' ),
			$this->output_update_count() ),
			'manage_options',
			$this->settings_page,
			array( $this, 'plugin_page' )
		);
		
		add_action( 'admin_footer-' . $extendd_options_page,	array( $this, 'inline_scripts' ) );	
    }

    /**
     * Set settings sections
     *
     * @param	array   ($sections setting sections array)
     */
    function add_sections( $sections = array() ) {
		$sections = apply_filters( 'extendd_add_settings_sections', $sections );				
		$this->settings_sections = $sections;
    }

    /**
     * Add a single section
     *
     * @param array   $section
     */
    function add_sidebar( $sidebar = array() ) {
		$sidebar = apply_filters( 'extendd_add_settings_sidebar', $sidebar );
		if ( !empty( $sidebar ) ) {
        	$this->settings_sidebars[] = $sidebar;
		}
    }

    /**
     * Set settings fields
     *
     * @param array   $fields settings fields array
     */
	function add_fields( $fields = array() ) {
		$fields = apply_filters( 'extendd_add_settings_fields', $fields );
		$this->settings_fields = $fields;
    }

    /**
     * Initialize and registers the settings sections and fileds to WordPress
     *
     * Usually this should be called at `admin_init` hook.
     *
     * This function gets the initiated settings sections and fields. Then
     * registers them to WordPress and ready for use.
     */
    function admin_init() {

		//register settings sections
		foreach ( $this->settings_sections as $section ) {
			if ( false == get_option( $section['id'] ) ) {
				add_option( $section['id'] );
			}
			
			add_settings_section( $section['id'], $section['title'], '__return_false', $section['id'] );
		}

		//register settings fields
		foreach ( $this->settings_fields as $section => $field ) {
			foreach ( $field as $option ) {
			
				$type = isset( $option['type'] ) ? $option['type'] : 'text';
				
				$args = array(
					'id'		=> $option['name'],
					'desc'		=> isset( $option['desc'] ) ? $option['desc'] : '',
					'name' 		=> $option['label'],
					'section' 	=> $section,
					'size' 		=> isset( $option['size'] ) ? $option['size'] : null,
					'options' 	=> isset( $option['options'] ) ? $option['options'] : '',
					'std' 		=> isset( $option['default'] ) ? $option['default'] : '',
				);
				
				add_settings_field( $section . '[' . $option['name'] . ']', $option['label'], array( $this, 'callback_' . $type ), $section, $section, $args );
			}
		}

		// creates our settings in the options table
		foreach ( $this->settings_sections as $section ) {
			register_setting( $section['id'], $section['id'], array( $this, 'validate_settings' ) );
		}
    }
	
	/**
	 * Validate the settings
	 *
	 * Modify the &updated=true text
	 *
	 * @return	void
	 */
	function validate_settings( $input ) {
		foreach ( $this->settings_sections as $section ) {
			$old = $this->get_option( 'license_active', $section['id'], '' );
			
			if ( empty( $input['license_key'] ) ) {
				add_settings_error( 'extendd-notices', 'extendd-empty-key', __( 'No license key has been entered.', 'extendd' ), 'error' );
				return $input;
			}
			if ( isset( $old ) && $old === $input['license_key'] ) {
				add_settings_error( 'extendd-notices', 'extendd-already-active', __( 'This license key has already been entered.', 'extendd' ), 'error' );
				return $input;
			}
		}
		return $input;
	}
	
	/**
	 * Validate the license on form submission
	 *
	 * Loops through each registered settings_sections and if set calls
	 * our API and checks if the key is valid.
	 *
	 * @since		12/18/12
	 * @updated	1/30/14
	 * @return		string
	 */
	function validate_license() {
		
		foreach ( $this->settings_sections as $section ) {
			
			// Get the settings for the extenstion
			$settings = get_option( $section['id'], array() );
			
			if ( !isset( $_POST[ $section['id'] ] ) )
				continue;
			
			if ( !isset( $_POST[ $section['id'] ]['license_key'] ) || empty( $_POST[ $section['id'] ]['license_key'] ) )
				continue;
			
			$edd_action = isset( $_POST[ $section['id'] ]['license_deactivate'] ) && 'on' === $_POST[ $section['id'] ]['license_deactivate'] ? 'deactivate' : 'activate';
			
        	$is_valid	= $settings['license_active'];
			
			// No need to activate if already valid
			if ( 'activate' === $edd_action && 'valid' === strtolower( trim( $is_valid ) ) )
				continue;
			
			// No need to deactivate if already deactivated
			if ( 'deactivate' === $edd_action && 'deactivated' === strtolower( trim( $is_valid ) ) ) {
				if ( isset( $settings['license_deactivate'] ) )
					unset( $settings['license_deactivate'] );
					
				update_option( $section['id'], $settings );
				delete_transient( $section['id'] . '_license_check' );
				continue;
			}
		
			$license = sanitize_text_field( $_POST[$section['id']]['license_key'] );
			
			// data to send in our API request
			$api_params = array( 
				'edd_action'	=> $edd_action . '_license', 
				'license'		=> $license, 
				'item_name'	=> urlencode( $section['title'] ) // the name of our product in EDD
			);
			
			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, $this->api_url ), array( 'timeout' => 15, 'sslverify' => false ) );
			
			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				wp_redirect( $this->get_settings_url( 'response_error' ) );
				exit;
			}
		
			// Decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			 
			$settings['license_key'] 		= trim( $license );
			$settings['license_active']	= trim( $license_data->license );
			
			if ( isset( $settings['license_deactivate'] ) )
				unset( $settings['license_deactivate'] );
			
			// Update the settings
			update_option( $section['id'], $settings );
			delete_transient( $section['id'] . '_license_check' );
			
			if ( 'valid' === trim( $license_data->license ) ) {		
				// Unset vars
				unset( $response, $license_data, $settings );
				wp_redirect( $this->get_settings_url( 'valid_api_key', urlencode( $section['title'] ) ) );
				exit;
			} elseif ( 'failed' === trim( $license_data->license ) ) {		
				// Unset vars
				unset( $response, $license_data, $settings );
				wp_redirect( $this->get_settings_url( 'failed_to_deactivate', urlencode( $section['title'] ) ) );
				exit;
			} elseif ( 'deactivated' === trim( $license_data->license ) ) {		
				// Unset vars
				unset( $response, $license_data, $settings );
				wp_redirect( $this->get_settings_url( 'api_key_deactivate', urlencode( $section['title'] ) ) );
				exit;
			} else {	
				// Unset vars
				unset( $response, $license_data, $settings );
				wp_redirect( $this->get_settings_url( 'invalid_api_key', urlencode( $section['title'] ) ) );
				exit;
			}
		}
	
	}	
	
	/**
	 * Validate the license on form submission
	 *
	 * Loops through each registered settings_sections and if set calls
	 * our API and checks if the key is valid.
	 *
	 * @since		12/18/12
	 * @updated	1/30/14
	 * @return		string
	 */
	function check_license() {
		
		check_ajax_referer( $this->settings_page, 'extendd_nonce' );
		
		if ( !isset( $_REQUEST['section'] ) )
			die('error');
			
		// Get the settings for the extenstion
       $license	= trim( $this->get_option( 'license_key', $_REQUEST['section'], '' ) );
		$code		= null;
		$cache		= true;
		
		if ( empty( $license ) )
			die('empty');
		
		// data to send in our API request
		$api_params = array( 
			'edd_action'	=> 'check_license', 
			'license' 		=> $license, 
			'item_name' 	=> urlencode( $_REQUEST['title'] ) // the name of our product in EDD
		);
		
		if ( false === ( $license_data = get_transient( $_REQUEST['section'] . '_license_check' ) ) ) :
			
			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, $this->api_url ), array( 'timeout' => 15, 'sslverify' => false ) );
			
			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				$code	= 'error';
				$cache	= false;
			}
			
			if ( is_null( $code ) && $cache ) {
		
				// Decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
				 
				$settings['license_key']		= $license;
				$settings['license_active']	= 'valid' === trim( $license_data->license ) ? trim( $license_data->license ) : 'expired';
				
				// Update the settings
				update_option( $_REQUEST['section'], $settings );
				
				if ( 'valid' === trim( $license_data->license ) ) {
					// Unset vars
					unset( $response, $license_data, $settings );
					$code = "valid";
				} else {	
					// Unset vars
					unset( $response, $license_data, $settings );
					$code = "invalid";
				}
				
				// Cache the response
				set_transient( $_REQUEST['section'] . '_license_check', $code, WEEK_IN_SECONDS * 3 );
//				delete_transient( $_REQUEST['section'] . '_license_check' );
				
			} // $code & $cache
		
		endif; // $license_data
		
		die( $license_data );	
	}
	
	/**
	 * Settings URL
	 *
	 * @access      private
	 * @since       1.0 
	 * @return      void
	 */
	function get_settings_url( $error = '', $extra_param = null ) {
		$extra_param = !is_null( $extra_param ) ? '&param=' . $extra_param : '';
		return admin_url( "/options-general.php?page={$this->settings_page}&extendd-message={$error}{$extra_param}" );
	}
	
	/**
	 * Admin Messages
	 *
	 * @access      private
	 * @since       1.0 
	 * @return      void
	 */	
	function admin_notices() {
		$plugin = isset( $_GET['param'] ) ? $_GET['param'] : '';
		
		if ( isset( $_GET['extendd-message'] ) && $_GET['extendd-message'] == 'response_error' ) {
			add_settings_error( 'extendd-notices', 'extendd-remote-api-fail', __( 'There was an error connecting to extendd.com/. Please try again at another time.', 'extendd' ), 'error' );
		}
		if ( isset( $_GET['extendd-message'] ) && $_GET['extendd-message'] == 'valid_api_key' ) {
			add_settings_error( 'extendd-notices', 'extendd-empty-key', sprintf( __( '%s license activated.', 'extendd' ), urldecode( $plugin ) ), 'updated' );
		}
		if ( isset( $_GET['extendd-message'] ) && $_GET['extendd-message'] == 'invalid_api_key' ) {
			add_settings_error( 'extendd-notices', 'extendd-empty-key',  sprintf( __( 'The %s license key is not valid. Please check you have entered the correct license.', 'extendd' ), urldecode( $plugin ) ), 'error' );
		}
		if ( isset( $_GET['extendd-message'] ) && $_GET['extendd-message'] == 'failed_to_deactivate' ) {
			add_settings_error( 'extendd-notices', 'extendd-failed',  sprintf( __( 'The %s license key failed to deactivate. It is either not a valid key, not currently active or expired.', 'extendd' ), urldecode( $plugin ) ), 'error' );
		}
		if ( isset( $_GET['extendd-message'] ) && $_GET['extendd-message'] == 'api_key_deactivate' ) {
			add_settings_error( 'extendd-notices', 'extendd-deactivated',  sprintf( __( 'The %s license key has been deactivated.', 'extendd' ), urldecode( $plugin ) ), 'updated' );
		}
		if ( isset( $_GET['extendd-message'] ) && $_GET['extendd-message'] == 'empty_key' ) {
			add_settings_error( 'extendd-notices', 'extendd-empty-key', __( 'No license key has been entered.', 'extendd' ), 'updated' );
		}
	}

    /**
     * Displays a text field for a settings field
     *
     * @param 	array   $args settings field args
	 * @return	string
     */
    function callback_text( $args ) {
		$has_update = $this->has_update();
		
		$value	 = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size	 = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
		
		$status = $this->get_option( 'license_active', $args['section'], '' );
		$status = !empty( $status ) ? $status : 'inactive';
		$style  = $class = '';
		
		switch ( $status ) {
			case 'valid':
				$style = ' style="background-color:#F5FFE8; border-color:#00CC2D;"';
				$class = ' license-valid';
				break;
			case 'invalid':
			case 'expired':
				$style = ' style="background-color:#FFEBE8; border-color:#CC0000;"';
				$class = ' license-invalid';
				break;
			case 'deactivated':
				$style = ' style="background-color:#F5FCFC; border-color:#91D0DE;"';
				$class = ' license-deactivated';
				break;
			case 'failed':
				$style = ' style="background-color:#FFEBE8; border-color:#CC0000;"';
				$class = ' license-failed';
				break;
			case 'inactive':
				$class = ' license-inactive';
				break;
		}
		
		$html  = sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"%5$s/>', $size, $args['section'], $args['id'], $value, $style );
		$html .= sprintf( '<br><span class="description"> %s</span>', $args['desc'] );
		
		$html .= sprintf( '&nbsp;<span class="license-status%s">&nbsp;( %s )</span>', $class, $status );
		
		if ( !empty( $has_update ) ) {
			$title = array();
			foreach ( $has_update as $key => $value ) {
				if ( 'count' === $key ) continue;
				$title[] = 'extendd_' . strtolower( str_replace( ' ', '_', $key ) );
			}
			if ( in_array( $args['section'], $title ) ) {
				$html .= sprintf( '<br><h4>An update to this plugin is available, please auto-update or visit "Your Account" on <a href="%s">Extendd.com</a> to download the latest copy.</h4>', 'http://extendd.com/my-account/' );
			}
		}

        echo $html;
    }

    /**
     * Displays a hidden field for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_hidden( $args ) {

        $value	= esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );

        $html	= sprintf( '<input type="hidden" id="%1$s[%2$s]" name="%1$s[%2$s]" value="%3$s"/>', $args['section'], $args['id'], $value );

        echo $html;
    }

    /**
     * Displays a checkbox for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_checkbox( $args ) {

        $value	= esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );

        $html	= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="on"%4$s />', $args['section'], $args['id'], $value, checked( $value, 'on', false ) );
        $html .= sprintf( '<label for="%1$s[%2$s]"> %3$s</label>', $args['section'], $args['id'], $args['desc'] );

        echo $html;
    }

    /**
     * Displays a textarea for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_html( $args ) {
        echo $args['desc'];
    }

    /**
     * Get the value of a settings field
     *
     * @param string  $option  settings field name
     * @param string  $section the section name this field belongs to
     * @param string  $default default text if it's not found
     * @return string
     */
    function get_option( $option, $section, $default = '' ) {

        $options = get_option( $section );

        if ( isset( $options[$option] ) ) {
            return $options[$option];
        }

        return $default;
    }

    /**
     * Show navigations as tab
     *
     * Shows all the settings section labels as tab
     */
    function show_navigation() {
		
        $html = '<h2 class="nav-tab-wrapper">';

        foreach ( $this->settings_sections as $tab ) {
            $html .= sprintf( '<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab['id'], str_replace( 'WordPress', '', $tab['title'] ) );
        }

        $html .= '</h2>';

        echo $html;
    }

    /**
     * Show the section settings forms
     *
     * This function displays every sections in a different form
     */
    function show_forms() {
		?>
        <style type="text/css">
		.section{clear:both;margin:0;padding:0}
		.section *, .section *:before, .section *:after{box-sizing:border-box}
		.section pre{overflow:auto;white-space:pre-wrap;word-wrap:break-word;}
		.col{display:block;float:left;margin:1% 0 1% 1.6%}
		.col:first-child{margin-left:0}
		.col-group:before,.col-group:after{content:"";display:table}
		.col-group:after{clear:both}
		.span_3_of_3{width:100%}
		.span_2_of_3{width:66.1%}
		.span_1_of_3{width:32.2%}
		span.license-valid{color:#00CC2D;}
		span.license-invalid{color:#CC0000;}
		span.license-deactivated{color:#91D0DE;}
		span.license-failed{color:#CC0000;}
		span.license-inactive{color:#999;}
		</style>
        <div class="section col-group">
            <div class="metabox-holder col span_2_of_3">
                <?php foreach ( $this->settings_sections as $form ) { ?>
                    <div id="<?php echo $form['id']; ?>" class="group">
                    	<div class="postbox">
                        <form method="post" action="options.php">

                            <?php settings_fields( $form['id'] ); ?>
                            <?php do_settings_sections( $form['id'] ); ?>

                            <div style="padding-left: 10px">
                            	<p>
									<?php submit_button( __( 'Activate', 'extedd' ), 'primary', 'submit', false ); ?>
                                    
                                    <a href="#" data-license-title="<?php echo $form['title']; ?>" data-license-id="<?php echo $form['id']; ?>" class="button" id="<?php echo $form['id']; ?>_license_check"><?php _e( 'Check License Status', 'extedd' ); ?></a>
                                    
                                    <span class="description license-description">
											<img width="16" height="16" alt="" src="<?php echo admin_url( 'images/wpspin_light-2x.gif' ); ?>" style="display:none; margin:0px 0 -3.5px 5px;">
											<span></span>
                                    </span>
                                </p>
                            </div>
                        </form> 
                        </div>
                        
                        <div class="metabox-holder">
                            <div class="postbox">
                            <h3><?php printf( __( '%s <strong>Changelog</strong>' ), $form['title'] ); ?></h3>
                            <?php $this->get_plugin_changelog( $form ); ?>
                            </div>
                        </div>                       
                    </div>
                <?php } ?>
            </div>
	        <div class="col span_1_of_3">
                <?php do_action( 'extendd_settings_sidebars', $this->settings_sidebars ); ?>
            </div>
        </div>
        <br class="clear">
        <?php
        if ( defined( 'WP_LOCAL_DEV' ) && ( WP_LOCAL_DEV || WP_DEBUG ) ) {
			foreach ( $this->settings_sections as $section ) {
				//delete_option( $section['id'] );			
				//print '<pre>' . print_r( $this->has_update(), true ) . '</pre>';
				//print '<pre>' . print_r( get_option( $section['id'] ), true ) . '</pre>';
			}
			//print '<pre>' . print_r( get_site_transient( 'update_plugins' ), true ) . '</pre>';
			//print '<pre>' . print_r( dirname( plugin_basename( __FILE__ ) ), true ) . '</pre>';
		}
    }


    function plugin_page() {
		echo '<div class="wrap">';
		//settings_errors( 'extendd-notices' );
		
		$this->show_navigation();
		$this->show_forms();
		
		echo '</div>';
    }
    
	/**
     * Tabbable JavaScript codes
     *
     * This code uses localstorage for displaying active tabs
     */
    function inline_scripts() { ?>
<script>		
// AJAX
var check_license = function( section, title ) {
	var data = {
		action			: 'extendd-check-license-<?php echo $this->settings_page; ?>',
		extendd_nonce	: '<?php echo wp_create_nonce( $this->settings_page ); ?>',
		section			: section,
		title			: title
	}
	jQuery('#'+section).find('span.description.license-description img').fadeIn('fast');
	jQuery.post(ajaxurl, data, function(res) {
		if (res) {
			jQuery('#'+section).find('span.description.license-description img').fadeOut('slow');
			jQuery('#'+section).find('span.description.license-description span').text('');
			
			if (res == 'error') {
				jQuery('#'+section).find('span.description.license-description span').text('<?php _e( 'An unknown error has occured.', 'extendd' ); ?>');
			}
			if (res == 'valid') {
				jQuery('#'+section).find('span.description.license-description span').text('<?php _e( 'Your license for', 'extendd' ); ?> '+title+' <?php _e( 'is valid.', 'extendd' ); ?>');
			}
			if (res == 'invalid') {
				jQuery('#'+section).find('span.description.license-description span').text('<?php _e( 'Your license for', 'extendd' ); ?> '+title+' <?php _e( 'has expired or is invalid. Please renew.', 'extendd' ); ?>');
			}
			if (res == 'empty') {
				jQuery('#'+section).find('span.description.license-description span').text('<?php _e( 'Please enter a valid license key.', 'extendd' ); ?>');
			}
		}				
	});
}
</script>
<script>
	jQuery(document).ready(function($) {
		// Switches option sections
		$('.group').hide();
		var activetab = '';
		if (typeof(localStorage) != 'undefined' ) {
			activetab = localStorage.getItem("activetab");
		}
		if (activetab != '' && $(activetab).length ) {
			$(activetab).fadeIn();
		} else {
			$('.group:first').fadeIn();
		}
		$('.group .collapsed').each(function() {
			$(this).find('input:checked').parent().parent().parent().nextAll().each(
			function(){
				if ($(this).hasClass('last')) {
					$(this).removeClass('hidden');
					return false;
				}
				$(this).filter('.hidden').removeClass('hidden');
			});
		});

		if (activetab != '' && $(activetab + '-tab').length ) {
			$(activetab + '-tab').addClass('nav-tab-active');
		}
		else {
			$('.nav-tab-wrapper a:first').addClass('nav-tab-active');
		}
		$('.nav-tab-wrapper a').click(function(e) {
			$('.nav-tab-wrapper a').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active').blur();
			var clicked_group = $(this).attr('href');
			if (typeof(localStorage) != 'undefined' ) {
				localStorage.setItem("activetab", $(this).attr('href'));
			}
			$('.group').hide();
			$(clicked_group).fadeIn();
			e.preventDefault();
		});
		
		// Fix for .inside wrapper
		$('form > table.form-table').wrap('<div class="inside"/>');
		
		// License status
		$('.group .license-status').each(function(i, e) {
			//$(this).clone().appendTo( $(this).closest('tr').find('th') );
		});
		
		// Hide input[type="hidde"] table > tr
		$('.group input[type="hidden"]').each(function(i, e) {
			$(this).closest('tr').hide();
		});		
		
		// Check License status AJAX
		$('.group a[data-license-id]').each(function(index, element) {
			$(this).on('click', function(e) {
				e.preventDefault();
				check_license( $(this).data('licenseId'), $(this).data('licenseTitle') ); //AJAX
			});
		});
			
	});
</script><?php
    }

	/**
	 * Create a potbox widget.
	 *
	 * @param 	string $id      ID of the postbox.
	 * @param 	string $title   Title of the postbox.
	 * @param 	string $content Content of the postbox.
	 */
	function postbox( $id, $title, $content ) {
		?>
        <div class="metabox-holder" id="<?php echo $id; ?>">
            <div class="postbox">
            <h3><?php echo $title; ?></h3>
            <div class="inside"><?php echo $content; ?></div>
            </div>
        </div>
        <?php
	}
	
	/**
	 * Fetch RSS items from the feed.
	 *
	 * @param 	int    $num  Number of items to fetch.
	 * @param 	string $feed The feed to fetch.
	 * @return 	array|bool False on error, array of RSS items on success.
	 */
	public function fetch_rss_items( $num, $feed, $cache_time = true ) {
		if ( !function_exists( 'fetch_feed' ) ) {
			include_once( ABSPATH . WPINC . '/feed.php' );
		}
		
		if ( $cache_time ) {
			add_filter( 'wp_feed_cache_transient_lifetime', create_function( '', 'return WEEK_IN_SECONDS;' ) );
		}
		
		// Fetch
		$rss = fetch_feed( $feed );
		
		if ( $cache_time )
			remove_all_filters( 'wp_feed_cache_transient_lifetime' );

		// Bail if feed doesn't work
		if ( !$rss || is_wp_error( $rss ) )
			return false;

		$rss_items = $rss->get_items( 0, $rss->get_item_quantity( $num ) );

		// If the feed was erroneous 
		if ( !$rss_items ) {
			$md5 = md5( $feed );
			delete_transient( 'feed_' . $md5 );
			delete_transient( 'feed_mod_' . $md5 );
			$rss       = fetch_feed( $feed );
			$rss_items = $rss->get_items( 0, $rss->get_item_quantity( $num ) );
		}

		return $rss_items;
	}
	
	/**
	 * Box with current API version.
	 * 
	 * Pings the Extendd.com site with $_GET peramater.
	 * @return	transient array
	 * @since	12/10/12
	 */
	function extendd_version_sidebar() {		
		if ( false === ( $settings_api = get_transient( 'extendd_settings_api_version' ) ) ) {
			$site = wp_remote_get( 'http://extendd.com/?extendd-settings-api-version=true', array( 'timeout' => 15, 'sslverify' => false ) );
			if ( !is_wp_error( $site ) ) {
//				print '<pre>' . print_r( $settings_api, true ) . '</pre>';
				if ( isset( $site['body'] ) && strlen( $site['body'] ) > 0 ) {
					$settings_api = json_decode( wp_remote_retrieve_body( $site ) );
					set_transient( 'extendd_settings_api_version', $settings_api, WEEK_IN_SECONDS ); // Cache for a week
				}
			}
		}
		
		$content  = '<ul>';
		$content .= '<li>' . sprintf( __( 'Current Version: <code>%s</code>', 'extendd' ), $this->version ) . '</li>';
		$content .= '<li>' . sprintf( __( 'Latest Version: <code>%s</code>', 'extendd' ), !empty( $settings_api->version ) ? $settings_api->version : __( 'Error', 'extendd' ) ) . '</li>';
		$content .= '</ul>';
		$content .= !empty( $settings_api->message ) ? '<p>' . $settings_api->message . '</p>' : '';
		$this->postbox( 'extendd-settings-api-latest', __( 'Extendd Settings API version', 'extendd' ), $content );
	}


	/**
	 * Box with latest plugins from Extendd.com for sidebar
	 */
	function extendd_plugins_sidebar( $args ) {
		
		$defaults = array(
			'items' => 6,
			'feed' 	=> 'http://extendd.com/feed/?post_type=download',
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		$rss_items = $this->fetch_rss_items( $args['items'], $args['feed'] );
		
		$content = '<ul>';
		if ( !$rss_items ) {
			$content .= '<li>' . __( 'Error fetching feed', 'extendd' ) . '</li>';
		} else {
			foreach ( $rss_items as $item ) {
				$url = preg_replace( '/#.*/', '', esc_url( $item->get_permalink(), null, 'display' ) );
				$content .= '<li>';
				$content .= '<a class="rsswidget" href="' . $url . '#utm_source=wpadmin&utm_medium=sidebarwidget&utm_term=newsitem&utm_campaign=extenddsettingsapi">' . esc_html( $item->get_title() ) . '</a> ';
				$content .= '</li>';
			}
		}
		$content .= '</ul>';
		$content .= '<ul class="social">';
			$content .= '<li><span class="genericon genericon-facebook"></span> <a href="https://www.facebook.com/WPExtendd">' . __( 'Like Extendd on Facebook', 'extendd' ) . '</a></li>';
			$content .= '<li><span class="genericon genericon-twitter"></span> <a href="http://twitter.com/WPExtendd">' . __( 'Follow Extendd on Twitter', 'extendd' ) . '</a></li>';
			$content .= '<li><span class="genericon genericon-twitter"></span> <a href="http://twitter.com/TheFrosty">' . __( 'Follow Austin on Twitter', 'extendd' ) . '</a></li>';
			$content .= '<li><span class="genericon genericon-googleplus"></span> <a href="https://plus.google.com/113609352601311785002/">' . __( 'Circle Extendd on Google+', 'extendd' ) . '</a></li>';
			$content .= '<li><span class="genericon genericon-mail"></span> <a href="http://eepurl.com/vi0bz">' . __( 'Subscribe via email', 'extendd' ) . '</a></li>';
		$content .= '</ul>';
		
		$this->postbox( 'extenddlatest', sprintf( __( 'Latest plugins from <a href="%s">%s</a>', 'extendd' ), 'http://extendd.com', 'Extendd.com' ), $content );
	}
	
	/**
	 * Output changelog if one exists
	 *
	 * $array = Array
		(
			[id] 		=> extendd_(string)
			[title] 	=> (string)
			[basename]	=> folder-name/file-name.php
			[version]	=> (int)
		)
	 *
	 * @return string|bool
	 */
	private function get_plugin_changelog( $array = array() ) {
		
		if ( false === ( $changelog = get_transient( $array['id'] . '_changelog' ) ) ) {
			$changelog = trailingslashit( WP_PLUGIN_DIR ) . plugin_dir_path( $array['basename'] ) . 'changelog.txt';
			if ( file_exists( $changelog ) ) {
				$changelog = file_get_contents( $changelog );
				set_transient( $array['id'] . '_changelog', $changelog, WEEK_IN_SECONDS ); // Cache for a week
			}
		}
		
		if ( $changelog ) { ?>
            <div id="<?php echo $array['id']; ?>_changelog" class="inside">
                <pre><?php echo $changelog; ?></pre>
            </div><?php
		}
	}

}

/**
 * The main function responsible for returning the one true extendd_settings_api Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $extendd_settings_api = EXTENDD_settings_init(); ?>
 *
 * @since v1.0.11
 *
 * @return The one true extendd_settings_api Instance
 */
function EXTENDD_settings_init() {
	return extendd_settings_api::instance();
}
add_action( 'init', 'EXTENDD_settings_init', 99 );