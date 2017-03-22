/*!
 * hoverIntent r7 // 2013.03.11 // jQuery 1.9.1+
 * http://cherne.net/brian/resources/jquery.hoverIntent.html
 *
 * You may use hoverIntent under the terms of the MIT license.
 * Copyright 2007, 2013 Brian Cherne
 */
(function(e){e.fn.hoverIntent=function(t,n,r){var i={interval:100,sensitivity:7,timeout:0};if ( typeof t==="object"){i=e.extend(i,t)}else if ( e.isFunction(n) ){i=e.extend(i,{over:t,out:n,selector:r})}else{i=e.extend(i,{over:t,out:t,selector:n})}var s,o,u,a;var f=function(e){s=e.pageX;o=e.pageY};var l=function(t,n){n.hoverIntent_t=clearTimeout(n.hoverIntent_t);if ( Math.abs(u-s)+Math.abs(a-o)<i.sensitivity){e(n).off("mousemove.hoverIntent",f);n.hoverIntent_s=1;return i.over.apply(n,[t])}else{u=s;a=o;n.hoverIntent_t=setTimeout(function(){l(t,n)},i.interval)}};var c=function(e,t){t.hoverIntent_t=clearTimeout(t.hoverIntent_t);t.hoverIntent_s=0;return i.out.apply(t,[e])};var h=function(t){var n=jQuery.extend({},t);var r=this;if ( r.hoverIntent_t){r.hoverIntent_t=clearTimeout(r.hoverIntent_t)}if ( t.type=="mouseenter"){u=n.pageX;a=n.pageY;e(r).on("mousemove.hoverIntent",f);if ( r.hoverIntent_s!=1){r.hoverIntent_t=setTimeout(function(){l(n,r)},i.interval)}}else{e(r).off("mousemove.hoverIntent",f);if ( r.hoverIntent_s==1){r.hoverIntent_t=setTimeout(function(){c(n,r)},i.timeout)}}};return this.on({"mouseenter.hoverIntent":h,"mouseleave.hoverIntent":h},i.selector)}})(jQuery)

jQuery(document).ready(function($) {
    
	// Submenus
	var config = {    
		 over: function(){ $('ul', this).fadeIn(200); },  
		 timeout: 300,
		 out: function(){ $('ul', this).fadeOut(300); }  
	};
	$('#head ul > li' ).not("#head ul li li").hoverIntent(config);
	
	// Removals
	var styles = $('link[id^="' + wp_invoice.template + '"]');
	if ( styles.length > 0 ) {
		if ( $.isArray( styles ) ) {
			$.each( styles, function() {
				$(this).remove();
			});
		} else {
			styles.remove();
		}
	}
	
	// AJAX comment_form()
	var wpi_comment_form = {
		lock: false,
		load: function( page, newUrl ) {
			if ( wpi_comment_form.lock )
				return false; // Prevent double-fires
			wpi_comment_form.lock = true;
			$.ajax({
				url: wp_invoice.ajaxurl,
				data: {
					action	: 'wp-invoice-comments',
					post_id	: wp_invoice.post_id,
					nonce	: wp_invoice.nonce,
				},
				complete: function() {
					wpi_comment_form.lock = false; // Regardess of success, release the lock
				},
				success: function( data ) {
					$('a.btn.comment').hide();
					$('div.reply').show();
					$('#comment-form-wrapper').html(data).slideDown('slow');
				}
			});
		}
	};
	
	$('a.btn.comment').on('click', function(e) {
		e.preventDefault();
		wpi_comment_form.load(wp_invoice.cpage);
	});
	
});