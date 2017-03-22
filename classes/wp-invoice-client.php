<?php 

class WP_Invoice_Client {
	
	var $name,
		$dir,
		$plugin_dir,
		$plugin_path;
	
	/**
	 * Invoice Constructor
	 *
	 * @since 1.0.0
	 * 
	 * @param object: wp_invoice_invoice to find parent variables.
	 **/
	function __construct( $parent )
	{
		$this->name			= $parent->name;							// Plugin Name
		$this->dir 			= dirname( plugin_dir_path( __FILE__ ) );	// This directory
		$this->plugin_dir 	= $parent->dir;								// Plugin directory
		$this->plugin_path 	= $parent->path;							// Plugin Absolute Path		
		
		// Init
		add_action( 'init',							array( $this, 'create_taxonomy' ) );
		
		// Client extra fields
		add_action( 'client_add_form_fields', 		array( $this, 'add_client' ), 10, 2 );
		add_action( 'client_edit_form_fields', 		array( $this, 'edit_client' ), 10, 2 );		
		
		// Edit, Create, Delete Client
		add_action( 'edit_client', 					array( $this, 'save_client' ), 10, 2 );
		add_action( 'create_client', 					array( $this, 'save_client' ), 10, 2 );
		add_action( 'delete_client', 					array( $this, 'delete_client' ), 10, 2 );
		
		// Client Columns
		add_filter( 'manage_edit-client_columns',	array( $this, 'client_columns_setup' ), 10, 1 );
		add_filter( 'manage_client_custom_column',	array( $this, 'client_columns_data' ), 10, 3 );
		
		// Client Taxonomy Table
		add_action( 'init',							array( $this,'taxonomy_metadata_wpdbfix' ) );
		add_action( 'switch_blog',					array( $this,'taxonomy_metadata_wpdbfix' ) );
		
		return true;
	}
	
	/**
	 * Creates Custom Taxonomy: Client
	 *
	 * @since 1.0.0
	 * 
	 **/
	function create_taxonomy()
	{
		$labels = array(
			'name'				=> _x( 'Clients', 'taxonomy general name' ),
			'singular_name'		=> _x( 'Client', 'taxonomy singular name' ),
			'search_items'		=>  __( 'Search Clients' ),
			'all_items'			=> __( 'All Clients' ),
			'parent_item' 		=> __( 'Parent Client' ),
			'parent_item_colon' => __( 'Parent Client:' ),
			'edit_item' 		=> __( 'Edit Client' ), 
			'update_item' 		=> __( 'Update Client' ),
			'add_new_item' 		=> __( 'Add New Client' ),
			'new_item_name' 	=> __( 'New Client Name' ),
		);
		
		register_taxonomy( 'client', 'invoice',
			array(
				'hierarchical'		=> true,
				'labels'			=> $labels,
				'query_var'			=> true,
				'rewrite'			=> false,
				'show_in_nav_menus'	=> false,
			)
		);
		
	}
	
	/**
	 * Add Extra Fields to Add Client
	 *
	 * @since 1.0.0
	 * 
	 **/
	function add_client( $tag )
	{
		?>
		<div class="form-field">
			<label for="client_email"><?php _e( 'Email Address', 'wp-invoice-pro' ); ?></label>
			<input type="text" id="client_email" name="client_email" size="40" value="">
		</div>
        <div class="form-field">
			<label for="client_password"><?php _e( 'Password', 'wp-invoice-pro' ); ?></label>
			<input type="text" id="client_password" name="client_password" size="40" value="">
		</div>
		<div class="form-field">
			<label for="client_business"><?php _e( 'Business Name', 'wp-invoice-pro' ); ?></label>
			<input type="text" id="client_business" name="client_business" size="40" value="">
		</div>
		<div class="form-field">
			<label for="client_address"><?php _e( 'Business Address', 'wp-invoice-pro' ); ?></label>
			<textarea id="client_address" name="client_address" cols="40" value="" rows="5"></textarea>
		</div>
        <div class="form-field">
			<label for="client_phone"><?php _e( 'Phone Number', 'wp-invoice-pro' ); ?></label>
			<input type="text" id="client_phone" name="client_phone" size="40" value="">
		</div>
        <div class="form-field">
			<label for="client_number"><?php _e( 'Client Number', 'wp-invoice-pro' ); ?></label>
			<input type="text" id="client_number" name="client_number" size="40" value="">
		</div>
		<!-- HIDDEN --
        <div class="form-field">
			<label for="stripe_client_id"><?php _e( 'Stripe Client ID', 'wp-invoice-pro' ); ?></label>
			<input type="text" id="stripe_client_id" name="stripe_client_id" size="40" value="" readonly>
		</div>
		-->
		
		<?php
	}
	
