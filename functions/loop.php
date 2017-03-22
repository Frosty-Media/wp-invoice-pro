<?php

/**
 * Heler loop function for /template/index.php
 *
 * @since	2.0.0
 */
function wp_invoice_table_list( $the_query = null, $client_loop = false, $taxonomy = 'client' ) {
	if ( is_null( $the_query ) ) return;

	if ( $client_loop ) {
		$tax_terms = get_terms( $taxonomy );
		if ( $tax_terms ) {
			foreach ( $tax_terms as $tax_term ) :
				$args = array(
					'post_type'			=> 'invoice',
					$taxonomy				=> $tax_term->slug,
					'post_status'			=> 'publish',
					'posts_per_page'		=> -1,
					'ignore_sticky_posts'	=> 1
				);
		
				$my_query = null;
				$my_query = new WP_Query( $args );
				if ( $my_query->have_posts() ) {
					wp_invoice_table_open_loop();
					while ( $my_query->have_posts() ) : $my_query->the_post();
						wp_invoice_table_loop_content();
					endwhile;
					wp_invoice_table_close_loop();
				}
				wp_reset_query();
			endforeach;
		}
	} else {
		wp_invoice_table_open_loop();
	
		while ( $the_query->have_posts() ) : $the_query->the_post();
			wp_invoice_table_loop_content();
		endwhile;
		
		wp_invoice_table_close_loop();
	}
}

function wp_invoice_table_open_loop() { ?>
    <table class="invoice padded dashboard">
    <thead>
        <tr>
            <th class="id"><?php _e( 'ID #', 'wp-invoice-pro' ); ?></th>
            <th class="item"><?php _e( 'Title', 'wp-invoice-pro' ); ?></th>
            <th class="date"><?php _e( 'Date', 'wp-invoice-pro' ); ?></th>
            <th class="total"><?php _e( 'Total', 'wp-invoice-pro' ); ?></th>
            <th class="status"><?php _e( 'Status', 'wp-invoice-pro' ); ?></th>
        </tr>
    </thead>
    <tbody><?php
}

function wp_invoice_table_close_loop() { ?>
    </tbody>
    </table><?php
}

function wp_invoice_table_loop_content() {
//	WP_INVOICE_PRO()->print_r( WP_INVOICE_PRO()->dir );
	$payment_status	= wp_invoice_get_invoice_status();
	$invoice_type 	= wp_invoice_get_invoice_type();
	
	if ( $payment_status == 'Paid' ) {
		$status = __( 'Paid', 'wp-invoice-pro' );
		$status_class = 'paid';
	} else if ( $payment_status != 'Paid' && $invoice_type != 'Quote' ) {
		$status = __( 'Pending', 'wp-invoice-pro' );
		$status_class = 'pending';
	} else if ( $invoice_type == 'Quote' && wp_invoice_get_quote_approved() != __( 'Not yet', 'wp-invoice-pro' ) ){
		$status = __( 'Approved', 'wp-invoice-pro' );
		$status_class = 'approved';
	} else {
		$status = __( 'Pending', 'wp-invoice-pro' );
		$status_class = 'pending';
	}
	?>
	<tr>
		<td class="id"><a href="<?php the_permalink(); ?>"><?php wp_invoice_number(); ?></a></td>
		<td class="item"><a href="<?php the_permalink(); ?>"><?php the_title(); ?> <?php if ( $invoice_type == 'Quote' ) echo '<em>' . __( '[Quote]', 'wp-invoice-pro' ) . '</em>'; ?></a></td>
		<td><?php echo get_the_date(); ?></td>
		<td><?php the_invoice_total(); ?></td>
		<td class="status <?php echo $status_class; ?>"><a href="<?php the_permalink(); ?>"><?php echo $status; ?></a></td>
	</tr><?php
}