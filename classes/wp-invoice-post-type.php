<?php

class WP_Invoice_Post_Type {

    var $name,
        $dir,
        $plugin_dir,
        $plugin_path;

    /**
     * Invoice Constructor
     *
     * @since 1.0.0
     *
     * @param wp_invoice_pro $parent
     **/
    function __construct( wp_invoice_pro $parent ) {
        $this->name        = $parent->name;                                // Plugin Name
        $this->dir         = dirname( plugin_dir_path( __FILE__ ) );    // This directory
        $this->plugin_dir  = $parent->dir;                                // Plugin directory
        $this->plugin_path = $parent->path;                                // Plugin Absolute Path

        // Set up Actions
        add_action( 'init', [ $this, 'create_custom_post' ] );
        add_action( 'admin_head', [ $this, 'icon' ] );

        add_filter( 'manage_edit-invoice_columns', [ $this, 'invoice_columns_setup' ] );
        add_filter( 'manage_edit-invoice_sortable_columns', [
            $this,
            'invoice_columns_setup_sortable',
        ] );
        add_filter( 'request', [ $this, 'invoice_column_orderby' ] );
        add_action( 'manage_posts_custom_column', [ $this, 'invoice_columns_data' ] );

        add_action( 'restrict_manage_posts', [ $this, 'invoice_columns_filter' ] );
//		add_filter( 'pre_get_posts', 						array( $this, 'invoice_number_order' ) );

        add_action( 'admin_menu', [ $this, 'create_meta_boxes' ] );
        add_action( 'save_post', [ $this, 'save_invoice' ], 10, 2 );
        add_action( 'template_redirect', [ $this, 'invoice_template_redirect' ], 8 );

        return true;
    }

    /**
     * Creates Custom Posts type: Invoice
     *
     * @since 1.0.0
     *
     **/
    function create_custom_post() {
        $labels = [
            'name' => __( 'Invoices' ),
            'singular_name' => __( 'Invoice' ),
            'search_items' => __( 'Search Invoices' ),
            'all_items' => __( 'All Invoices' ),
            'parent_item' => __( 'Parent Invoice' ),
            'parent_item_colon' => __( 'Parent Invoice:' ),
            'edit_item' => __( 'Edit Invoice' ),
            'update_item' => __( 'Update Invoice' ),
            'add_new_item' => __( 'Add New Invoice' ),
            'new_item_name' => __( 'New Invoice Name' ),
            'view_item' => __( 'View Invoice / Quote' ),
        ];

        $supports = [ 'title', 'comments' ];

        if ( wp_invoice_get_content_editor() == 'enabled' ) {
            $supports[] = 'editor';
        }

        register_post_type( 'invoice', [
            'labels' => $labels,
            //			'menu_icon' 			=> trailingslashit( $this->plugin_path ) . 'images/icon-adminmenu16-sprint_2x.png',
            'public' => true,
            'show_ui' => true,
            '_builtin' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'has_archive' => true,
            'rewrite' => [ 'slug' => 'invoice', 'with_front' => false, 'feeds' => false ],
            'query_var' => 'invoice',
            'supports' => $supports,
            'exclude_from_search' => true,
            'show_in_menu' => true,
        ] );
    }

    /**
     * Output css in the admin head
     *
     * @return    string
     */
    function icon() {
        $post_type = 'invoice'; ?>
        <style>
            /* Admin Menu - 16px */
            #menu-posts-<?php echo $post_type; ?> .wp-menu-image {
                background: url('<?php echo trailingslashit( $this->plugin_path ) ?>images/icon-adminmenu16-sprite.png') no-repeat 9px 7px !important;
            }

