<?php

if ( !is_admin() ) return;

/**
 * Master theme class
 * 
 * @since 1.0
 */
class wp_invoice_settings {
	
	var $parent,
		$page;
	
	private $sections;
	private $checkboxes;
	private $settings;
	
	public function __construct( $parent ) {
		
		$this->parent = $parent;
		$this->page = 'settings';
		
		/* This will keep track of the checkbox options for the validate_settings function. //*/
		$this->checkboxes	= array();
		$this->settings		= array();
		$this->get_settings();
		
		$this->sections['general']   	= __( 'General', 'wp-invoice' );
		$this->sections['company']      = __( 'Company', 'wp-invoice' );
		$this->sections['news']			= __( 'News', 'wp-invoice' );
		$this->sections['changelog']	= __( 'Changelog', 'wp-invoice' );
		
		add_action( 'admin_menu', array( $this, 'add_pages' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		
		if ( !get_option( $this->parent->settings_name ) )
			$this->initialize_settings();		
	}
	
	/**
	 * Initialize settings to their default values
	 * 
	 */
	public function initialize_settings() {
		
		$default_settings = array();
		foreach ( $this->settings as $id => $setting ) {
			if ( $setting['type'] != 'heading' )
				$default_settings[$id] = $setting['std'];
		}
		
		update_option( $this->parent->settings_name, $default_settings );		
	}
	
	/**
	 * Add options page
	 *
	 * @since 1.0
	 */
	public function add_pages() {
		
		$parent = 'edit.php?post_type=' . $this->parent->post_type;

		$settings_page = add_submenu_page( $parent, $this->parent->name, __( 'Settings', 'wp-invoice' ), 'manage_options', $this->page, array( $this, 'display_page' ) );
		
		add_action( 'admin_print_scripts-' . $settings_page,	array( $this, 'scripts' ) );
		add_action( 'admin_print_styles-' . $settings_page,		array( $this, 'styles' ) );	
		add_action( 'admin_head-' . $settings_page,				array( $this, 'inline_scripts' ) );	
	}
	
	/**
	 * Register settings
	 *
	 */
	public function register_settings() {
		
		register_setting( $this->parent->settings_name, $this->parent->settings_name, array( $this, 'validate_settings' ) );
		
		foreach ( $this->sections as $slug => $title ) {
			if ( $slug == 'news' )
				add_settings_section( $slug, $title, array( $this, 'display_news_section' ), $this->page );
			elseif ( $slug == 'updates' )
				add_settings_section( $slug, $title, array( $this, 'display_updates_section' ), $this->page );
			elseif ( $slug == 'changelog' )
				add_settings_section( $slug, $title, array( $this, 'display_changelog_section' ), $this->page );
			else
				add_settings_section( $slug, $title, array( $this, 'display_section' ), $this->page );
		}
		
		$this->get_settings();
		
		foreach ( $this->settings as $id => $setting ) {
			$setting['id'] = $id;
			$this->create_setting( $setting );
		}		
	}
	
	/**
	 * Validate settings
	 *
	 */
	public function validate_settings( $input ) {
		
		$options = get_option( $this->parent->settings_name );
		
		foreach ( $this->checkboxes as $id ) {
			if ( isset( $options[$id] ) && ! isset( $input[$id] ) )
				unset( $options[$id] );
		}

		/**
		 * Custom validation
		 */
		$input['currency']			= esc_attr( strtoupper( substr( $input['currency'], 0, 2 ) ) );
		$input['tax']				= wp_invoice_number_format( $input['tax'], 4 );
		$input['send_invoice']		= esc_attr( $input['send_invoice'] );
		$input['permalink']			= esc_attr( $input['permalink'] );
		$input['require_login']		= isset( $input['require_login'] ) ? true : false;
		$input['from_email']		= is_email( $input['from_email'] ) ? $input['from_email'] : '';
		$input['payment_gateway']	= trim( esc_attr( $input['payment_gateway'] ) );
		
		$input['name']				= trim( esc_attr( $input['name'] ) );
		$input['company_name']		= trim( esc_attr( $input['company_name'] ) );
		//*/
		
		return $input;		
	}
	
	/**
	 * Create settings field
	 *
	 * @since 1.0
	 */
	public function create_setting( $args = array() ) {
		
		$defaults = array(
			'id'      => 'default_field',
			'title'   => __( 'Default Field', 'wp-invoice' ),
			'desc'    => __( 'This is a default description.', 'wp-invoice' ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'general',
			'choices' => array(),
			'class'   => ''
		);
			
		extract( wp_parse_args( $args, $defaults ) );
		
		$field_args = array(
			'type'      => $type,
			'id'        => $id,
			'desc'      => $desc,
			'std'       => $std,
			'choices'   => $choices,
			'label_for' => $id,
			'class'     => $class
		);
		
		if ( $type == 'checkbox' )
			$this->checkboxes[] = $id;
		
		add_settings_field( $id, $title, array( $this, 'display_setting' ), $this->page, $section, $field_args );
	}
	
	/**
	 * Settings and defaults
	 * 
	 * @since 1.0
	 */
	public function get_settings() {
		$options = get_option( $this->parent->settings_name );
		
		/* General Settings
		===========================================*/
		/**
		$this->settings['example_text'] = array(
			'title'   => __( 'Example Text Input', 'wp-invoice' ),
			'desc'    => __( 'This is a description for the text input.', 'wp-invoice' ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'general'
		);
		
		$this->settings['example_textarea'] = array(
			'title'   => __( 'Example Textarea Input', 'wp-invoice' ),
			'desc'    => __( 'This is a description for the textarea input.', 'wp-invoice' ),
			'std'     => '',
			'type'    => 'textarea',
			'section' => 'general'
		);
		
		$this->settings['example_checkbox'] = array(
			'section' => 'general',
			'title'   => __( 'Example Checkbox', 'wp-invoice' ),
			'desc'    => __( 'This is a description for the checkbox.', 'wp-invoice' ),
			'type'    => 'checkbox',
			'std'     => 1 // Set to 1 to be checked by default, 0 to be unchecked by default.
		);
		
		$this->settings['example_heading'] = array(
			'section' => 'general',
			'title'   => '', // Not used for headings.
			'desc'    => 'Example Heading',
			'type'    => 'heading'
		);
		
		$this->settings['example_radio'] = array(
			'section' => 'general',
			'title'   => __( 'Example Radio', 'wp-invoice' ),
			'desc'    => __( 'This is a description for the radio buttons.', 'wp-invoice' ),
			'type'    => 'radio',
			'std'     => '',
			'choices' => array(
				'choice1' => 'Choice 1',
				'choice2' => 'Choice 2',
				'choice3' => 'Choice 3'
			)
		);
		
		$this->settings['example_select'] = array(
			'section' => 'general',
			'title'   => __( 'Example Select', 'wp-invoice' ),
			'desc'    => __( 'This is a description for the drop-down.', 'wp-invoice' ),
			'type'    => 'select',
			'std'     => '',
			'choices' => array(
				'choice1' => 'Other Choice 1',
				'choice2' => 'Other Choice 2',
				'choice3' => 'Other Choice 3'
			)
		);
		//**/
		
		$countries = wp_invoice_get_countries();
		$currency = array();
		foreach ( $countries as $key => $country ) {
			$currency[$key] = $country['name'];
		}
		
		$gateways = wp_invoice_get_payment_gateways();
		$gateway = array();
		$gateway['-'] = __( 'None', 'wp-invoice' ); 
		foreach ( $gateways as $key => $value ) {
			$gateway[$key] = $value;
		}
		//print '<pre>'; print_r( $gateway ); print '</pre>';
		
		$this->settings['currency'] = array(
			'section' => 'general',
			'title'   => __( 'Currency', 'wp-invoice' ),
			'desc'    => __( 'Set your desired currency.', 'wp-invoice' ),
			'type'    => 'select',
			'choices' => $currency,
		);
		
		$this->settings['tax'] = array(
			'section' => 'general',
			'title'   => __( 'Tax', 'wp-invoice' ),
			'desc'    => __( 'Enter the tax.', 'wp-invoice' ),
			'type'    => 'text',
			'std'     => '0.00',
			'class'	  => 'small-text',
		);
		
		$this->settings['send_invoice'] = array(
			'section' => 'general',
			'title'   => __( 'Send Invoice', 'wp-invoice' ),
			'desc'    => __( 'Select recipients.', 'wp-invoice' ),
			'std'     => 'client',
			'type'    => 'radio',
			'choices' => array(
				'client'	=> __( 'Client Only', 'wp-invoice' ),
				'both'		=> __( 'Client and Me', 'wp-invoice' ),
			)
		);
		
		$this->settings['permalink'] = array(
			'section' => 'general',
			'title'   => __( 'Permalinks', 'wp-invoice' ),
			'desc'    => __( 'Encoded permalinks are more secure.', 'wp-invoice' ),
			'std'     => 'encoded',
			'type'    => 'radio',
			'choices' => array(
				'encoded'	=> __( 'Encoded', 'wp-invoice' ),
				'standard'	=> __( 'Standard', 'wp-invoice' ),
			)
		);
		
		$this->settings['require_login'] = array(
			'section' => 'general',
			'title'   => __( 'Login', 'wp-invoice' ),
			'desc'    => __( 'Require users to login to view invoice. More secure. (Suggested)', 'wp-invoice' ),
			'std'     => true,
			'type'    => 'checkbox',
		);
		
		$this->settings['from_email'] = array(
			'section' => 'general',
			'title'   => __( 'Email', 'wp-invoice' ),
			'desc'    => __( 'Sent from email.', 'wp-invoice' ),
			'type'    => 'text',
			'std'     => get_bloginfo( 'admin_email' ),
		);
		
		$this->settings['payment_gateway'] = array(
			'section' => 'general',
			'title'   => __( 'Payment Gateway', 'wp-invoice' ),
			'desc'    => __( 'Select your gateway. (Optional)', 'wp-invoice' ),
			'type'    => 'select',
			'choices' => $gateway,
		);
		
		$this->settings['payment_gateway_account'] = array(
			'section' => 'general',
			'title'   => __( 'Payment Account', 'wp-invoice' ),
			'type'    => 'input',
		);
				
		/* Company
		===========================================*/
		
		$this->settings['name'] = array(
			'section' => 'company',
			'title'   => __( 'Your Name (Sent From)', 'wp-invoice' ),
			'desc'    => __( 'Set sent from name for the [wp-invoice-name] shortcode', 'wp-invoice' ),
			'type'    => 'input',
			'std'     => '',
		);
		
		$this->settings['company_name'] = array(
			'section' => 'company',
			'title'   => __( 'Company Name', 'wp-invoice' ),
			'desc'    => __( 'Set company name for the [wp-invoice-company] shortcode', 'wp-invoice' ),
			'type'    => 'input',
			'std'     => '',
		);
				
		/* Hidden Fields
		===========================================*/
		
		/**
		$this->settings['active_id'] = array(
			'section' => 'about',
			'title'   => '',
			'type'    => 'hidden',
			'std'     => '',
		);
		//*/
		
	}
	
	/**
	 * Display options page
	 *
	 * @since 1.0
	 */
	public function display_page() { ?>
	
		<div class="wrap">
	
			<?php screen_icon(); ?>
	
			<h2><?php printf( __( '%1$s Settings', 'wp-invoice' ), $this->parent->name ); ?></h2>
	
			<div id="poststuff">
	
				<form method="post" action="options.php">
	
					<?php settings_fields( $this->parent->settings_name ); ?>
                    
                    <div class="ui-tabs">
                    <ul class="ui-tabs-nav">
                    <?php foreach ( $this->sections as $section_slug => $section ) echo '<li><a href="#' . $section_slug . '" class="' . $section_slug . '">' . $section . '</a></li>'; ?>
                    </ul>
	
					<?php do_settings_sections( esc_attr( $_GET['page'] ) ); ?>
                    </div>
	
					<?php submit_button( esc_attr__( 'Update Settings', 'wp-invoice' ) ); ?>
	
				</form>
            
            <?php if ( WP_DEBUG ) {
				
				print '<pre>';
				
				print_r( get_option( $this->parent->settings_name ) );
				
				print '</pre>';
				
				} ?>
	
			</div><!-- #poststuff -->
	
		</div><!-- .wrap --><?php
		
	}
	
	/**
	 * Description for section
	 *
	 * @since 1.0
	 */
	public function display_section() {}
	
	/**
	 * Description for Update section
	 *
	 * @since 1.0
	 */
	public function display_updates_section() {}	
	
	/**
	 * Description for News section
	 *
	 * @since 1.0
	 */
	public function display_news_section() {}
	
	/**
	 * Description for Changelog section
	 *
	 * @since 1.0
	 */
	public function display_changelog_section() {
		//delete_transient( $this->parent->settings_name . '_changelog' );
		if ( false === ( $cache = get_transient( $this->parent->settings_name . '_changelog' ) ) ) {
			$changelog = file_get_contents( $this->parent->plugin_path . 'changelog.txt' );
			if ( !is_wp_error( $changelog ) ) {
				set_transient( $this->parent->settings_name . '_changelog', $changelog, 3600 );
			}
		}
		print '<pre>' . print_r( $cache, true ) . '</pre>';
	}
	
	/**
	 * HTML output for text field
	 *
	 * @since 1.0
	 */
	public function display_setting( $args = array() ) {
		
		extract( $args );
		
		$options = get_option( $this->parent->settings_name );
		
		if ( ! isset( $options[$id] ) && $type != 'checkbox' )
			$options[$id] = $std;
		elseif ( ! isset( $options[$id] ) )
			$options[$id] = 0;
		
		$field_class = '';
		if ( $class != '' )
			$field_class = ' ' . $class;
		
		switch ( $type ) {
			
			case 'heading':
				echo '</td></tr><tr valign="top"><td colspan="2"><h4>' . $desc . '</h4>';
				break;
			
			case 'checkbox':
				
				echo '<input class="checkbox' . $field_class . '" type="checkbox" id="' . $id . '" name="wp_invoice_settings[' . $id . ']" value="1" ' . checked( $options[$id], 1, false ) . ' /> <label for="' . $id . '">' . $desc . '</label>';
				
				break;
			
			case 'select':
				echo '<select class="select' . $field_class . '" name="wp_invoice_settings[' . $id . ']">';
				
				foreach ( $choices as $value => $label )
					echo '<option value="' . esc_attr( $value ) . '"' . selected( $options[$id], $value, false ) . '>' . $label . '</option>';
				
				echo '</select>';
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'radio':
				$i = 0;
				foreach ( $choices as $value => $label ) {
					echo '<input class="radio' . $field_class . '" type="radio" name="wp_invoice_settings[' . $id . ']" id="' . $id . $i . '" value="' . esc_attr( $value ) . '" ' . checked( $options[$id], $value, false ) . '> <label for="' . $id . $i . '">' . $label . '</label>';
					if ( $i < count( $options ) - 1 )
						echo '<br />';
					$i++;
				}
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'textarea':
				echo '<textarea class="' . $field_class . '" id="' . $id . '" name="wp_invoice_settings[' . $id . ']" placeholder="' . $std . '" rows="5" cols="30">' . wp_htmledit_pre( $options[$id] ) . '</textarea>';
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'password':
				echo '<input class="regular-text' . $field_class . '" type="password" id="' . $id . '" name="wp_invoice_settings[' . $id . ']" value="' . esc_attr( $options[$id] ) . '" />';
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
				
			case 'file':
			
				echo '<input class="upload_file" type="text" size="45" id="' . $id . '" name="wp_invoice_settings[' . $id . ']" value="' . esc_url( $options[$id] ) . '" />';
				echo '<input class="upload_button button" type="button" value="Upload File" />';
				echo '<input class="upload_file_id" type="hidden" id="' . $id . '_id" name="wp_invoice_settings[' . $id . '_id]" value="' . $id . "_id" . '" />';					
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
					
				echo '<div id="' . $id . '_status" class="clp_upload_status">';	
					if ( $options[$id] != '' ) { 
						$check_image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $options[$id] );
						if ( $check_image ) {
							echo '<div class="img_status">';
							echo '<img src="' . $options[$id] . '" alt="" />';
							echo '<a href="#" class="remove_file_button" rel="' . $id . '">Remove Image</a>';
							echo '</div>';
						} else {
							$parts = explode( "/", $id );
							for( $i = 0; $i < sizeof( $parts ); ++$i ) {
								$title = $parts[$i];
							} 
							echo 'File: <strong>' . $title . '</strong>&nbsp;&nbsp;&nbsp; (<a href="' . $options[$id] . '" target="_blank" rel="external">Download</a> / <a href="#" class="remove_file_button" rel="' . $id . '">Remove</a>)';
						}	
					}
				echo '</div>';
				
				break;
			
			case 'hidden':
		 		echo '<input class="regular-text' . $field_class . '" type="hidden" id="' . $id . '" name="wp_invoice_settings[' . $id . ']" placeholder="' . $std . '" value="' . esc_attr( $options[$id] ) . '" />';
				
				break;
			
			case 'paragraph':
				echo '</td></tr><tr valign="top"><td colspan="2"><p>' . esc_html( $desc ) . '</p>';
				
				break;
				
			case 'text':
			default:
		 		echo '<input class="regular-text' . $field_class . '" type="text" id="' . $id . '" name="wp_invoice_settings[' . $id . ']" placeholder="' . $std . '" value="' . esc_attr( $options[$id] ) . '" />';
		 		
		 		if ( $desc != '' )
		 			echo '<br /><span class="description">' . $desc . '</span>';
		 		
		 		break;
		 	
		}
		
	}
	
	/**
	 * jQuery Tabs
	 *
	 */
	public function scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'theme-preview' );
		wp_enqueue_script( 'wp-invoice-admin' );		
	}
	
