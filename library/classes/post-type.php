<?php

class wp_invoice_post_type {
	
	var $parent,
		$prefix,
		$settings;
	
	/**
	 * post_type Constructor
	 * 
	 */
	function __construct( $parent ) {
		
		$this->parent 	= $parent;
		$this->prefix	= '_wp_invoice_'; // Start with an underscore to hide fields from custom fields list
		$this->settings	= $this->parent->settings;
		
		/* Register the post_type */
		add_action( 'init',						array( $this, 'register_post_type' ), 9 );
		add_action( 'admin_head',				array( $this, 'icon' ) );
		
		/* Manage the post columns */
		add_filter( 'manage_edit-' . 			$this->parent->post_type . '_columns',				array( $this, 'columns_setup' ) );
		add_filter( 'manage_edit-' . 			$this->parent->post_type . '_sortable_columns',		array( $this, 'columns_setup_sortable' ) );
		add_action( 'manage_' . 				$this->parent->post_type . '_posts_custom_column',	array( $this, 'columns_data' ), 10, 2 );
		add_filter( 'request',					array( $this, 'column_orderby' ) );
		
		/* Initiate the WPAlchemy class */
		add_action( 'init',						array( $this, 'initialize_wpalchemy_meta_boxes' ) );
		
		/* Enqueue scripts */
		add_action( 'admin_enqueue_scripts',	array( $this, 'admin_enqueue_scripts' ), 12 );
		
		/* Invoice templateing system */
		add_action( 'template_redirect',		array( $this, 'invoice_template_redirect' ) );
		
		/* Modify the password template */
		add_filter( 'the_password_form',		array( $this, 'get_the_password_form' ) );
		
		/* wp_footer action call */
		add_action( 'wp_footer',				array( $this, 'wp_invoice_footer' ) );
	}
	