	/**
	 * Add Extra Fields to Edit Client
	 *
	 * @since 1.0.0
	 * 
	 **/
	function edit_client( $tag )
	{
		$client_email		= get_term_meta( $tag->term_id, 'client_email', true );
		$client_password 	= get_term_meta( $tag->term_id, 'client_password', true ); 
		$client_business 	= get_term_meta( $tag->term_id, 'client_business', true );
		$client_address 	= get_term_meta( $tag->term_id, 'client_address', true );
		$client_phone 		= get_term_meta( $tag->term_id, 'client_phone', true );
		$client_number 	= get_term_meta( $tag->term_id, 'client_number', true );
		$stripe_client_id 	= get_term_meta( $tag->term_id, 'stripe_client_id', true );
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="client_email"><?php _e( 'Email Address', 'wp-invoice-pro' ); ?></label></th>
			<td><input type="text" id="client_email" name="client_email" size="40" value="<?php echo $client_email; ?>"></td>
		</tr>
        <tr class="form-field">
			<th scope="row" valign="top"><label for="client_password"><?php _e( 'Password', 'wp-invoice-pro' ); ?></label></th>
			<td><input type="text" id="client_password" name="client_password" size="40" value="<?php echo $client_password; ?>"></td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="client_business"><?php _e( 'Business Name', 'wp-invoice-pro' ); ?></label></th>
			<td><input type="text" id="client_business" name="client_business" size="40" value="<?php echo $client_business; ?>"></td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="client_address"><?php _e( 'Business Address', 'wp-invoice-pro' ); ?></label></th>
			<td><textarea id="client_address" name="client_address" cols="40" value="" rows="5"><?php echo $client_address; ?></textarea></td>
		</tr>
        <tr class="form-field">
			<th scope="row" valign="top"><label for="client_phone"><?php _e( 'Phone Number', 'wp-invoice-pro' ); ?></label></th>
			<td><input type="text" id="client_phone" name="client_phone" size="40" value="<?php echo $client_phone; ?>"></td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="client_number"><?php _e( 'Client Number', 'wp-invoice-pro' ); ?></label></th>
			<td><input type="text" id="client_number" name="client_number" size="40" value="<?php echo $client_number; ?>"><br />
            <span class="description"><?php _e( 'Could be used as a VAT Number', 'wp-invoice-pro' ); ?></span></td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="stripe_client_id"><?php _e( 'Stripe Client ID', 'wp-invoice-pro' ); ?></label></th>
			<td><input type="text" id="stripe_client_id" name="stripe_client_id" size="40" value="<?php echo $stripe_client_id; ?>" readonly></td>
		</tr>
		<?php
	}
	
	/**
	 * Save Extra Fields for Client
	 *
	 * @since 1.0.0
	 * 
	 **/ 
	function save_client( $term_id, $tt_id )
	{
		if ( !$term_id ) return;

		if ( isset( $_POST['client_email'] ) )
			update_term_meta( $term_id, 'client_email', $_POST['client_email'] );
			
		if ( isset( $_POST['client_password'] ) )
			update_term_meta( $term_id, 'client_password', $_POST['client_password'] );
			
		if ( isset( $_POST['client_business'] ) )
			update_term_meta( $term_id, 'client_business', $_POST['client_business'] );
	
		if ( isset( $_POST['client_address'] ) )
			update_term_meta( $term_id, 'client_address', $_POST['client_address'] );
		
		if ( isset( $_POST['client_phone'] ) )
			update_term_meta( $term_id, 'client_phone', $_POST['client_phone'] );
			
		if ( isset( $_POST['client_number'] ) )
			update_term_meta( $term_id, 'client_number', $_POST['client_number'] );
			
		if ( isset( $_POST['stripe_client_id'] ) )
			update_term_meta( $term_id, 'stripe_client_id', $_POST['stripe_client_id'] );
	}
	
