<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]> <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

<title><?php wp_title( '|', true, 'right' ); ?></title>

<link rel="profile" href="http://gmpg.org/xfn/11" />

<?php wp_head(); ?>

</head>
<body <?php body_class(); ?>>

	<header id="head" role="banner">
		<div id="site-title">
			<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
				<?php if ( wp_invoice_get_option('logo' ) ) { ?>
					<h1 id="logo" class="logo-img">
		        	<img src="<?php echo wp_invoice_get_option('logo' ); ?>" alt="<?php bloginfo( 'name' ); ?>" />
		        	</h1>
		        <?php } else { ?>
		    		<h1 id="logo"><?php bloginfo( 'name' ); ?></h1>
		    		<?php if ( get_bloginfo('description' ) ) : ?><p id="tagline"><?php bloginfo( 'description' ); ?></p><?php endif; ?>
		        <?php } ?>
	        </a>
		</div>