	/**
	 * Register the invoice post_type
	 *
	 * @return	void 
	 */
	function register_post_type() {
		
		$labels = array(
			'name'				=> _x( 'Invoices', 'post type general name' ),
			'singular_name'		=> _x( 'Invoice', 'post type singular name' ),
			'add_new'			=> _x( 'Add New', 'invoice' ),
			'add_new_item'		=> __( 'Add New Invoice' ),
			'edit_item'			=> __( 'Edit Invoice' ),
			'new_item'			=> __( 'New Invoice' ),
			'all_items'			=> __( 'All Invoices' ),
			'view_item'			=> __( 'View Invoice' ),
			'search_items'		=> __( 'Search Invoices' ),
			'not_found'			=> __( 'No invoices found' ),
			'not_found_in_trash'=> __( 'No invoices found in Trash' ), 
			'parent_item_colon'	=> '',
			'menu_name'			=> __( 'Invoices' )		
		);
		
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'show_in_admin_bar' => false,
			'show_ui' => true, 
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'invoice', 'with_front' => false ),
			'capability_type' => 'post',
			'has_archive' => false, 
			'hierarchical' => false,
			'menu_position' => null,
			//'menu_icon' => trailingslashit( $this->parent->plugin_url ) . 'images/icon-adminmenu16-sprite.png',
			'supports' => array( 'title', 'editor', 'comments' )
		); 
		
		register_post_type( $this->parent->post_type, $args );		
	}

	/**
	 * Output css in the admin head
	 *
	 * @return	string
	 */
	function icon() {
		?>
		<style>
			/* Admin Menu - 16px */
			#menu-posts-<?php echo $this->parent->post_type; ?> .wp-menu-image {
				background: url('<?php echo trailingslashit( $this->parent->plugin_url ) ?>images/icon-adminmenu16-sprite.png') no-repeat 7px 7px !important;
			}
			#menu-posts-<?php echo $this->parent->post_type; ?>:hover .wp-menu-image, #menu-posts-<?php echo $this->parent->post_type; ?>.wp-has-current-submenu .wp-menu-image {
				background-position: 7px -25px !important;
			}
			/* Post Screen - 32px */
			.icon32-posts-<?php echo $this->parent->post_type; ?> {
				background: url('<?php echo trailingslashit( $this->parent->plugin_url ) ?>images/icon-adminpage32.png') no-repeat left top !important;
			}
			@media
			only screen and (-webkit-min-device-pixel-ratio: 1.5),
			only screen and (   min--moz-device-pixel-ratio: 1.5),
			only screen and (     -o-min-device-pixel-ratio: 3/2),
			only screen and (        min-device-pixel-ratio: 1.5),
			only screen and (        		 min-resolution: 1.5dppx) {
				
				/* Admin Menu - 16px @2x */
				#menu-posts-<?php echo $this->parent->post_type; ?> .wp-menu-image {
					background-image: url('<?php echo trailingslashit( $this->parent->plugin_url ) ?>images/icon-adminmenu16-sprite_2x.png') !important;
					-webkit-background-size: 16px 48px;
					-moz-background-size: 16px 48px;
					background-size: 16px 48px;
				}
				/* Post Screen - 32px @2x */
				.icon32-posts-<?php echo $this->parent->post_type; ?> {
					background-image: url('<?php echo trailingslashit( $this->parent->plugin_url ) ?>images/icon-adminpage32_2x.png') !important;
					-webkit-background-size: 32px 32px;
					-moz-background-size: 32px 32px;
					background-size: 32px 32px;
				}         
			}
		</style>
	<?php } 
	
	/**
	 * 
	 */
	function columns_setup( $columns ) {
		$columns = array(
			'cb'			=> '<input type="checkbox" />',
			'invoice_no'	=> __( 'Number', 'wp-invoice' ),
			'invoice_type'	=> __( 'Type', 'wp-invoice' ),
			'title'			=> __( 'Title', 'wp-invoice' ),
			'amount'		=> __( 'Amount', 'wp-invoice' ),
			'status'		=> __( 'Status', 'wp-invoice' ),
			'client'		=> __( 'Client', 'wp-invoice' ),
			//'author'		=> __( 'Author', 'wp-invoice' ),
		);
		return $columns;
	}
	
	function columns_setup_sortable( $columns ) {
		$columns['invoice_no']		= 'invoice_no';
		$columns['invoice_type']	= 'invoice_type';
		$columns['client']			= 'client';
		return $columns;
	}

	function columns_data( $column, $post_id ) {
		global $wp_invoice_email, $wp_invoice_detail, $wp_invoice_breakdown;
		
		switch ( $column ) {			
			case 'invoice_no' :
				echo wp_invoice_get_number( $post_id, __( 'Not Set', 'wp-invoice' ) );
				break;
			
			case 'invoice_type' :
				echo wp_invoice_get_type( $post_id, __( 'Not Set', 'wp-invoice' ) );
				break;
			
			case 'amount' :
				$wp_invoice_breakdown->the_meta();
				if ( $wp_invoice_breakdown->get_the_value('total') != '' ) {
					echo wp_invoice_currency_format( $wp_invoice_breakdown->get_the_value('total') );
				} else {
					_e( 'Not Set', 'wp-invoice' );
				}
				break;
			
			case 'client' :
        		$user = wp_invoice_get_user( $post_id );
				
				if ( !empty( $user ) && $user->data->display_name != wp_get_current_user()->data->display_name ) {
					
					//print '<pre>'; print_r( $user ); print '</pre>';
					echo sprintf( __( '<a href="%s">%s</a>', 'wp-invoice' ),
							esc_url( add_query_arg( array( 'post' => $post_id, 'action' => 'edit' ), 'post.php' ) ),
							esc_attr( $user->data->display_name )
						);
					
				} else {
					$out = '' .
						__( 'No Client Set', 'wp-invoice' ) .
						'<div class="row-actions">' .
						sprintf( __( '<a href="%s">Add Client</a>', 'wp-invoice' ),
							esc_url( add_query_arg( array( 'post' => $post_id, 'action' => 'edit' ), 'post.php' ) )
						) .
						'</div>';
					echo $out;
				}
				break;
			
			case 'status' :
				echo wp_invoice_get_status( $post_id );
				break;
		}
		
	}
	
	/**
	 * Order the meta
	 *
	 * @return 	$query
	 */
	function column_orderby( $vars ) {
		global $wp_invoice_detail, $wp_invoice_client;
		
		if ( !isset( $vars['orderby'] ) ) return $vars;
		
		//print_r( $vars );
		
		if ( $vars['orderby'] == 'invoice_no' ) {
			$wp_invoice_detail->the_meta();
			$key = $wp_invoice_detail->get_the_name('number');
			$val = $wp_invoice_detail->get_the_name('number');
			$vars = array_merge( $vars, array(
				'orderby'	=> 'meta_value_num',
				'meta_query' => array(
					array(
						'key' => $key,
						'value' => $val,
						'compare' => '='
					)
				)
		    ) );
		}
		
		if ( $vars['orderby'] == 'invoice_type' ) {
			$wp_invoice_detail->the_meta();
			$key = $wp_invoice_detail->get_the_name('type');
			$val = $wp_invoice_detail->get_the_value('type');
			$vars = array_merge( $vars, array(
				'orderby'	=> 'meta_value',
				'meta_query' => array(
					array(
						'key' => $key,
						'value' => $val,
						'compare' => '='
					)
				)
		    ) );
		}
		
		if ( $vars['orderby'] == 'client' ) {
			$wp_invoice_client->the_meta();
			$key = $wp_invoice_client->get_the_name('user');
			$val = $wp_invoice_client->get_the_value('user');
			$vars = array_merge( $vars, array(
				'orderby'	=> 'meta_value',
				'meta_query' => array(
					array(
						'key' => $key,
						'value' => $val,
						'compare' => '='
					)
				)
		    ) );
		}	 
		return $vars;
	}
	
	/**
	 * Initialize the WPAlchemy Meta Box class
	 *
	 * @ref		http://www.farinspace.com/wpalchemy-metabox-data-storage-modes/
	 * @updated	2/27/13
	 * @since 	0.1.0
	 */
	function initialize_wpalchemy_meta_boxes() {
		global $wp_invoice_email, $wp_invoice_detail, $wp_invoice_greeting, $wp_invoice_breakdown, $wp_invoice_client;
		
		if ( !class_exists( 'WPAlchemy_MetaBox' ) )
			require_once( trailingslashit( $this->parent->plugin_path ) . 'library/classes/wpalchemy/MetaBox.php' );
		
		$prefix = '_wp_invoice_pro_';
					
		/* Send and View Email */
		$wp_invoice_email = new WPAlchemy_MetaBox(
			array(
				'mode'			=> WPALCHEMY_MODE_EXTRACT,
				'prefix'		=> $prefix,
				'id'			=> $this->prefix . 'project_email',
				'title'			=> __( 'Send &amp; Email', 'wp-invoice' ),
				'types'			=> array( $this->parent->post_type ),
				'template'		=> trailingslashit( $this->parent->plugin_path ) . 'library/views/project-email.php',
				'hide_editor' 	=> false,
				'lock'			=> true,
				'view'			=> 'always_opened',
				'context'		=> 'side',
				'priority'		=> 'high',
				'hide_screen_option' => true
		));
		
		/* Project details */
		$wp_invoice_detail = new WPAlchemy_MetaBox(
			array(
				'mode'			=> WPALCHEMY_MODE_ARRAY,
				'prefix'		=> $prefix,
				'id'			=> $this->prefix . 'project_details',
				'title'			=> __( 'Invoice Details', 'wp-invoice' ),
				'types'			=> array( $this->parent->post_type ),
				'template'		=> trailingslashit( $this->parent->plugin_path ) . 'library/views/project-details.php',
				'hide_editor' 	=> false,
				'lock'			=> true,
				'view'			=> 'always_opened',
				'context'		=> 'side',
				'hide_screen_option' => true
		));
		
		/* Project breakdown */
		$wp_invoice_breakdown = new WPAlchemy_MetaBox(
			array(
				'mode'			=> WPALCHEMY_MODE_EXTRACT,
				'prefix'		=> $prefix,
				'id'			=> $this->prefix . 'project_breakdown',
				'title'			=> __( 'Project Breakdown', 'wp-invoice' ),
				'types'			=> array( $this->parent->post_type ),
				'template'		=> trailingslashit( $this->parent->plugin_path ) . 'library/views/project-breakdown.php',
				'hide_editor' 	=> false,
				'view'			=> 'always_opened',
				'save_filter'	=> 'wp_invoice_pro_clean_empty_breakdown_meta',
				'hide_screen_option' => true,
		));
		
		/* Project greeting */
		$wp_invoice_greeting = new WPAlchemy_MetaBox(
			array(
				'mode'			=> WPALCHEMY_MODE_EXTRACT,
				'prefix'		=> $prefix,
				'id'			=> $this->prefix . 'project_greeting',
				'title'			=> __( 'Email Greeting', 'wp-invoice' ),
				'types'			=> array( $this->parent->post_type ),
				'template'		=> trailingslashit( $this->parent->plugin_path ) . 'library/views/project-greeting.php',
				'hide_editor' 	=> false,
				'view'			=> 'always_opened',
				'hide_screen_option' => true
		));
		
		/* Invoice Client */
		$wp_invoice_client = new WPAlchemy_MetaBox(
			array(
				'mode'			=> WPALCHEMY_MODE_EXTRACT,
				'prefix'		=> $prefix,
				'id'			=> $this->prefix . 'project_client',
				'title'			=> __( 'Client', 'wp-invoice' ),
				'types'			=> array( $this->parent->post_type ),
				'template'		=> trailingslashit( $this->parent->plugin_path ) . 'library/views/project-user.php',
				'hide_editor' 	=> false,
				'view'			=> 'always_opened',
				'context'		=> 'side',
				'priority'		=> 'default',
				'hide_screen_option' => true
		));
	}
	
	/* Enqueue scripts (and related stylesheets) */
	function admin_enqueue_scripts( $hook_suffix ) {
		global $post_type;
		
		if ( $this->parent->post_type == $post_type ) {
			
			/* Enqueue Scripts */
			wp_enqueue_script( array( 'wp-invoice-admin', 'jquery-ui-sortable' ) );
			wp_enqueue_style( array( 'wp-invoice-admin' ) );
			
		}
	}
	
	/**
	 * Invoice Template Redirect
	 * 
	 */
	function invoice_template_redirect() {
		global $wp, $post, $wp_invoice_detail;
		
		$post_type = get_query_var('post_type');
		$email = isset( $_GET['email'] ) ? $_GET['email'] : null;
		$paid = isset( $_GET['paid'] ) ? $_GET['paid'] : null;
		
		if ( $this->parent->post_type == $post_type ) {
			
			if ( !is_null( $paid ) && 'true' == $paid ) {
				update_post_meta( $post->ID, $wp_invoice_detail->get_the_name('paid'), date_i18n( 'm/d/Y' ) );
			}
			
			// 1. find invoice.php template file
			$invoice_template = trailingslashit( get_stylesheet_directory() ) . 'wp-invoice/invoice.php';
			
			if ( !file_exists( $invoice_template ) ) $invoice_template = trailingslashit( $this->parent->plugin_path ) . 'template/invoice.php';
			
			// 2. find email.php template file
			$email_template = trailingslashit( get_stylesheet_directory() ) . '/wp-invoice/email.php';
			
			if ( !file_exists( $email_template ) ) $email_template = trailingslashit( $this->parent->plugin_path ) . '/template/email.php';
			
			$this->invoice_security();
			
			if ( $email == 'send' ) {
				// get html email and store as variable for sending
				ob_start();
					require_once( $email_template );
					$message = ob_get_clean();
				require_once( trailingslashit( $this->parent->plugin_path ) . 'library/classes/mail.php' );
				new wp_invoice_email( $this->parent, $message );
			}
			elseif ( $email == 'view' ) {
				require_once( $email_template );
			}
			elseif ( is_single() ) {
				require_once( $invoice_template );
			}
			die();
		}
	}
	
	/**
	 * Invoice Security
	 *
	 * If client login is required redirect them to the login page.
	 *		@use filter the wp_login_url() if you do not want them going
	 *		to the wp-login.php page.
	 *
	 * If the invoice/quote requres a password show them the password
	 *		form before redirecting them to the page.
	 *
	 * @return
	 */
	function invoice_security() {
		global $post, $wp_invoice_email;
		
		if ( empty( $post ) ) $post = get_post();
		
		$wp_invoice_email->the_meta();
		
		$require_login 		= isset( $this->settings['require_login'] ) ? $this->settings['require_login'] : false;
		$post_require_login = get_post_meta( $post->ID, '_wp_invoice_pro_require_login', true );
		$post_require_login = isset( $post_require_login ) ? $post_require_login : false;
		$post_disable_login = get_post_meta( $post->ID, '_wp_invoice_pro_disable_login', true );
		$post_disable_login = isset( $post_disable_login ) ? $post_disable_login : false;
		
		if ( !is_user_logged_in() ) {
			if ( ( $require_login || $post_require_login ) && !$post_disable_login ) {								
				$redirect = $_SERVER['REQUEST_URI'];
				wp_redirect( wp_login_url( $redirect ) );
				exit;
			}
		}		
		elseif ( post_password_required() ) {	
			$css = "</p>\n" .
			'<style type="text/css" media="all">
				form { width: 85%; margin: 20% auto; padding: 25px 20px; }
				form p { color: #666; font-size: 1.1em; line-height: 1.1em; margin: 0 0 25px; padding: 0; text-align: center; }
				form label { display: none; visablity: hidden; }
				form input[type="text"], form input[type="password"] { font-size: 1.4em; line-height: 2em; height: 2em; padding: 2px 10px; -moz-border-radius: 6px; -webkit-border-radius: 6px; -ms-border-radius: 6px; border-radius: 6px; border: 3px solid #E5E5E5; font-family: Georgia, "Times New Roman", Times, serif; font-style: italic; margin: 0 0 10px 0; width: auto; }
				form input[type="submit"] {
					background: #4E68C7;
					border: 0 none;
					border-radius: 0;
					box-shadow: 1px 0 1px #203891, 0 1px 1px #3852B1, 2px 1px 1px #203891, 1px 2px 1px #3852B1, 3px 2px 1px #203891, 2px 3px 1px #3852B1, 4px 3px 1px #203891, 3px 4px 1px #3852B1, 5px 4px 1px #203891, 4px 5px 1px #3852B1, 6px 5px 1px #203891;
					cursor: pointer;
					color: white;
					outline: 0 none;
					padding: 9px 16px;
					position: relative;
					top: -5px;
					font-weight: bold;
					white-space: nowrap;
				}
				form input[type="submit"]:hover, form input[type="submit"]:focus {
					background: #3D57B4;
					color: #eee;
				}
				form input[type="submit"]:active {
					box-shadow: 1px 0 1px #203891, 0 1px 1px #3852B1, 2px 1px 1px #203891, 1px 2px 1px #3852B1, 3px 2px 1px #203891;
					left: 3px;
					top: -2px;
				}
			</style>' . "\n";	
			
			$form = $css . get_the_password_form();   
			
			wp_die( $form, esc_attr__( 'Please enter your password. | WP Invoice', 'wp-invoice' ), array( 'response' => 302 ) );
        }
	}
	
	/**
	 * Change the password form for this $post_type only
	 *
	 * @return
	 */
	function get_the_password_form( $output ) {
		$post_type = get_query_var('post_type');
		
		if ( $this->parent->post_type == $post_type ) {
			$post = get_post( get_the_ID() );
			$label = 'pwbox-' . ( empty( $post->ID ) ? rand() : $post->ID );
			$output = '<form action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" method="post">
			<p>' . sprintf( __("This %s is password protected. To view it please enter your password below:"), wp_invoice_get_type( $post->ID ) ) . '</p>
			<p><label for="' . $label . '">' . __( 'Password:', 'wp-invoice' ) . '</label> <input name="post_password" id="' . $label . '" type="password" size="20" placeholder="' . __( 'Password:', 'wp-invoice' ) . '" autofocus required> <input type="submit" name="Submit" value="' . esc_attr__( 'Submit', 'wp-invoice' ) . '" /></p>
		</form>
			';
		}
		return $output;
	}
	 
	/**
	 * Print Bar
	 *
	 * @author Austin Passy
	 * @since 1.0.0
	 * 
	 **/
	function wp_invoice_footer() {
		global $post;
		
		if ( isset( $_GET['email'] ) && $_GET['email'] == 'send' ) {
			return false;	
		}
		
		if ( $this->parent->post_type == get_post_type( $post->ID ) ) : ?>
        	<style type="text/css" media="all">
				#wpinvoicebar {
					position:fixed; bottom:0px; left:0px; width:100%; height:30px;
					background: #1c2a3f; /* Old browsers */
					background: -moz-linear-gradient(top, #1c2a3f 0%, #30497b 100%); /* FF3.6+ */
					background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#1c2a3f), color-stop(100%,#30497b)); /* Chrome,Safari4+ */
					background: -webkit-linear-gradient(top, #1c2a3f 0%,#30497b 100%); /* Chrome10+,Safari5.1+ */
					background: -o-linear-gradient(top, #1c2a3f 0%,#30497b 100%); /* Opera 11.10+ */
					background: -ms-linear-gradient(top, #1c2a3f 0%,#30497b 100%); /* IE10+ */
					background: linear-gradient(to bottom, #1c2a3f 0%,#30497b 100%); /* W3C */
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#1c2a3f', endColorstr='#30497b',GradientType=0 ); /* IE6-9 */
					font-size:11px; color:#999; text-shadow:#000 0px -1px 0px; overflow:hidden; border-top:#343434 solid 1px;
				}
				#wpinvoicebar .container {width:600px; margin:0 auto; background:none transparent; border:none; padding:0px; overflow:hidden; height:30px; line-height:30px;}
				#wpinvoicebar p {color:#999; margin:0px; padding:0px; }
				#wpinvoicebar .status {float:left; color:#999;}
				#wpinvoicebar .status.paid {float:left; color:#95db30;}
				#wpinvoicebar .buttons {float:right;}
				#wpinvoicebar .buttons a {-moz-border-radius: 11px; -webkit-border-radius: 11px;-khtml-border-radius: 11px; border-radius: 11px; cursor:pointer; font-size:11px; padding:4px 8px 3px 8px; text-decoration:none; background:url("<?php echo admin_url( '/images/white-grad.png' ); ?>") repeat-x scroll left top #F2F2F2; text-shadow:0 1px 0 #FFFFFF; margin-left:5px; display:block; float:left; line-height:13px; margin-top:4px; color:#333;}
				#wpinvoicebar .print a:hover {background:#fff none;}
			</style>
            <style type="text/css" media="print">
				#wpinvoicebar {display:none; height:0;}
			</style>
            
			<div id="wpinvoicebar">
            
            	<div class="container">
                
                <div class="status <?php if ( wp_invoice_get_status( $post->ID ) == __( 'Paid', 'wp-invoice' ) ) echo 'paid'; ?>">
                
					<?php if ( ucfirst( wp_invoice_get_type( $post->ID ) ) == __( 'Invoice', 'wp-invoice' ) ) : ?>
                    
                        <?php _e( 'Invoice status:', 'wp-invoice' ); ?> <?php echo wp_invoice_get_status( $post->ID ); ?>
                        
                    <?php else: ?>
                    
                        <?php _e( 'Invoice status: Quote', 'wp-invoice' ); ?>	
                        
                    <?php endif; ?>
                    
                 </div><!-- .status -->
            	
                <div class="buttons">
                
                	<?php edit_post_link( 'Edit ' . wp_invoice_get_type( $post->ID ) ); ?> 
                    
                    <?php if ( !isset( $_GET['email'] ) ) : //viewing online version ?>
                    
                    	<?php if ( is_user_logged_in() ) : ?>
                        
                    		<a href="<?php echo add_query_arg( 'email', 'view', get_permalink( $post->ID ) ); ?>"><?php _e( 'Email Version', 'wp-invoice' ); ?></a>
                            
                        <?php endif; ?>
                        
                        <a href="javascript:print()" onclick="return false;"><?php _e( 'Print', 'wp-invoice' ); ?></a>
                        
                        <?php if ( ucfirst( wp_invoice_get_type( $post->ID ) ) == __( 'Invoice', 'wp-invoice' ) ) : ?>
                        
                        	<?php wp_invoice_payment_gateway_button( $post->ID ); ?>
                            
                        <?php endif; ?>
                        
                    <?php elseif ( isset( $_GET['email'] ) && $_GET['email'] == 'view' ) : //viewing email version ?>
                    
                    	<a href="<?php the_permalink(); ?>"><?php _e( 'Online Version', 'wp-invoice' ); ?></a>
                        
                        <a href="<?php echo add_query_arg( 'email', 'send', get_permalink( $post->ID ) ); ?>"><?php _e( 'Send Email', 'wp-invoice' ); ?></a>
                        
                    <?php endif; ?>
                	
                </div><!-- .buttons -->
             
                </div><!-- .container -->
                
            </div><!-- #wpinvoicebar -->
		<?php endif;
	}
}
	
function wp_invoice_pro_clean_empty_breakdown_meta( $meta, $post_id ) {
	foreach( $meta['detail'] as $key => &$detail ) {
		if ( !isset( $detail['title'] ) && 'NaN' == $detail['subtotal'] ) unset( $meta['detail'][$key], $detail );
//		print '<pre>' . print_r( $meta['detail'][$key], true ) . '</pre>';
	}
//	print '<pre>' . print_r( $meta, true ) . '</pre>'; exit;
	return $meta;
}

?>