	/**
	 * Delete Extra Fields for Client
	 *
	 * @since 1.0.0
	 * 
	 **/
	function delete_client( $term_id, $tt_id )
	{
		if ( !$term_id ) return;
		delete_term_meta( $term_id, 'client_email', $_POST['client_email'] );
		delete_term_meta( $term_id, 'client_password', $_POST['client_password'] );
		delete_term_meta( $term_id, 'client_business', $_POST['client_business'] );
		delete_term_meta( $term_id, 'client_address', $_POST['client_address'] );
		delete_term_meta( $term_id, 'client_phone', $_POST['client_phone'] );
		delete_term_meta( $term_id, 'client_number', $_POST['client_number'] );
		delete_term_meta( $term_id, 'stripe_client_id', $_POST['stripe_client_id'] );
	}
	
	/**
	 * Client Columns Setup
	 *
	 * @since 1.0.0
	 * 
	 **/
	function client_columns_setup($columns)
	{
	
		$columns = array(
			"cb" 				=> "<input type=\"checkbox\" />",
			"name" 				=> "Name",
			"client_business" 	=> "Business",
			"client_email" 	=> "Email Address",
			"posts" 			=> "Invoices"
		);
		return $columns;
	}

	function client_columns_data( $row_content, $column_name, $term_id ) 
	{
		if ( "client_business" == $column_name ) return get_term_meta( $term_id, 'client_business', true );
		elseif ( "client_email" == $column_name ) return get_term_meta( $term_id, 'client_email', true );
		
	}

	function taxonomy_metadata_wpdbfix() 
	{
		global $wpdb;
		$wpdb->taxonomymeta = "{$wpdb->prefix}taxonomymeta";
	}
	
}

/**
 * Client Table Functions
 *
 * @since 1.0.0
 * 
 **/ 
function add_term_meta( $term_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'taxonomy', $term_id, $meta_key, $meta_value, $unique );
}
function delete_term_meta( $term_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'taxonomy', $term_id, $meta_key, $meta_value );
}
function get_term_meta( $term_id, $key, $single = false ) {
	return get_metadata( 'taxonomy', $term_id, $key, $single );
}
function update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'taxonomy', $term_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Client Name
 *
 * @since 1.0.0
 * 
 **/
