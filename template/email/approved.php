<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title><?php bloginfo('name' ); ?> | <?php the_title(); ?></title>
</head>
<body>
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
	
	<p><?php _e( 'The following', 'wp-invoice-pro' ); ?> <?php wp_invoice_type(); ?> <?php _e( 'has been approved:', 'wp-invoice-pro' ); ?></p>
	
	<p><a href="<?php the_permalink(); ?>"><?php the_permalink(); ?></a></p>

<?php endwhile; endif; ?>
</body>
</html>