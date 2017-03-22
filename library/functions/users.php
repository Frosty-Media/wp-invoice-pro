<?php
/**
 * Admin user functions
 *
 * Functions used for modifying the users panel in admin.
 *
 * @author 		Austin Passy
 */

/**
 * Define columns to show on the users page.
 *
 * @access public
 * @param array $columns Columns on the manage users page
 * @return array The modified columns
 */
function wp_invoice_user_columns( $columns ) {
	if ( ! current_user_can( 'create_users' ) )
		return $columns;

	$columns['wp_invoice_client_company'] = __('Company', 'wp-invoice' );
	$columns['wp_invoice_client_address'] = __('Address', 'wp-invoice' );
	return $columns;
}

add_filter( 'manage_users_columns', 'wp_invoice_user_columns', 10, 1 );


/**
 * Define values for custom columns.
 *
 * @access public
 * @param mixed $value The value of the column being displayed
 * @param mixed $column_name The name of the column being displayed
 * @param mixed $user_id The ID of the user being displayed
 * @return string Value for the column
 */
function wp_invoice_user_column_values( $value, $column_name, $user_id ) {
	switch ( $column_name ) :
		case "wp_invoice_client_company" :
			$company = get_user_meta( $user_id, 'client_company', true );

			if (!$company) $value = __('N/A', 'wp-invoice' ); else $value = $company;

			$value = wpautop($value);
		break;
		case "wp_invoice_client_address" :
			$address = array(
				'address_1'		=> get_user_meta( $user_id, 'client_address_1', true ),
				'address_2'		=> get_user_meta( $user_id, 'client_address_2', true ),
				'city'			=> get_user_meta( $user_id, 'client_city', true ),
				'state'			=> get_user_meta( $user_id, 'client_state', true ),
				'postcode'		=> get_user_meta( $user_id, 'client_postcode', true ),
				'country'		=> get_user_meta( $user_id, 'client_country', true )
			);
			
			$address['address_1'] = empty( $address['address_2'] ) ? $address['address_1'] : $address['address_1'] . " ";
			$address['city'] = empty( $address['city'] ) ? $address['city'] : $address['city'] . ", ";

			$formatted_address = '' .
				$address['address_1'] . $address['address_2'] . "\n" .
				$address['city'] . $address['state'] . " " .	$address['postcode'] . "\n" .
				$address['country'];

			if (!$formatted_address) $value = __('N/A', 'wp-invoice' ); else $value = $formatted_address;

			$value = wpautop($value);
		break;
	endswitch;
	return $value;
}

add_action( 'manage_users_custom_column', 'wp_invoice_user_column_values', 10, 3 );


/**
 * Get Address Fields for the edit user pages.
 *
 * @access public
 * @return array Fields to display which are filtered through wp_invoice_customer_meta_fields before being returned
 */
function wp_invoice_get_customer_meta_fields() {
	$show_fields = apply_filters('wp_invoice_customer_meta_fields', array(
		'client' => array(
			'title' => __('Client Details', 'wp-invoice' ),
			'fields' => array(
				'client_company' => array(
						'label' => __( 'Company', 'wp-invoice' ),
						'description' => ''
					),
				'client_address_1' => array(
						'label' => __( 'Address 1', 'wp-invoice' ),
						'description' => ''
					),
				'client_address_2' => array(
						'label' => __( 'Address 2', 'wp-invoice' ),
						'description' => ''
					),
				'client_city' => array(
						'label' => __( 'City', 'wp-invoice' ),
						'description' => ''
					),
				'client_postcode' => array(
						'label' => __( 'Postcode', 'wp-invoice' ),
						'description' => ''
					),
				'client_state' => array(
						'label' => __( 'State/County', 'wp-invoice' ),
						'description' => __('Country or state code', 'wp-invoice' ),
					),
				'client_country' => array(
						'label' => __( 'Country', 'wp-invoice' ),
						'description' => __('2 letter Country code', 'wp-invoice' ),
					),
				'client_phone' => array(
						'label' => __( 'Telephone', 'wp-invoice' ),
						'description' => ''
					)
			)
		),
	));
	return $show_fields;
}


/**
 * Show Address Fields on edit user pages.
 *
 * @access public
 * @param mixed $user User (object) being displayed
 * @return void
 */
function wp_invoice_customer_meta_fields( $user ) {
	if ( ! current_user_can( 'create_users' ) )
		return;

	$show_fields = wp_invoice_get_customer_meta_fields();

	foreach( $show_fields as $fieldset ) :
		?>
		<h3><?php echo $fieldset['title']; ?></h3>
		<table class="form-table">
			<?php
			foreach( $fieldset['fields'] as $key => $field ) :
				?>
				<tr>
					<th><label for="<?php echo $key; ?>"><?php echo $field['label']; ?></label></th>
					<td>
						<input type="text" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo esc_attr( get_user_meta( $user->ID, $key, true ) ); ?>" class="regular-text" /><br/>
						<span class="description"><?php echo $field['description']; ?></span>
					</td>
				</tr>
				<?php
			endforeach;
			?>
		</table>
		<?php
	endforeach;
}

add_action( 'show_user_profile', 'wp_invoice_customer_meta_fields' );
add_action( 'edit_user_profile', 'wp_invoice_customer_meta_fields' );


/**
 * Save Address Fields on edit user pages
 *
 * @access public
 * @param mixed $user_id User ID of the user being saved
 * @return void
 */
function wp_invoice_save_customer_meta_fields( $user_id ) {
	if ( ! current_user_can( 'create_users' ) )
		return $columns;

 	$save_fields = wp_invoice_get_customer_meta_fields();

 	foreach( $save_fields as $fieldset )
 		foreach( $fieldset['fields'] as $key => $field )
 			if ( isset( $_POST[ $key ] ) )
 				update_user_meta( $user_id, $key, trim( esc_attr( $_POST[ $key ] ) ) );
}

add_action( 'personal_options_update', 'wp_invoice_save_customer_meta_fields' );
add_action( 'edit_user_profile_update', 'wp_invoice_save_customer_meta_fields' );