function wp_invoice_get_invoice_client_name( $post_id = null )
{
	if ( is_null( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}
	
	$terms = get_the_terms( $post_id , 'client' );
	if ( $terms )
	{	
		$terms = array_values( $terms );
		return $terms[0]->name;
	}
}

function wp_invoice_client()
{
	echo wp_invoice_get_invoice_client_name();
}

/**
 * Client Description
 *
 * @since 1.0.0
 * 
 **/
function wp_invoice_get_invoice_client_description( $post_id = null )
{
	if ( is_null( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}
	
	$terms = get_the_terms( $post_id , 'client' );
	if ( $terms )
	{	
		$terms = array_values( $terms );
		return $terms[0]->description;
		
	}
}

function wp_invoice_client_description()
{
	echo nl2br( wp_invoice_get_invoice_client_description() );
}

/**
 * Client Email
 *
 * @since 1.0.0
 * 
 **/
function wp_invoice_get_invoice_client_email( $post_id = null )
{
	if ( is_null( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}
	
	$terms = get_the_terms( $post_id , 'client' );
	if ( $terms )
	{	
		$terms = array_values( $terms );
		return get_term_meta( $terms[0]->term_id, 'client_email', true );
	}
}

function wp_invoice_client_email()
{
	echo wp_invoice_get_invoice_client_email();
}

/**
 * Client Business
 *
 * @since 1.0.0
 * 
 **/
function wp_invoice_get_invoice_client_business( $post_id = null )
{
	if ( is_null( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}
	
	$terms = get_the_terms( $post_id , 'client' );
	if ( $terms )
	{	
		$terms = array_values( $terms );
		return get_term_meta( $terms[0]->term_id, 'client_business', true );
	}
}

function wp_invoice_client_business()
{
	echo wp_invoice_get_invoice_client_business();
}

/**
 * Client Business Address
 *
 * @since 1.0.0
 * 
 **/
function wp_invoice_get_invoice_client_business_address( $post_id = null )
{
	if ( is_null( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}
	
	$terms = get_the_terms( $post_id , 'client' );
	if ( $terms )
	{	
		$terms = array_values( $terms );
		return get_term_meta( $terms[0]->term_id, 'client_address', true );
	}
}

function wp_invoice_client_business_address()
{
	echo nl2br( wp_invoice_get_invoice_client_business_address() );
}

/**
 * Client Phone Number
 *
 * @since 1.0.0
 * 
 **/
function wp_invoice_get_invoice_client_phone( $post_id = null )
{
	if ( is_null( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}
	
	$terms = get_the_terms( $post_id , 'client' );
	if ( $terms )
	{	
		$terms = array_values( $terms );	
		return get_term_meta( $terms[0]->term_id, 'client_phone', true );
	}
}

function wp_invoice_client_phone()
{
	echo wp_invoice_get_invoice_client_phone();
}

/**
 * Client VAT Number
 *
 * @since 1.0.0
 * 
 **/
function wp_invoice_get_invoice_client_number( $post_id = null )
{
	if ( is_null( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}
	
	$terms = get_the_terms( $post_id , 'client' );
	if ( $terms )
	{	
		$terms = array_values( $terms );
		return get_term_meta( $terms[0]->term_id, 'client_number', true );
	}
}

function wp_invoice_client_number()
{
	echo wp_invoice_get_invoice_client_number();
}


/**
 * Client Edit Link
 *
 * @since 1.0.0
 * 
 **/
function wp_invoice_get_invoice_client_edit_link( $post_id = null )
{
	if ( is_null( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}
	
	$terms = get_the_terms( $post_id , 'client' );
	if ( $terms )
	{	
		$terms = array_values( $terms );
		return admin_url( 'edit-tags.php?action=edit&taxonomy=client&post_type=invoice&tag_ID=' . $terms[0]->term_id );
	}
}

function wp_invoice_client_edit_link()
{
	echo wp_invoice_get_invoice_client_edit_link();
}

function wp_invoice_get_invoice_client_edit( $post_id = NULL )
{
	if ( $post_id == NULL) {
		global $post;
		$post_id = $post->ID;
	}
	$terms = get_the_terms( $post_id , 'client' );
	if ( $terms )
	{	
		$terms = array_values( $terms );
		return '<a title="Edit Client" href="' . admin_url( 'edit-tags.php?action=edit&taxonomy=client&post_type=invoice&tag_ID=' . $terms[0]->term_id ) . '">' . $terms[0]->name . '</a>';
	}
}

/**
 * Client Password
 *
 * @since 1.0.0
 * 
 **/
function wp_invoice_get_invoice_client_password( $post_id = null )
{
	if ( is_null( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}
	
	$terms = get_the_terms( $post_id , 'client' );
	if ( $terms )
	{	
		$terms = array_values( $terms );
		return get_term_meta( $terms[0]->term_id, 'client_password', true );
	}
	else
	{
		return false;	
	}
}

/**
 * Stripe Client ID
 *
 * @since 2.4.0
 * 
 **/
function wp_invoice_get_invoice_stripe_client_id( $post_id = null )
{
	if ( is_null( $post_id ) ) {
		global $post;
		$post_id = $post->ID;
	}
	
	$terms = get_the_terms( $post_id , 'client' );
	if ( $terms )
	{	
		$terms = array_values( $terms );
		return get_term_meta( $terms[0]->term_id, 'stripe_client_id', true );
	}
}

function wp_invoice_stripe_client_id()
{
	echo wp_invoice_get_invoice_stripe_client_id();
}