            #menu-posts-<?php echo $post_type; ?> .wp-menu-image:before {
                content: "" !important;
            }

            #menu-posts-<?php echo $post_type; ?>:hover .wp-menu-image, #menu-posts-<?php echo $post_type; ?>.wp-has-current-submenu .wp-menu-image {
                background-position: 9px -25px !important;
            }

            /* Post Screen - 32px */
            .icon32-posts-<?php echo $post_type; ?> {
                background: url('<?php echo trailingslashit( $this->plugin_path ) ?>images/icon-adminpage32.png') no-repeat left top !important;
            }

            @media only screen and (-webkit-min-device-pixel-ratio: 1.5), only screen and (   min--moz-device-pixel-ratio: 1.5), only screen and (     -o-min-device-pixel-ratio: 3/2), only screen and (        min-device-pixel-ratio: 1.5), only screen and (                 min-resolution: 1.5dppx) {

                /* Admin Menu - 16px @2x */
                #menu-posts-<?php echo $post_type; ?> .wp-menu-image {
                    background-image: url('<?php echo trailingslashit( $this->plugin_path ) ?>images/icon-adminmenu16-sprite_2x.png') !important;
                    -webkit-background-size: 16px 48px !important;
                    -moz-background-size: 16px 48px !important;
                    background-size: 16px 48px !important;
                }

                /* Post Screen - 32px @2x */
                .icon32-posts-<?php echo $post_type; ?> {
                    background-image: url('<?php echo trailingslashit( $this->plugin_path ) ?>images/icon-adminpage32_2x.png') !important;
                    -webkit-background-size: 32px 32px !important;
                    -moz-background-size: 32px 32px !important;
                    background-size: 32px 32px !important;
                }
            }
        </style>
    <?php }


    /**
     * Creates Custom Posts type: Invoice
     *
     * @since 1.0.0
     *
     **/
    function invoice_columns_setup( $columns ) {
        $columns = [
            "cb" => "<input type=\"checkbox\" />",
            "invoice_no" => "Invoice No.",
            "invoice_type" => "Type",
            "title" => "Title",
            "amount" => "Amount",
            "status" => "Status",
            "client" => "Client",
        ];

        return $columns;
    }

    function invoice_columns_setup_sortable( $columns ) {
        $columns['invoice_no']   = 'invoice_no';
        $columns['invoice_type'] = 'invoice_type';

        return $columns;
    }

    function invoice_column_orderby( $vars ) {
        if ( ! isset( $vars['orderby'] ) ) {
            return $vars;
        }

        if ( $vars['orderby'] == 'invoice_no' ) {
            $vars = array_merge( $vars, [
                'meta_key' => 'invoice_number',
                'orderby' => 'meta_value_num',
            ] );
        }

        if ( $vars['orderby'] == 'invoice_type' ) {
            $vars = array_merge( $vars, [
                'meta_key' => 'invoice_type',
                'orderby' => 'meta_value',
            ] );
        }

        return $vars;
    }

    function invoice_columns_data( $column ) {
        global $post;
        $wp_invoice = WP_INVOICE_PRO();
        if ( "ID" == $column ) {
            echo $post->ID;
        } elseif ( "invoice_no" == $column ) {
            echo get_post_meta( $post->ID, 'invoice_number', true );
        } elseif ( "invoice_type" == $column ) {
            wp_invoice_type( $post->ID );
        } elseif ( "amount" == $column ) {
            echo wp_invoice_format_amount( wp_invoice_get_invoice_total( $post->ID ) );
        } elseif ( "client" == $column ) {
            echo wp_invoice_get_invoice_client_edit( $post->ID );
        } elseif ( "status" == $column ) {
            echo wp_invoice_get_invoice_status( $post->ID );
        }
    }

    /**
     * Orders Invoice by Invoice Number, not date created
     *
     * @since 1.0.0
     *
     **/
    function wp_invoice_number_order( WP_Query $query ) {
        if ( ! is_admin() ) {
            return $query;
        }

        if ( $query->query['post_type'] == 'invoice' ) {
            $query->set( 'meta_key', 'invoice_number' );
            $query->set( 'meta_compare', '>=' );
            $query->set( 'meta_value', false );
            $query->set( 'orderby', 'meta_value' );
            $query->set( 'order', 'ASC' );
//			$query->set('post_status', 'publish,pending,draft,future,private' );
        }

        return $query;
    }

    /**
     * Adds filters to Invoice Columns
     *
     * @since 1.0.0
     *
     **/
    function invoice_columns_filter() {
        global $wp, $post;
        $post_type = $wp->query_vars['post_type'];

        if ( $post_type == 'invoice' ) {
            $the_terms = get_terms( 'client', 'orderby=name&hide_empty=0' );

            $content = '<select name="client" id="client" class="postform">';
            $content .= '<option value="0">' . __( 'View all Clients', 'wp-invoice-pro' ) . '</option>';
            foreach ( $the_terms as $term ) {
                $content .= '<option value="' . $term->slug . '">' . $term->name . ' ( ' . $term->count . ' )</option>';
            }
            $content .= '</select>';

            $content = str_replace( 'post_tag', 'tag', $content );
            echo $content;
        }
    }

    /**
     * action Init function
     *
     * @since 1.0.0
     *
     **/
    function action_init() {
        // 1. flush and refresh permalinks
        global $wp_rewrite;
        $wp_rewrite->flush_rewrite_rules();

        // 2. Rewrite Permalinks
        $rewrite_rules               = $wp_rewrite->generate_rewrite_rules( 'invoice/' );
        $rewrite_rules['invoice/?$'] = 'index.php?paged=1';

        foreach ( $rewrite_rules as $regex => $redirect ) {
            if ( strpos( $redirect, 'attachment=' ) === false ) {
                $redirect .= '&post_type=invoice';
            }
            if ( 0 < preg_match_all( '@\$([0-9])@', $redirect, $matches ) ) {
                for ( $i = 0; $i < count( $matches[0] ); $i ++ ) {
                    $redirect = str_replace( $matches[0][ $i ], '$matches[' . $matches[1][ $i ] . ']', $redirect );
                }
            }
            $wp_rewrite->add_rule( $regex, $redirect, 'top' );
        }

        // 3. flush and refresh permalinks
        global $wp_rewrite;
        $wp_rewrite->flush_rewrite_rules();
    }

    /**
     * Meta Box: Invoice Details
     *
     * @author Sawyer Hollenshead
     * @since 1.0.0
     *
     **/
    function invoice_details() {
        global $post;

        // Use nonce for verification
        echo '<input type="hidden" name="ei_noncename" id="ei_noncename" value="' . wp_create_nonce( 'ei-n' ) . '" />';
        ?>

        <ul>

            <li class="normal-detail">
                <label><?php _e( 'ID Number:', 'wp-invoice-pro' ); ?> </label>
                <div class="front">
                    <span><?php wp_invoice_number(); ?></span>
                    <a href="#" class="wp_invoice-edit"><?php _e( 'Edit', 'wp-invoice-pro' ); ?></a>
                </div>
                <div class="back">
                    <input type="text" name="invoice-number" id="invoice-number"
                           value="<?php wp_invoice_number(); ?>" size="10"/>
                    <a href="#"
                       class="button wp_invoice-ok"><?php _e( 'OK', 'wp-invoice-pro' ); ?></a>
                    <a href="#"
                       class="wp_invoice-cancel"><?php _e( 'Cancel', 'wp-invoice-pro' ); ?></a>
                </div>
            </li>

            <li class="normal-detail">
                <label><?php _e( 'Type:', 'wp-invoice-pro' ); ?> </label>
                <div class="front">
                    <span><?php wp_invoice_type(); ?></span>
                    <a href="#" class="wp_invoice-edit"><?php _e( 'Edit', 'wp-invoice-pro' ); ?></a>
                </div>
                <div class="back">
                    <select name="invoice-type" id="invoice-type">
                        <option value="<?php _e( 'Invoice', 'wp-invoice-pro' ); ?>" <?php if ( wp_invoice_get_invoice_type() == __( 'Invoice', 'wp-invoice-pro' ) ) {
                            echo 'selected="selected"';
                        } ?>><?php _e( 'Invoice', 'wp-invoice-pro' ); ?></option>
                        <option value="<?php _e( 'Quote', 'wp-invoice-pro' ); ?>" <?php if ( wp_invoice_get_invoice_type() == __( 'Quote', 'wp-invoice-pro' ) ) {
                            echo 'selected="selected"';
                        } ?>><?php _e( 'Quote', 'wp-invoice-pro' ); ?></option>
                    </select>
                    <a href="#"
                       class="button wp_invoice-ok"><?php _e( 'OK', 'wp-invoice-pro' ); ?></a>
                    <a href="#"
                       class="wp_invoice-cancel"><?php _e( 'Cancel', 'wp-invoice-pro' ); ?></a>
                </div>
            </li>

            <li class="normal-detail">
                <label><?php _e( 'Tax:', 'wp-invoice-pro' ); ?> </label>
                <div class="front">
                    <span><?php wp_invoice_tax(); ?></span>
                    <a href="#" class="wp_invoice-edit"><?php _e( 'Edit', 'wp-invoice-pro' ); ?></a>
                </div>
                <div class="back">
                    <input type="text" name="invoice-tax" id="invoice-tax"
                           value="<?php wp_invoice_tax(); ?>" size="2"/>
                    <a href="#"
                       class="button wp_invoice-ok update-subtotal"><?php _e( 'OK', 'wp-invoice-pro' ); ?></a>
                    <a href="#"
                       class="wp_invoice-cancel update-subtotal"><?php _e( 'Cancel', 'wp-invoice-pro' ); ?></a>
                </div>
            </li>

            <li class="date-detail">
                <label><?php _e( 'Sent:', 'wp-invoice-pro' ); ?> </label>
                <div class="front">
                    <span><?php echo wp_invoice_get_invoice_sent_pretty(); ?></span>
                    <a href="#" class="wp_invoice-edit"><?php _e( 'Edit', 'wp-invoice-pro' ); ?></a>
                </div>
                <div class="back">
                    <select name="wp-invoice-mm" id="wp-invoice-mm">
                        <option></option>
                        <option value="01"><?php _e( 'Jan', 'wp-invoice-pro' ); ?></option>
                        <option value="02"><?php _e( 'Feb', 'wp-invoice-pro' ); ?></option>
                        <option value="03"><?php _e( 'Mar', 'wp-invoice-pro' ); ?></option>
                        <option value="04"><?php _e( 'Apr', 'wp-invoice-pro' ); ?></option>
                        <option value="05"><?php _e( 'May', 'wp-invoice-pro' ); ?></option>
                        <option value="06"><?php _e( 'Jun', 'wp-invoice-pro' ); ?></option>
                        <option value="07"><?php _e( 'Jul', 'wp-invoice-pro' ); ?></option>
                        <option value="08"><?php _e( 'Aug', 'wp-invoice-pro' ); ?></option>
                        <option value="09"><?php _e( 'Sep', 'wp-invoice-pro' ); ?></option>
                        <option value="10"><?php _e( 'Oct', 'wp-invoice-pro' ); ?></option>
                        <option value="11"><?php _e( 'Nov', 'wp-invoice-pro' ); ?></option>
                        <option value="12"><?php _e( 'Dec', 'wp-invoice-pro' ); ?></option>
                    </select>
                    <input type="text" maxlength="2" size="1" value="" name="wp-invoice-dd"
                           id="wp-invoice-dd"/>,
                    <input type="text" maxlength="4" size="3" value="" name="wp-invoice-yyyy"
                           id="wp-invoice-yyyy"/>
                    <input type="hidden" name="invoice-sent" id="invoice-sent"
                           value="<?php echo wp_invoice_get_invoice_sent(); ?>"/>

                    <a href="#"
                       class="button wp_invoice-ok"><?php _e( 'OK', 'wp-invoice-pro' ); ?></a>
                    <a href="#"
                       class="wp_invoice-clear"><?php _e( 'Reset', 'wp-invoice-pro' ); ?></a>
                    <a href="#"
                       class="wp_invoice-cancel"><?php _e( 'Cancel', 'wp-invoice-pro' ); ?></a>
                </div>
            </li>

            <li class="date-detail">
                <label><?php _e( 'Paid:', 'wp-invoice-pro' ); ?> </label>
                <div class="front">
                    <span><?php echo wp_invoice_get_invoice_paid_pretty(); ?></span>
                    <a href="#" class="wp_invoice-edit"><?php _e( 'Edit', 'wp-invoice-pro' ); ?></a>
                </div>
                <div class="back">
                    <select name="wp-invoice-mm" id="wp-invoice-mm">
                        <option></option>
                        <option value="01"><?php _e( 'Jan', 'wp-invoice-pro' ); ?></option>
                        <option value="02"><?php _e( 'Feb', 'wp-invoice-pro' ); ?></option>
                        <option value="03"><?php _e( 'Mar', 'wp-invoice-pro' ); ?></option>
                        <option value="04"><?php _e( 'Apr', 'wp-invoice-pro' ); ?></option>
                        <option value="05"><?php _e( 'May', 'wp-invoice-pro' ); ?></option>
                        <option value="06"><?php _e( 'Jun', 'wp-invoice-pro' ); ?></option>
                        <option value="07"><?php _e( 'Jul', 'wp-invoice-pro' ); ?></option>
                        <option value="08"><?php _e( 'Aug', 'wp-invoice-pro' ); ?></option>
                        <option value="09"><?php _e( 'Sep', 'wp-invoice-pro' ); ?></option>
                        <option value="10"><?php _e( 'Oct', 'wp-invoice-pro' ); ?></option>
                        <option value="11"><?php _e( 'Nov', 'wp-invoice-pro' ); ?></option>
                        <option value="12"><?php _e( 'Dec', 'wp-invoice-pro' ); ?></option>
                    </select>
                    <input type="text" maxlength="2" size="1" value="31" name="wp-invoice-dd"
                           id="wp-invoice-dd"/>,
                    <input type="text" maxlength="4" size="3" value="2010" name="wp-invoice-yyyy"
                           id="wp-invoice-yyyy"/>
                    <input type="hidden" name="invoice-paid" id="invoice-paid"
                           value="<?php echo wp_invoice_get_invoice_paid(); ?>"/>

                    <a href="#"
                       class="button wp_invoice-ok"><?php _e( 'OK', 'wp-invoice-pro' ); ?></a>
                    <a href="#"
                       class="wp_invoice-clear"><?php _e( 'Reset', 'wp-invoice-pro' ); ?></a>
                    <a href="#"
                       class="wp_invoice-cancel"><?php _e( 'Cancel', 'wp-invoice-pro' ); ?></a>
                </div>
            </li>

            <li class="date-detail">
                <label><?php _e( 'Quote Approved:', 'wp-invoice-pro' ); ?> </label>
                <div class="front">
                    <span><?php echo wp_invoice_get_quote_approved_pretty(); ?></span>
                    <a href="#" class="wp_invoice-edit"><?php _e( 'Edit', 'wp-invoice-pro' ); ?></a>
                </div>
                <div class="back">
                    <select name="wp-invoice-mm" id="wp-invoice-mm">
                        <option></option>
                        <option value="01"><?php _e( 'Jan', 'wp-invoice-pro' ); ?></option>
                        <option value="02"><?php _e( 'Feb', 'wp-invoice-pro' ); ?></option>
                        <option value="03"><?php _e( 'Mar', 'wp-invoice-pro' ); ?></option>
                        <option value="04"><?php _e( 'Apr', 'wp-invoice-pro' ); ?></option>
                        <option value="05"><?php _e( 'May', 'wp-invoice-pro' ); ?></option>
                        <option value="06"><?php _e( 'Jun', 'wp-invoice-pro' ); ?></option>
                        <option value="07"><?php _e( 'Jul', 'wp-invoice-pro' ); ?></option>
                        <option value="08"><?php _e( 'Aug', 'wp-invoice-pro' ); ?></option>
                        <option value="09"><?php _e( 'Sep', 'wp-invoice-pro' ); ?></option>
                        <option value="10"><?php _e( 'Oct', 'wp-invoice-pro' ); ?></option>
                        <option value="11"><?php _e( 'Nov', 'wp-invoice-pro' ); ?></option>
                        <option value="12"><?php _e( 'Dec', 'wp-invoice-pro' ); ?></option>
                    </select>
                    <input type="text" maxlength="2" size="1" value="31" name="wp-invoice-dd"
                           id="wp-invoice-dd"/>,
                    <input type="text" maxlength="4" size="3" value="2010" name="wp-invoice-yyyy"
                           id="wp-invoice-yyyy"/>
                    <input type="hidden" name="quote_approved" id="quote_approved"
                           value="<?php echo wp_invoice_get_quote_approved(); ?>"/>

                    <a href="#"
                       class="button wp_invoice-ok"><?php _e( 'OK', 'wp-invoice-pro' ); ?></a>
                    <a href="#"
                       class="wp_invoice-clear"><?php _e( 'Reset', 'wp-invoice-pro' ); ?></a>
                    <a href="#"
                       class="wp_invoice-cancel"><?php _e( 'Cancel', 'wp-invoice-pro' ); ?></a>
                </div>
            </li>

        </ul>

        <input type="hidden" name="wp_invoice_hidden_currency" id="wp_invoice_hidden_currency"
               value="<?php wp_invoice_currency_format(); ?>"/>
        <input type="hidden" name="wp_invoice_hidden_tax" id="wp_invoice_hidden_tax"
               value="<?php wp_invoice_tax(); ?>"/>
        <input type="hidden" name="wp_invoice_hidden_permalink" id="wp_invoice_hidden_permalink"
               value="<?php echo wp_invoice_get_permalink(); ?>"/>
        <input type="hidden" name="wp_invoice_hidden_password" id="wp_invoice_hidden_password"
               value="<?php echo wp_invoice_get_invoice_client_password(); ?>"/>
        <?php
    }

    /*--------------------------------------------------------------------------------------------
                                        Send Invoice
    --------------------------------------------------------------------------------------------*/
    function invoice_send() {
        global $post;
        ?>
        <?php if ( isset( $_GET['sent'] ) && $_GET['sent'] == 'success' ): ?>
            <div class="updated">
                <p><?php _e( 'Invoice sent successfully!', 'wp-invoice-pro' ); ?></p>
            </div>
        <?php elseif ( isset( $_GET['sent'] ) && $_GET['sent'] == 'fail' ): ?>
            <div class="error">
                <p><?php _e( 'Invoice failed to send.', 'wp-invoice-pro' ); ?></p>
            </div>
        <?php endif; ?>
        <ul>
            <li>
                <?php if ( wp_invoice_get_invoice_client_name() ): ?>
                    <?php if ( wp_invoice_get_invoice_client_email() ): ?>
                        <a href="<?php echo add_query_arg( 'email', 'send', get_permalink( $post->ID ) ); ?>"
                           class="button"><?php _e( 'Send Email', 'wp-invoice-pro' ); ?></a> <?php _e( 'to', 'wp-invoice-pro' ); ?><?php wp_invoice_client_email(); ?>
                        <a href="<?php wp_invoice_client_edit_link(); ?>"><?php _e( 'Edit Client', 'wp-invoice-pro' ); ?></a>
                    <?php else: ?>
                        <a class="button disabled"><?php _e( 'Send Email', 'wp-invoice-pro' ); ?></a> <?php _e( 'no email address', 'wp-invoice-pro' ); ?>
                        <a href="<?php wp_invoice_client_edit_link(); ?>"><?php _e( 'Edit Client', 'wp-invoice-pro' ); ?></a>
                    <?php endif; ?>
                <?php else: ?>
                    <a class="button disabled"><?php _e( 'Send Email', 'wp-invoice-pro' ); ?></a> <?php _e( 'No Client Selected', 'wp-invoice-pro' ); ?>
                <?php endif; ?>
            </li>
        </ul>
        <?php
    }

    /*--------------------------------------------------------------------------------------------
                                            Project Breakdown
    --------------------------------------------------------------------------------------------*/
    function project_breakdown() {
        global $post, $detailTitle;
        $detailCount = 0;

        $detailCount = count( $detailTitle );

        wp_nonce_field( basename( __FILE__ ), 'wp-invoice-project' ); ?>

        <div class="detail detail-header">
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td class="title"><?php _e( 'Title', 'wp-invoice-pro' ); ?></td>
                    <td class="description"><?php _e( 'Description', 'wp-invoice-pro' ); ?></td>
                    <td class="type"><?php _e( 'Type', 'wp-invoice-pro' ); ?></td>
                    <td class="rate"><?php _e( 'Rate', 'wp-invoice-pro' ); ?><span
                                class="hr"></span></td>
                    <td class="duration"><?php _e( 'Quantity', 'wp-invoice-pro' ); ?></td>
                    <td class="subtotal"><?php _e( 'Subtotal', 'wp-invoice-pro' ); ?></td>
                </tr>
            </table>
        </div>
        <div class="details">
            <?php if ( wp_invoice_has_details() ) : ?>
                <?php while ( wp_invoice_detail() ) : ?>
                    <div class="detail">
                        <table cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td class="title"><input type="text" name="detail-title[]"
                                                         id="detail-title"
                                                         value="<?php wp_invoice_the_detail_title(); ?>"/>
                                </td>
                                <td class="description"><textarea name="detail-description[]"
                                                                  id="detail-description"><?php echo wp_invoice_get_the_detail_description(); ?></textarea>
                                </td>
                                <td class="type">
                                    <select name="detail-type[]" id="detail-type">
                                        <option value="<?php _e( 'Timed', 'wp-invoice-pro' ); ?>" <?php if ( wp_invoice_get_the_detail_type() == __( 'Timed', 'wp-invoice-pro' ) ) {
                                            echo 'selected="selected"';
                                        } ?>>Timed
                                        </option>
                                        <option value="<?php _e( 'Fixed', 'wp-invoice-pro' ); ?>" <?php if ( wp_invoice_get_the_detail_type() == __( 'Fixed', 'wp-invoice-pro' ) ) {
                                            echo 'selected="selected"';
                                        } ?>>Fixed
                                        </option>
                                    </select>
                                </td>
                                <td class="rate">
                                    <input size="2"
                                           onBlur="if (this.value == '' ) {this.value = '0.00';}"
                                           onFocus="if ( this.value == '0.00' ) {this.value = '';}"
                                           type="text" name="detail-rate[]" id="detail-rate"
                                           value="<?php echo wp_invoice_get_the_detail_rate(); ?>"/>
                                </td>
                                <td class="duration">
                                    <input size="2"
                                           onBlur="if (this.value == '' ) {this.value = '0.0';}"
                                           onFocus="if ( this.value == '0.0' ) {this.value = '';}"
                                           type="text" name="detail-duration[]" id="detail-duration"
                                           value="<?php wp_invoice_the_detail_duration(); ?>"/>
                                </td>
                                <td class="subtotal">
                                    <input type="hidden" name="detail-subtotal[]"
                                           id="detail-subtotal"
                                           value="<?php wp_invoice_the_detail_subtotal(); ?>"/>
                                    <p><?php echo wp_invoice_format_amount( '<span id="detail-subtotal">' . wp_invoice_get_the_detail_subtotal() . '</span>' ); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="6">
                                    <a class="delete" href="#"
                                       title="Remove Detail"><?php _e( 'Remove', 'wp-invoice-pro' ); ?></a>
                                    <div class="grab"><?php _e( 'Reorder', 'wp-invoice-pro' ); ?></div>
                                </td>
                            </tr>

                        </table>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="detail">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td class="title"><input type="text" name="detail-title[]"
                                                     id="detail-title"/></td>
                            <td class="description"><textarea name="detail-description[]"
                                                              id="detail-description"></textarea>
                            </td>
                            <td class="type">
                                <select name="detail-type[]" id="detail-type">
                                    <option value="Timed"><?php _e( 'Timed', 'wp-invoice-pro' ); ?></option>
                                    <option value="Fixed"><?php _e( 'Fixed', 'wp-invoice-pro' ); ?></option>
                                </select>
                            </td>
                            <td class="rate">
                                <input size="2"
                                       onBlur="if (this.value == '' ) {this.value = '0.00';}"
                                       onFocus="if ( this.value == '0.00' ) {this.value = '';}"
                                       type="text" name="detail-rate[]" id="detail-rate"
                                       value="0.00"/>
                            </td>
                            <td class="duration">
                                <input size="2"
                                       onBlur="if (this.value == '' ) {this.value = '0.0';}"
                                       onFocus="if ( this.value == '0.0' ) {this.value = '';}"
                                       type="text" name="detail-duration[]" id="detail-duration"
                                       value="0.0"/>
                            </td>
                            <td class="subtotal">
                                <input type="hidden" name="detail-subtotal[]" id="detail-subtotal"
                                       value="0.00"/>
                                <p><?php echo wp_invoice_format_amount( '<span id="detail-subtotal">0.00</span>' ); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="6">
                                <a class="delete" href="#"
                                   title="Remove Detail"><?php _e( 'Remove', 'wp-invoice-pro' ); ?></a>
                                <div class="grab"><?php _e( 'Reorder', 'wp-invoice-pro' ); ?></div>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="detail detail-footer">
            <p>
                <strong><?php _e( 'Subtotal', 'wp-invoice-pro' ); ?>
                    :</strong> <?php echo wp_invoice_format_amount( '<span class="invoice-subtotal">' . wp_invoice_get_the_invoice_subtotal() . '</span>' ); ?>
                &nbsp;&nbsp;&nbsp;
                <?php //if ( wp_invoice_has_tax() ) :
                ?>
                <strong><?php _e( 'Tax', 'wp-invoice-pro' ); ?>
                    :</strong> <?php echo wp_invoice_format_amount( '<span class="invoice-tax">' . wp_invoice_get_the_invoice_tax() . '</span>' ); ?>
                &nbsp;&nbsp;&nbsp;
                <?php //endif;
                ?>
                <strong><?php _e( 'Total', 'wp-invoice-pro' ); ?>
                    :</strong> <?php echo wp_invoice_format_amount( '<span class="invoice-total">' . get_the_invoice_total() . '</span>' ); ?>
                &nbsp;&nbsp;&nbsp;
                <a class="add-detail button-primary" href="#"
                   title="Add New Row"><?php _e( 'Add New Row', 'wp-invoice-pro' ); ?></a>
            </p>
        </div>
        <?php
    }

    function create_meta_boxes() {
        add_meta_box( 'invoice_details', __( 'Invoice Details', 'wp-invoice-pro' ), [
            $this,
            'invoice_details',
        ], 'invoice', 'normal', 'low' );
        add_meta_box( 'project_breakdown', __( 'Project Breakdown', 'wp-invoice-pro' ), [
            $this,
            'project_breakdown',
        ], 'invoice', 'normal', 'high' );
        add_meta_box( 'invoice_send', __( 'Send Email', 'wp-invoice-pro' ), [
            $this,
            'invoice_send',
        ], 'invoice', 'side', 'low' );
    }

    /*--------------------------------------------------------------------------------------------
                                        Save
    --------------------------------------------------------------------------------------------*/
    function save_invoice( $post_id, $post ) {

        if ( $post->post_type != 'invoice' ) {
            return $post_id;
        }

        if ( wp_is_post_revision( $post_id ) ) {
            return $post_id;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }

        // verify this with nonce because save_post can be triggered at other times
        if ( ! isset( $_REQUEST['wp-invoice-project'] ) || ! wp_verify_nonce( $_REQUEST['wp-invoice-project'], basename( __FILE__ ) ) ) {
            return $post_id;
        }

        // do not save if this is an auto save routine
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        $meta = [
            'invoice_number' => $_POST['invoice-number'],
            'invoice_type' => $_POST['invoice-type'],
            'invoice_tax' => $_POST['invoice-tax'],
            'invoice_sent' => $_POST['invoice-sent'],
            'invoice_paid' => $_POST['invoice-paid'],
            'quote_approved' => $_POST['quote_approved'],
            'detail_title' => $_POST['detail-title'],
            'detail_description' => $_POST['detail-description'],
            'detail_type' => $_POST['detail-type'],
            'detail_rate' => $_POST['detail-rate'],
            'detail_duration' => $_POST['detail-duration'],
            'detail_subtotal' => $_POST['detail-subtotal'],
        ];

        foreach ( $meta as $meta_key => $new_meta_value ) {

            /* Get the meta value of the custom field key. */
            $meta_value = get_post_meta( $post_id, $meta_key, true );

            /* If there is no new meta value but an old value exists, delete it. */
            if ( current_user_can( 'delete_post_meta', $post_id, $meta_key ) && '' == $new_meta_value && $meta_value ) {
                delete_post_meta( $post_id, $meta_key, $meta_value );
            } /* If a new meta value was added and there was no previous value, add it. */
            elseif ( current_user_can( 'add_post_meta', $post_id, $meta_key ) && $new_meta_value && '' == $meta_value ) {
                add_post_meta( $post_id, $meta_key, $new_meta_value, true );
            } /* If the new meta value does not match the old value, update it. */
            elseif ( current_user_can( 'edit_post_meta', $post_id, $meta_key ) && $new_meta_value && $new_meta_value != $meta_value ) {
                update_post_meta( $post_id, $meta_key, $new_meta_value );
            }
        }
    }

    /**
     * Invoice Template Redirect
     *
     * @author Sawyer Hollenshead
     * @since 1.0.0
     *
     **/
    function invoice_template_redirect() {
        // define invoice url variables
        global $wp, $post;

        $post_type = get_query_var( 'post_type' );

        $email    = ( isset( $_GET['email'] ) ) ? $_GET['email'] : false;
        $paid     = ( isset( $_GET['diap'] ) ) ? $_GET['diap'] : false;
        $approved = ( isset( $_GET['approved'] ) ) ? $_GET['approved'] : false;

        if ( $post_type === 'invoice' ) {
            if ( $paid === 'yes' ) {
                update_post_meta( $post->ID, 'invoice_paid', date( 'd/m/Y' ) );
            }

            if ( $approved === 'yes' ) {
                $approved_template = trailingslashit( get_stylesheet_directory() ) . 'wp-invoice/email/approved.php';
                if ( ! file_exists( $approved_template ) ) {
                    $approved_template = trailingslashit( $this->plugin_dir ) . 'template/email/approved.php';
                }

                update_post_meta( $post->ID, 'quote_approved', date_i18n( 'd/m/Y' ) );
                ob_start();
                include( $approved_template );
                $message = ob_get_clean();
                include( trailingslashit( $this->plugin_dir ) . 'functions/email-approved.php' );
                exit;
            } else if ( $approved === 'reset' ) {
                delete_post_meta( $post->ID, 'quote_approved' );
            }

//			$this->invoice_security();
            if ( $email === 'send' ) {
                $email_template = trailingslashit( get_stylesheet_directory() ) . 'wp-invoice/email/email.php';
                if ( ! file_exists( $email_template ) ) {
                    $email_template = trailingslashit( $this->plugin_dir ) . 'template/email/email.php';
                }

                // get html email and store as variable for sending
                ob_start();
                include( $email_template );
                $message = ob_get_clean();
                include( trailingslashit( $this->plugin_dir ) . 'functions/email-client.php' );
                exit;
            }
        }
    }

    /**
     * Invoice Security
     *
     * @since 1.0.0
     */
    function invoice_security() {
        if ( post_password_required( get_the_ID() ) ) :
            ob_start(); ?>
            <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo( 'charset' ); ?>"/>

            <title><?php wp_title( '|', true, 'right' ); ?></title>

            <link rel="profile" href="http://gmpg.org/xfn/11"/>
            <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>"/>

            <link rel="stylesheet"
                  href="<?php echo trailingslashit( $this->plugin_path ) . 'css/style.css'; ?>"
                  type="text/css" media="screen">
            <script type="text/javascript"
                    src="<?php echo trailingslashit( $this->plugin_path ) . 'js/modernizr-2.7.1.min.js'; ?>"></script>
        </head>
        <body>
        <form method="post"
              action="<?php echo esc_url( add_query_arg( 'action', 'postpass', wp_login_url() ) ); ?>"
              id="password">
            <h1><?php _e( 'This', 'wp-invoice-pro' ); ?> <?php wp_invoice_type(); ?> <?php _e( 'is Password Protected', 'wp-invoice-pro' ); ?>
                .</h1>
            <input type="text" id="pwbox-<?php echo intval( get_the_ID() ); ?>" name="post_password"
                   value="<?php _e( 'Password', 'wp-invoice-pro' ); ?>"
                   onfocus="if ( this.value == '<?php _e( 'Password', 'wp-invoice-pro' ); ?>' ) {this.value = '';this.type='password'}"
                   onblur="if (this.value == '' ) {this.value = '<?php _e( 'Password', 'wp-invoice-pro' ); ?>'; this.type='text'}"
                   autocomplete="off">
            <input type="submit" value="Submit" name="Submit" class="btn"/>
            <p style="margin-top:8px"><a href="<?php echo isset( $_SERVER['HTTP_REFERER'] ) ?
                    esc_url( $_SERVER['HTTP_REFERER'] ) : ''; ?>" onclick="history.go(-1)">Go
                    Back</a></p>
        </form>
        </body>
            </html><?php
            echo ob_get_clean();
            exit;
        endif;
    }

    /**
     * Check if user is logged in an current user is post author.
     *
     * @since 2.1.4
     */
    function is_user_logged_in_current_author() {
        global $post, $current_user;

        if ( is_user_logged_in() && // Force user validation... STUFF
             ! empty( $post ) && // Check if a $post exists in globals
             ! empty( $current_user ) && // Check if current_user exists in globals
             ( $current_user->ID == $post->post_author ) // Match User IDs
        ) {
            return true;
        } else {
            return false;
        }
    }

}