	/**
	 * Styling for the theme options page
	 *
	 */
	public function styles() {		
		wp_enqueue_style( 'wp-invoice-admin' );
		wp_enqueue_style( 'thickbox' );		
	}
	
	/**
	 * Loads the JavaScript required for toggling the meta boxes on the plugin settings page.
	 *
	 */
	function inline_scripts() { ?>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready(function($) {
				/* Tabs **/
				var sections = [];
				
				<?php foreach ( $this->sections as $section_slug => $section )
					echo "sections['$section'] = '$section_slug';\n\t\t\t\t"; ?>
				
				var wrapped = $('.wrap h3').wrap('<div class="ui-tabs-panel">');
				wrapped.each(function() {
					$(this).parent().append($(this).parent().nextUntil('div.ui-tabs-panel'));
				});
				$('.ui-tabs-panel').each(function(index) {
					$(this).prop('id', sections[$(this).children('h3').text()]);
					if (index > 0)
						$(this).addClass('ui-tabs-hide');
				});
				$('.ui-tabs').tabs({
					fx: { opacity: 'toggle', duration: 'fast' }
				});
				
				$('input[type="text"], textarea').each(function() {
					if ($(this).val() == $(this).prop('placeholder') || $(this).val() == '')
						$(this).css('color', '#999');
				});
				
				$('input[type="text"], textarea').focus(function() {
					if ($(this).val() == $(this).prop('placeholder') || $(this).val() == '') {
						$(this).val('');
						$(this).css('color', '#000');
					}
				}).blur(function() {
					if ($(this).val() == '' || $(this).val() == $(this).prop('placeholder')) {
						$(this).val($(this).prop('placeholder'));
						$(this).css('color', '#999');
					}
				});
				/* End Tabs **/
				
				$('#post').prop('enctype', 'multipart/form-data');
				$('#post').prop('encoding', 'multipart/form-data');
				
				$('.wrap h3, .wrap table').show();
				
				/**
				 * This will make the 'warning' checkbox class really stand out when checked.
				 * I use it here for the Reset checkbox.
				 */
				$('.warning').change(function() {
					if ($(this).is(':checked'))
						$(this).parent().css('background', '#c00').css('color', '#fff').css('fontWeight', 'bold');
					else
						$(this).parent().css('background', 'none').css('color', 'inherit').css('fontWeight', 'normal');
				});
				
				/* Browser compatibility //*/
				if ($.browser.mozilla) $('form').prop('autocomplete', 'off');
			});
			//]]>
		</script><?php
	}
	
}

?>