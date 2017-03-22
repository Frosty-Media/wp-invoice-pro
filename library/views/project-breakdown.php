<?php $settings = get_option( 'wp_invoice_settings' ); ?>

<div class="project-breakdown">
 
	<p><a href="#" class="dodelete-detail button"><?php _e( 'Remove all', 'wp-invoice' ); ?></a> <a href="#" class="docopy-detail button"><?php _e( 'Add Field', 'wp-invoice' ); ?></a></p>
 
    <noscript><?php _e( 'Enable javascript to see the subtotal update live.', 'wp-invoice' ); ?></noscript>
    
    <div class="wrapper header">        	
        <p class="title"><?php _e( 'Title', 'wp-invoice' ); ?></p>
            
        <p class="desc"><?php _e( 'Description', 'wp-invoice' ); ?></p>
            
        <p class="type"><?php _e( 'Type', 'wp-invoice' ); ?></p>
            
        <p class="rate"><?php _e( 'Rate', 'wp-invoice' ); ?></p>
            
        <p class="time"><?php _e( 'Time', 'wp-invoice' ); ?></p>
            
        <p class="time"><?php _e( 'Subtotal', 'wp-invoice' ); ?></p>
	</div>
		
	<?php while( $mb->have_fields_and_multi('detail') ) : ?>
    <?php $mb->the_group_open(); ?>
    
        <div class="wrapper body sortable">
        
        <p class="title">
        <?php $mb->the_field('title'); ?>
        <label><?php _e( 'Title:', 'wp-invoice' ); ?></label>
        <input type="text" class="large-text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" />
        </p>
        
        <p class="desc">
        <?php $mb->the_field('description'); ?>
        <label><?php _e( 'Description:', 'wp-invoice' ); ?></label>
        <input type="text" class="large-text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" />
        </p>
        
        <p class="type">
        <?php $mb->the_field('type'); ?>
        <select name="<?php $mb->the_name(); ?>">
            <option value=""><?php _e( 'Select', 'wp-invoice' ); ?></option>
            <option value="timed" <?php selected( $mb->get_the_value(), 'timed' ); ?>><?php _e( 'Timed', 'wp-invoice' ); ?></option>
            <option value="fixed" <?php selected( $mb->get_the_value(), 'fixed' ); ?>><?php _e( 'Fixed', 'wp-invoice' ); ?></option>
        </select>
        </p>
        
        <p class="rate">
        <?php $mb->the_field('rate'); ?>
        <label><?php _e( 'Rate:', 'wp-invoice' ); ?></label>
        <input type="text" class="large-text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" />
        </p>
        
        <p class="time">
        <?php $mb->the_field('time'); ?>
        <label><?php _e( 'Time:', 'wp-invoice' ); ?></label>
        <input type="text" class="large-text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" />
        </p>
        
        <p class="subtotal">
        <?php $mb->the_field('subtotal'); ?>
        <label><?php _e( 'Subtotal:', 'wp-invoice' ); ?></label>
        <input type="text" class="large-text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" readonly="readonly" />
        </p>
        
        <p class="delete">
        <a class="dodelete button" href="#" title="<?php _e( 'Remove this Field', 'wp-invoice' ); ?>">&times;</a>
        </p>
        
        <p class="move">
        <span>&nbsp;</span>
        <span>&nbsp;</span>
        <span>&nbsp;</span>
        </p>
        
        </div>
        
    <?php $mb->the_group_close(); ?>
    <?php endwhile; ?>
    
    <div class="wrapper footer">
    
    	<p>
        <a href="#" class="docopy-detail button"><?php _e( 'Add Field', 'wp-invoice' ); ?></a>
        </p>
        
        <p class="invoice-total">
        <?php $mb->the_field('total'); ?>
        <strong><?php _e( 'Total:', 'wp-invoice' ); ?></strong>
        <input type="text" class="small-text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" readonly="readonly" />
        <span><?php $mb->the_value(); ?></span>
        </p>
        
        <p class="invoice-tax">
        <?php $mb->the_field('tax'); ?>
        <strong><?php _e( 'Tax:', 'wp-invoice' ); ?></strong>
        <input type="text" class="small-text" name="<?php $mb->the_name(); ?>" value="<?php echo ( $mb->get_the_value() != '' ) ? $settings['tax'] : $mb->get_the_value(); ?>" readonly="readonly" />
        <span><?php $mb->the_value(); ?></span>
        </p>
        
        <p class="invoice-subtotal">
        <?php $mb->the_field('subtotal'); ?>
        <strong><?php _e( 'Subtotal:', 'wp-invoice' ); ?></strong>
        <input type="text" class="small-text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" readonly="readonly" />
        <span><?php $mb->the_value(); ?></span>
        </p>
        
    </div>

</div>