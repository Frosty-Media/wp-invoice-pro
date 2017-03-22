<?php

/**
 * Please remember email clients don't read <div> tags. Only tables. Enjoy.
 *
 */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title><?php echo ucfirst( wp_invoice_get_type( get_the_ID() ) ); ?> #<?php echo wp_invoice_get_number( get_the_ID() ); ?> | <?php bloginfo( 'name' ); ?> &ndash; <?php the_title(); ?></title>
	<style type="text/css" media="screen">
		article,aside,details,figcaption,figure,footer,header,hgroup,nav,section,summary{ display:block}audio,canvas,video{ display:inline-block; *display:inline; *zoom:1}audio:not([controls]){ display:none; height:0}[hidden]{ display:none}html{ font-size:100%;/* 1 */ -webkit-text-size-adjust:100%;/* 2 */ -ms-text-size-adjust:100%;/* 2 */}html,button,input,select,textarea{ font-family:sans-serif}body{ margin:0}a:focus{ outline:thin dotted}a:active,a:hover{ outline:0}h1{ font-size:2em; margin:0.67em 0}h2{ font-size:1.5em; margin:0.83em 0}h3{ font-size:1.17em; margin:1em 0}h4{ font-size:1em; margin:1.33em 0}h5{ font-size:0.83em; margin:1.67em 0}h6{ font-size:0.67em; margin:2.33em 0}abbr[title]{ border-bottom:1px dotted}b,strong{ font-weight:bold}blockquote{ margin:1em 40px}dfn{ font-style:italic}mark{ background:#ff0; color:#000}p,pre{ margin:1em 0}code,kbd,pre,samp{ font-family:monospace,serif; _font-family:'courier new',monospace; font-size:1em}pre{ white-space:pre; white-space:pre-wrap; word-wrap:break-word}q{ quotes:none}q:before,q:after{ content:''; content:none}small{ font-size:80%}sub,sup{ font-size:75%; line-height:0; position:relative; vertical-align:baseline}sup{ top:-0.5em}sub{ bottom:-0.25em}dl,menu,ol,ul{ margin:1em 0}dd{ margin:0 0 0 40px}menu,ol,ul{ padding:0 0 0 40px}nav ul,nav ol{ list-style:none; list-style-image:none}img{ border:0;/* 1 */ -ms-interpolation-mode:bicubic;/* 2 */}svg:not(:root){ overflow:hidden}figure{ margin:0}form{ margin:0}fieldset{ border:1px solid #c0c0c0; margin:0 2px; padding:0.35em 0.625em 0.75em}legend{ border:0;/* 1 */ padding:0; white-space:normal;/* 2 */ *margin-left:-7px;/* 3 */}button,input,select,textarea{ font-size:100%;/* 1 */ margin:0;/* 2 */ vertical-align:baseline;/* 3 */ *vertical-align:middle;/* 3 */}button,input{ line-height:normal}button,html input[type="button"],/* 1 */input[type="reset"],input[type="submit"]{ -webkit-appearance:button;/* 2 */ cursor:pointer;/* 3 */ *overflow:visible; /* 4 */}button[disabled],input[disabled]{ cursor:default}input[type="checkbox"],input[type="radio"]{ box-sizing:border-box;/* 1 */ padding:0;/* 2 */ *height:13px;/* 3 */ *width:13px;/* 3 */}input[type="search"]{ -webkit-appearance:textfield;/* 1 */ -moz-box-sizing:content-box; -webkit-box-sizing:content-box;/* 2 */ box-sizing:content-box}input[type="search"]::-webkit-search-cancel-button,input[type="search"]::-webkit-search-decoration{ -webkit-appearance:none}button::-moz-focus-inner,input::-moz-focus-inner{ border:0; padding:0}textarea{ overflow:auto;/* 1 */ vertical-align:top;/* 2 */}table{ border-collapse:collapse; border-spacing:0}
		
		body { font-size: 0.7em }
		
		/* Client-specific Styles */
		#outlook a {padding:0; } /* Force Outlook to provide a "view in browser" menu link. */
		body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0; } 
		/* Prevent Webkit and Windows Mobile platforms from changing default font sizes.*/ 
		.ExternalClass {width:100%; } /* Force Hotmail to display emails at full width */  
		.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%; }
		/* Forces Hotmail to display normal line spacing.  More on that: http://www.emailonacid.com/forum/viewthread/43/ */ 
		#backgroundTable {margin:0; padding:0; width:100% !important; line-height: 100% !important; }
		/* End reset */

		/* Some sensible defaults for images
		Bring inline: Yes. */
		img {outline:none; text-decoration:none; -ms-interpolation-mode: bicubic; } 
		a img {border:none; } 
		.image_fix {display:block; }

		/* Yahoo paragraph fix
		Bring inline: Yes. */
		p {margin: 1em 0; }

		/* Hotmail header color reset
		Bring inline: Yes. */
		h1, h2, h3, h4, h5, h6 {color: black !important; }

		h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {color: blue !important; }

		h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {
		color: red !important; /* Preferably not the same color as the normal header link color.  There is limited support for psuedo classes in email clients, this was added just for good measure. */
		}

		h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited {
		color: purple !important; /* Preferably not the same color as the normal header link color. There is limited support for psuedo classes in email clients, this was added just for good measure. */
		}

		/* Outlook 07, 10 Padding issue fix
		Bring inline: No.*/
		table td {border-collapse: collapse; }

		/* Remove spacing around Outlook 07, 10 tables
		Bring inline: Yes */
		table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; }

		/* Styling your links has become much simpler with the new Yahoo.  In fact, it falls in line with the main credo of styling in email and make sure to bring your styles inline.  Your link colors will be uniform across clients when brought inline.
		Bring inline: Yes. */
		a { color: #0091DB; text-decoration: none; padding-bottom: 0px; border-bottom: 1px solid rgba(0, 143, 220, 0.4); }
		a:hover { color: #70B7DD; border-bottom-color: rgba(0, 143, 220, 1); }
		
		/** Global
		 ****************************/
		h1 { font-weight :bold; font-size: 72px; line-height: 72px; }
		h2 { font-weight: bold; font-size: 18px; line-height: 18px; position: relative; margin-top: -18px; }
		body { background-color: #fff; margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; }
		a img { border: none; outline: none; }
		table, tr, td { vertical-align: top; }
		
		.body { padding: 30px 0px; border-top: #CCC solid 1px; border-bottom: #CCC solid 1px; }
		
		.meta { font-family: 'Lucida Grande'; color: #666666; padding-bottom: 20px; }
		.meta p { color: #666; font-size:11px; }
		.meta p a { color: #333; text-decoration: underline; }

		.container { background-color: #FFF; border: #DDDFDF solid 1px; margin-bottom: 50px; text-align: left; }
		.greeting p { color: #000; font-size: 1em; line-height: 1.1em; }
		
		table.header, table.info, table.hentry, table.breakdown,  table.payment-details { margin-bottom: 25px; }
		
		/** Header
		 ****************************/	
		.header h3 { font-weight: 400; font-size: 1.7em; line-height: 1.55em; }
		.header h3 strong {font-weight:700; }
		.header p { font-size: .85em; line-height: .95em; }
		.header p a { color:#999; }
		.header p a:hover { text-decoration: underline; }		
		
		/** Greeting
		 ****************************/
		p.error { margin: 5px 0 15px; background: #FFEBE8; border: 1px solid #CC0000; text-align: center; }		
		p.error span { display: block; padding: 5px; }
		
		/** Info
		 ****************************/
		.info { overflow: hidden; clear: both; }
		
		fieldset { border: 1px solid #F4F4F4; padding: 10px; width: 238px; }
		fieldset legend { display: block; padding: 0px 5px; color: #0091db; font-size: 1em; }
		fieldset p { font-size: .9em; margin-bottom: 5px; }
		fieldset p.hidden { display: none; }
		fieldset p strong { min-width: 120px; }		
		
		/** Project breakdown
		 ****************************/
		.breakdown { padding-top: 60px; width: 100%; }
		.breakdown tr {}
		.breakdown tr td { padding: 5px 5px; color: #999; font-size: 1em; line-height: 1em; }
		.breakdown tr.heading td { color: #0091db; text-transform:uppercase; font-size: 14px; line-height: 14px; padding: 7px 5px; font-weight: bold; }
		.breakdown tr.title td { background-color: #f5f5f5 !important; color: #616161; font-weight: bold; }
		.breakdown tr.description td { font-size: 10px; line-height: 1em; }
		.breakdown tr.heading td.total { background-color: #0091db; color: #FFF; }		

		/** Payment details
		 ****************************/
		.payment-details { padding-top:60px; }		
		
		/** Credit
		 ****************************/
		p.credits { padding-top: 60px; font-size: 10px; line-height: 14px; color: #999; }


		/***************************************************
		****************************************************
		MOBILE TARGETING
		****************************************************
		***************************************************/
		@media only screen and (max-device-width: 480px) {
			/* Part one of controlling phone number linking for mobile. */
			a[href^="tel"], a[href^="sms"] {
				text-decoration: none;
				color: blue; /* or whatever your want */
				pointer-events: none;
				cursor: default;
			}

			.mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
				text-decoration: default;
				color: orange !important;
				pointer-events: auto;
				cursor: default;
			}

		}

		/* More Specific Targeting */

		@media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
		/* You guessed it, ipad (tablets, smaller screens, etc) */
			/* repeating for the ipad */
			a[href^="tel"], a[href^="sms"] {
				text-decoration: none;
				color: blue; /* or whatever your want */
				pointer-events: none;
				cursor: default;
			}

			.mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
				text-decoration: default;
				color: orange !important;
				pointer-events: auto;
				cursor: default;
			}
		}

		@media only screen and (-webkit-min-device-pixel-ratio: 2) {
		/* Put your iPhone > 4g styles in here */ 
		}

		/* Android targeting */
		@media only screen and (-webkit-device-pixel-ratio:.75){
		/* Put CSS for low density (ldpi) Android layouts in here */
		}
		@media only screen and (-webkit-device-pixel-ratio:1){
		/* Put CSS for medium density (mdpi) Android layouts in here */
		}
		@media only screen and (-webkit-device-pixel-ratio:1.5){
		/* Put CSS for high density (hdpi) Android layouts in here */
		}
		/* end Android targeting */		
    </style>

<?php wp_head(); ?>
</head>
<body>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

<?php $user = wp_invoice_get_user( get_the_ID() ); //print '<pre>'; print_r( $user ); print '</pre>'; ?>

<?php $display_name = ( 0 != $user->ID ) ? $user->data->display_name : null; ?>

<?php $user_id = ( 0 != $user->ID ) ? $user->data->ID : null; ?>

    <table width="100%" cellspacing="0" cellpadding="20">
        <tr>
        <td class="greeting">
        	<?php if ( is_user_logged_in() && $display_name == wp_get_current_user()->data->display_name ) : ?>
            
            	<p class="error"><span><?php _e( 'Please select a Client from the client metabox.', 'wp-invoice' ); ?></span></p>
            
            <?php endif; ?>
        
			<?php if ( $greeting = wp_invoice_get_greeting( get_the_ID() ) ) : ?>
				
			<?php echo wpautop( do_shortcode( wp_invoice_get_greeting( get_the_ID() ) ) ); ?>
				
			<?php else : ?>
				
			<?php printf( __( '<p>Dear %s,</p><p>Here is your %s for %s.</p><p><small>Sent via WP Invoice Pro</small></p>', 'wp-invoice' ),
					esc_attr( $display_name ),
					wp_invoice_get_type( get_the_ID() ),
					get_the_title()
				); ?>
                
			<?php endif; ?>
        </td>
        </tr>
    </table>
    
	<table width="100%" cellspacing="0" cellpadding="0" bgcolor="#F0F0F0" class="body">
        <tr>
        <td align="center">
    	
            <table width="600" cellspacing="0" cellpadding="0">
                <tr>
                <td align="center" class="meta">
                    <p>
					<?php printf( __( 'You can view this %s online <a href="%s">here</a>.', 'wp-invoice' ),
							wp_invoice_get_type( get_the_ID() ),
							get_permalink()
						); ?>
					</p>
                </td>
                </tr>
            </table>
        
        <table width="600" cellspacing="0" cellpadding="30" class="container" bgcolor="#FFFFFF">
            <tr>
            <td align="left" valign="top">
                
                <table cellpadding="0" cellspacing="0" class="header">
                    <tr>
                        <td>
                            <h3><?php echo apply_filters( 'wp_invoice_company_title', __( 'WP Invoice Pro', 'wp-invoice' ) ); ?></h3>
                            
                            <p><?php echo apply_filters( 'wp_invoice_company_subtitle', __( 'by Austin Passy', 'wp-invoice' ) ); ?></p>
                            
                            <p><a href="mailto:<?php echo apply_filters( 'wp_invoice_company_email', antispambot( get_the_author_meta('email'), 1 ) ); ?>"><?php echo apply_filters( 'wp_invoice_company_email', antispambot( get_the_author_meta('email') ) ); ?></a></p>
                            
                            <p><a href="<?php echo get_site_url(); ?>"><?php echo get_site_url(); ?></a></p>
                        </td>
                    </tr>
                </table>
                
                <table cellpadding="0" cellspacing="0" class="info">
                    <tr>
                    <td>
                        <fieldset>
                            <legend><?php _e( 'Sent to', 'wp-invoice' ); ?></legend>
                            
                            <p><strong><?php echo esc_attr( $display_name ); ?></strong></p>
                            
                            <?php $user_data = wp_invoice_get_user_meta( null, $user_id ); ?>
                            
                            <p><big><?php echo esc_attr( $user_data['company'] ); ?></big></p>
                            
            				<?php $user_data['address_2'] = empty( $user_data['address_2'] ) ? $user_data['address_2'] : ' ' . esc_attr( $user_data['address_2'] ); ?>
                            
            				<?php $user_data['city'] = empty( $user_data['city'] ) ? $user_data['city'] : '<br>' . esc_attr( $user_data['city'] ); ?>
                            
            				<?php $user_data['state'] = empty( $user_data['state'] ) ? $user_data['state'] : ', ' . esc_attr( $user_data['state'] ); ?>
                            
            				<?php $user_data['postcode'] = empty( $user_data['postcode'] ) ? $user_data['postcode'] : ' ' . esc_attr( $user_data['postcode'] ); ?>
                            
            				<?php $user_data['country'] = empty( $user_data['country'] ) ? $user_data['country'] : '<br>' . esc_attr( $user_data['country'] ); ?>
                            
							<address><?php echo $user_data['address_1'] . $user_data['address_2'] . $user_data['city'] . $user_data['state'] . $user_data['postcode'] . $user_data['country']; ?></address>
                        </fieldset>
                    </td>
                    <td style="padding-left:20px;">
                        <fieldset class="last">
                            <legend><?php printf( __( '%s details', 'wp-invoice' ), ucfirst( wp_invoice_get_type( get_the_ID() ) ) ); ?></legend>
                            
                            <p><strong><?php _e( 'Project:', 'wp-invoice' ); ?></strong> <?php the_title(); ?></p>
                            
                            <p><?php printf( __( '<strong>%s number:</strong> %s', 'wp-invoice' ), ucfirst( wp_invoice_get_type( get_the_ID() ) ), esc_attr( wp_invoice_get_number( get_the_ID() ) ) ); ?></p>
                            
                            <p><strong><?php _e( 'Date Issued:', 'wp-invoice' ); ?></strong> <?php the_time( get_option('date_format') ); ?></p>
                        </fieldset>
                    </td>
                    </tr>
                </table>
                
                <?php if ( !empty( $post->post_content ) ) : ?>
                <table cellpadding="0" cellspacing="0" class="hentry">
                    <tr class="heading">
                        <td><?php _e( 'Notes:', 'wp-invoice' ); ?></td>
                    </tr>
                    <tr>
                        <td><?php the_content(); ?></td>
                    </tr>
                </table>
                <?php endif; ?>
            
                <table cellpadding="0" cellspacing="0" class="breakdown">
                    <tr class="heading">
                        <td><?php _e( 'Project Breakdown', 'wp-invoice' ); ?></td>
                        <td><?php _e( 'Type', 'wp-invoice' ); ?></td>
                        <td><?php _e( 'Rate', 'wp-invoice' ); ?></td>
                        <td><?php _e( 'Hours', 'wp-invoice' ); ?></td>
                        <td style="width:75px;"><?php _e( 'Subtotal', 'wp-invoice' ); ?></td>
                    </tr>
                    
                    <?php global $wp_invoice_breakdown; ?>
                    
                    <?php while ( $wp_invoice_breakdown->have_fields_and_multi('detail') ) : ?>
                    
    					<?php $wp_invoice_breakdown->the_group_open(); ?>
                                <tr class="title">
                                    <td><?php esc_attr( $wp_invoice_breakdown->the_value('title') ); ?></td>
                                    <td><?php esc_attr( $wp_invoice_breakdown->the_value('type') ); ?></td>
                                    <td><?php echo wp_invoice_currency_format( $wp_invoice_breakdown->get_the_value('rate') ); ?></td>
                                    <td><?php esc_attr( $wp_invoice_breakdown->the_value('time') ); ?></td>
                                    <td><?php echo wp_invoice_currency_format( $wp_invoice_breakdown->get_the_value('subtotal') ); ?></td>
                                </tr>
                                <tr class="description">
                                	<?php $description = $wp_invoice_breakdown->get_the_value('description') != '' ? $wp_invoice_breakdown->get_the_value('description') : '&nbsp;'; ?>
                                    <td><?php echo esc_html( $description ); ?></td>
                                    <td colspan="4"></td>
                                </tr>
                                
    					<?php $wp_invoice_breakdown->the_group_close(); ?>
                        
                    <?php endwhile; ?>
                    
                    <?php global $wp_invoice_detail; ?>
                    
                    <?php $tax = $wp_invoice_detail->get_the_value('invoice_tax'); ?>
                    
                    <?php if ( !empty( $tax ) || ( intval( $tax ) <= (int)0 ) ) :  ?>
                    <tr class="heading">
                        <td colspan="3"></td>
                        <td><?php _e( 'Subtotal', 'wp-invoice' ); ?></td>
                        <td><?php echo wp_invoice_currency_format( $wp_invoice_breakdown->get_the_value('subtotal') ); ?></td>
                    </tr>
                    <tr class="heading">
                        <td colspan="3"></td>
                        <td><?php _e( 'Tax', 'wp-invoice' ); ?></td>
                        <td><?php echo wp_invoice_currency_format( $wp_invoice_breakdown->get_the_value('tax') ); ?></td>
                    </tr>
                    <?php endif; ?>
                    
                    <tr class="heading">
                        <td colspan="3"></td>
                        <td class="total"><?php _e( 'Total', 'wp-invoice' ); ?></td>
                        <td class="total"><?php echo wp_invoice_currency_format( $wp_invoice_breakdown->get_the_value('total') ); ?></td>
                    </tr>
                </table>
                
                <table cellpadding="0" cellspacing="0" class="payment-details">
                    <tr>
                    <td>
                        <fieldset class="last">
                        <legend><?php _e( 'Payment Details', 'wp-invoice' ); ?></legend>
                		<?php if ( ucfirst( wp_invoice_get_type( get_the_ID() ) ) == __( 'Invoice', 'wp-invoice' ) ) : ?>
                        
                            <p><strong><?php _e( 'Bank:', 'wp-invoice' ); ?></strong> <span><?php echo apply_filters( 'wp_invoice_bank', 'Bank Name' ); ?></span></p>
                            <p><strong><?php _e( 'Acc Name:', 'wp-invoice' ); ?></strong> <span><?php echo apply_filters( 'wp_invoice_account_name', 'My Account' ); ?></p>
                            <p><strong><?php _e( 'Acc Routing:', 'wp-invoice' ); ?></strong> <span><?php echo apply_filters( 'wp_invoice_account_name', 'XXXXX-XXXXX-XXXXX' ); ?></p>
                            <p><strong><?php _e( 'Acc Number:', 'wp-invoice' ); ?></strong> <span><?php echo apply_filters( 'wp_invoice_account_number', 'XXXXX-XXXX-XXXX' ); ?></span></p>
                            
                        <?php else: ?>
                        
                            <p><strong><?php _e( 'This is a project quote, not an invoice.', 'wp-invoice' ); ?></strong></p>
                            <p><?php _e( 'No payment is required. Hope to hear from you soon.', 'wp-invoice' ); ?></p>
                            
                        <?php endif; ?>
                        </fieldset>
                    </td>
                    </tr>
                </table>
                
                <?php if ( ucfirst( wp_invoice_get_type( get_the_ID() ) ) == __( 'Invoice', 'wp-invoice' ) ) : ?>
                    <p class="credits">
                        <?php _e( '<strong>Important:</strong> The above invoice must be payed by Electronic Funds Transfer. Payment is due within 30 days from the date in this invoice. Late payment is subject to a fee of 5% per month.', 'wp-invoice' ); ?>
                    </p>
                <?php endif; ?>
                
                </td>
                </tr>
            </table>
            
        </td>
        </tr>
	</table>
    
<?php endwhile; endif; ?>

<?php wp_footer(); ?>
</body>
</html>