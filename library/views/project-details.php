<?php $settings = get_option( 'wp_invoice_settings' ); ?>

<?php $edit_ok_cancel = sprintf( __( '%1$sEdit%3$s%2$sOK%3$s', 'wp-invoice' ),
									  '<a href="#" class="wp-invoice-edit" onclick="return false;">',
									  '<a href="#" class="wp-invoice-ok" onclick="return false;" style="display:none">',
									  '</a>' ); ?>
<div class="project-details">

    <table class="form-table">
    <tbody>
        
        <tr><th style="width:18%">
        <?php $mb->the_field('number'); ?>
        <label><?php _e( 'Number:', 'wp-invoice' ); ?></label>
        </th>
        <td>
            <input type="text" class="medium-text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" />
            <span class="description"><?php echo $edit_ok_cancel; ?></span>
        </td>
        
        <tr><th style="width:18%">
        <?php $mb->the_field('type'); ?>
        <label><?php _e( 'Type:', 'wp-invoice' ); ?></label>
        </th>
        <td>
            <select name="<?php $mb->the_name(); ?>">
                <option value="invoice" <?php selected( $mb->get_the_value(), 'invoice' ); ?>><?php _e( 'Invoice', 'wp-invoice' ); ?></option>
                <option value="quote" <?php selected( $mb->get_the_value(), 'quote' ); ?>><?php _e( 'Quote', 'wp-invoice' ); ?></option>
            </select>
            <span class="description"><?php echo $edit_ok_cancel; ?></span>
        </td>
        
        <tr><th style="width:18%">
        <?php $mb->the_field('invoice_tax'); ?>
        <label><?php _e( 'Tax:', 'wp-invoice' ); ?></label>
        </th>
        <td>
            <input type="text" class="small-text" name="<?php $mb->the_name(); ?>" value="<?php if ( $mb->get_the_value() == '' ) echo $settings['tax']; else echo $mb->get_the_value(); ?>" />
            <span class="description"><?php echo $edit_ok_cancel; ?></span>
        </td>
        
        <tr><th style="width:18%">
        <?php $mb->the_field('sent'); ?>
        <label><?php _e( 'Sent:', 'wp-invoice' ); ?></label>
        </th>
        <td>
            <input type="text" class="medium-text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" />
            <span class="description"><?php echo $edit_ok_cancel; ?></span>
        </td>
        
        <tr><th style="width:18%">
        <?php $mb->the_field('paid'); ?>
        <label><?php _e( 'Paid:', 'wp-invoice' ); ?></label>
        </th>
        <td>
            <input type="text" class="medium-text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" />
            <span class="description"><?php echo $edit_ok_cancel; ?></span>
        </td>
        
    </tbody></table>

</div>