<?php 
/* 
 * Dashboard Template
 *
 * @since 1.0.0
 * 
 * Retrieves the client ID and displays the archive for that client.
 * If no ID is sent, a search box is displayed.
 *
 **/

/* Check for the Client ID */
$key		= false;
$verify 	= false;
$query_id 	= false;

if ( isset( $_GET['client_id'] ) && isset( $_GET['key'] ) ) {
	
	$client_id 	= $_GET['client_id'];
	$key		= $_GET['key'];
	
	// Verify client and key match
	if ( $key == wp_invoice_get_key( $client_id ) ) {	
		// Check if cookie isset and if it doesn't match the GET-client
		if ( !isset( $_COOKIE['wp_invoice_client_id'] ) || $_COOKIE['wp_invoice_client_id'] != $client_id ) {
			wp_invoice_setcookie( 'wp_invoice_client_id', $client_id );
		}
		// Set $query_id
		$query_id = $client_id;
	}
	
	$verify = true;
	
} elseif ( isset( $_COOKIE['wp_invoice_client_id'] ) ) {
	$query_id = $_COOKIE['wp_invoice_client_id'];
	$verify = false;
	
} else {
	$query_id = false;
	$verify = true;
}

// Get the header
include_once( trailingslashit( WP_INVOICE_DIR ) . 'template/header.php' );

// Get the menu
wp_invoice_get_menu( $query_id, $key, $verify );

if ( $query_id ) :

	$args = array(
		'posts_per_page' 	=> -1,
		'post_type'			=> 'invoice',
		'tax_query' 		=> array(
			array(
				'taxonomy'	=> 'client',
				'field' 	=> 'id',
				'terms' 	=> array( $query_id )
			)
		)
	);
	$the_query = new WP_Query( $args );
	
	if ( $the_query->have_posts() ) : ?>
	
		<header>
			<section class="mini last">
				<?php $term = get_term( $query_id, 'client' ); ?>
				<p><?php echo $term->name; ?></p>
				<h1>Dashboard</h1>
			</section>
		</header><!-- #invoice-header -->
		
        <?php wp_invoice_table_list( $the_query ); ?>
		
	<?php else: // No posts available ?>
		<header>
			<section class="mini last">
				<h1><?php _e( 'Dashboard', 'wp-invoice-pro' ); ?></h1>
			</section>
		</header><!-- #invoice-header -->
		
		<section class="page-entry">
			<?php _e( 'Sorry we couldn\'t find anything. ', 'wp-invoice-pro' ); ?>
		</section>     
        <?php wp_invoice_search_form(); ?>
    
	<?php endif; // has_posts() ?>

<?php else : // Didn't supply a query term ?>
	<header>
		<section class="mini last">
			<h1><?php _e( 'Dashboard', 'wp-invoice-pro' ); ?></h1>
		</section>
	</header><!-- #invoice-header -->
	
	<section class="alert">
		<?php 
		// A client ID and key weren't provided
		if ( $query_id == $key ) : ?>
			<p><strong><?php _e( 'Error:', 'wp-invoice-pro' ); ?></strong> <?php _e( 'The Dashboard URL must consist of your client ID and key. Please contact us if you think this is a mistake.', 'wp-invoice-pro' ); ?></p>
		<?php 
		// A key wasn't provided
		elseif ( !$query_id ) : ?>
			<p><strong><?php _e( 'Error:', 'wp-invoice-pro' ); ?></strong> <?php _e( 'Your key is invalid. Please contact us if you think this is a mistake.', 'wp_invoice_invoice' ); ?></p>
		<?php else: ?>
			<p><strong><?php _e( 'Error:', 'wp-invoice-pro' ); ?></strong> <?php _e( 'We couldn\'t find your information. Please contact us if you think this is a mistake.', 'wp_invoice_invoice' ); ?></p>
		<?php endif; ?>
	</section>
    
    <?php if ( is_user_logged_in() ) : // If we're logged in let show ALL invoices. ?>
    
    <?php if ( !current_user_can( 'read_private_posts' ) ) : // If we're not Admins lets NOT show all invoice. ?>
    
		<?php $args = array(
            'posts_per_page'	=> -1,
            'post_type'			=> 'invoice'
        );
        $the_query = new WP_Query( $args );
        
        if  ( $the_query->have_posts() ) : ?>
            
            <?php wp_invoice_table_list( $the_query, true ); ?>
        
        <?php endif; ?>
        
    <?php endif; endif; ?>
	
    <?php wp_invoice_search_form( true ); ?>
    
<?php endif; // $query_term ?>

<?php include_once( trailingslashit( WP_INVOICE_DIR ) . 'template/footer.php' ); ?>