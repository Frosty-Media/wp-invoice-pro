<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title><?php bloginfo('name' ); ?> | <?php the_title(); ?></title>
</head>
<body>
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
	
	<?php if ( wp_invoice_get_option( 'email_content' ) ) :
		echo wpautop( wp_invoice_get_option( 'email_content' ) ); 
	else: ?>
		<p><?php _e( 'To view your', 'wp-invoice-pro' ); ?> <?php wp_invoice_type(); ?><?php _e( ', or to print a copy for your records, click the link below:', 'wp-invoice-pro' ); ?></p>	
	<?php endif; ?>
	
	<p><a href="<?php the_permalink(); ?>"><?php the_permalink(); ?></a>
    <?php
    	$wp_invoice = WP_INVOICE_PRO();
		$password = $wp_invoice->get_invoice_password( array( 'id' => get_the_ID() ) );
		if ( !is_null( $password ) ) {
			printf( __( '<br>The invoice password is: %s', 'wp-invoice-pro' ), $password );
		}
	?></p>
	
	<?php 
	$terms	= get_the_terms( get_the_ID(), 'client' );
	$id		= false;
	$key	= '';
	if ( $terms )
	{	
		$terms	= array_values( $terms );
		$id		= $terms[0]->term_id;
	}
	
	if ( $id ) :
		$key = wp_invoice_get_key( $id ); ?>	
	<p>
	<strong><?php _e( 'For your records&hellip;', 'wp-invoice-pro' ); ?></strong><br>
	<?php _e( 'Your client ID is:', 'wp-invoice-pro' ); ?> <?php echo $id; ?><br>
	<?php _e( 'Your client key is:', 'wp-invoice-pro' ); ?> <?php echo $key; ?><br>
    <a href="<?php echo get_post_type_archive_link( 'invoice' ); ?>"><?php echo get_post_type_archive_link( 'invoice' ); ?></a>
    </p>
	
	<?php endif; ?>
	
	<p><?php _e( 'Thank you,', 'wp-invoice-pro' ); ?><br />
	<?php if ( wp_invoice_get_option( 'company' ) ) echo wp_invoice_get_option( 'company' ); ?>
	</p>

<?php endwhile; endif; ?>
</body>
</html>