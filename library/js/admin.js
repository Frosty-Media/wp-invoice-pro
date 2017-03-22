jQuery(document).ready(function($) {
	
	/**
	 * post.php page
	 *
	 **/
	if ( $('.project-email').length > 0 ) {
		
		/**
		 ****** Generate the permalink URI ******
		 ****************************************/
		function rand(l,u) {
			 return Math.floor((Math.random() * (u-l+1))+l);
		}
		
		function generate_permalink() {
			var length = 30;
			var characters = '0123456789abcdefghijklmnopqrstuvwxyz';
			var string = "";    
			for ( var p = 0; p < length; p++ ) {
				string += characters[rand(0, ( characters.length - 1 ) )];
			}			
			return string;
		}
		
		var permalink = $('input[name="_wp_invoice_project_email[permalink]"]').val();
		if ( permalink == 'encoded' ) {
			if ( $('#slugdiv input#post_name').val().length < 30 ) {
				$('#slugdiv input#post_name').val(generate_permalink());
			}
		} else {
			$('#slugdiv input#post_name').val('');	
		}
		
		var error = $('.project-email .wrapper .haserror');
		if ( $(error).length > 0 ) {
			setInterval(function() {
				$(error).addClass('error');
			}, 1000);
		}
		
		var client = $('input[name="_wp_invoice_project_email[client]"]');
		//console.log( client );
		if ( $(client).val() == true ) {
			$('.send-email a').removeClass('disabled');
		} else {
			$('.send-email a').prop('href','');
		}

		
	}
	
	if ( $('.project-details').length > 0 ) {		
		
		/**
		 *********** Invoice Detail *************
		 ****************************************/
		$.fn.detail_toggle = function() {
			$(this).find('.front').toggle();
			$(this).find('.back').toggle();	
			$(this).find('span.description a[class^="wp-invoice"]').toggle();	
		}	
		$.fn.detail_get_input = function() {
			var $this = $(this);
			var result = '';
			if ( $this.find('input').size() > 0 ) {
				result = $this.find('input').val();
			}
			else if ( $this.find('select').size() > 0 ) {
				result = $this.find('select').val();
			}
			return  result;
		}		
		$.fn.detail_set_input = function(origInputValue) {
			var $this = $(this);
			if ( $this.find('input').size() > 0 ) {
				$this.find('input').prop('value', origInputValue);
			}
			else if ( $this.find('select').size() > 0 ) {
				$this.find('select option').each(function() {
					if ( $(this).val() == origInputValue ) {
						$(this).prop('selected','selected');
					}
				});
			}
		}
		
		$('.project-details .form-table td').each(function(e) {
			var $this = $(this);
			var origInputValue;
			var inputType = 'input'; if ( $this.find('select').size > 0 ) { inputType = 'option'; }
			
			var input = $this.find('input');
			var select = $this.find('select');
			
			input.wrap('<div class="back" style="display:none" />').parent().parent().prepend('<div class="front" style="display:inline"><span>'+input.val()+'</span></div>');
			select.wrap('<div class="back" style="display:none" />').parent().parent().prepend('<div class="front" style="display:inline"><span>'+select.val()+'</span></div>');
			
			$this.find('a.wp-invoice-edit').on('click',function(e) {
				$this.detail_toggle();
				e.preventDefault();
			});	
			
			$this.find('a.wp-invoice-ok').on('click',function(e) {
				var newInputValue = $this.detail_get_input();
				$this.find('.front span').html(newInputValue);	
				$this.detail_toggle();
				update_subtotal_numbers();
				e.preventDefault();
			});
		});
		
	}
	
	if ( $('.project-breakdown').length > 0 ) {
		
		/**
		 *********** Update Subtotal ************
		 ****************************************/
		function update_subtotal(detail) {
			var rate = detail.find('.rate input').val(); rate = parseFloat(rate);
			var duration = detail.find('.time input').val(); duration = parseFloat(duration);
			
			var subtotal = 0.00;
			if ( detail.find('select :selected').val() == 'fixed') {
				subtotal = rate;
				detail.find('.time input').val('N/A');
			} else {
				subtotal = rate * duration; subtotal = subtotal.toFixed(2);
			}
	
			detail.find('.subtotal input').not('.wpa_group.tocopy .subtotal input').val(subtotal);
			
			update_subtotal_numbers();
		}
		
		function update_subtotal_numbers() {
			$('.wrapper.footer .invoice-subtotal span').text( get_invoice_subtotal().toFixed(2) );
				$('.wrapper.footer .invoice-subtotal input').val( get_invoice_subtotal().toFixed(2) );
				
			$('.wrapper.footer .invoice-tax span').text( get_invoice_tax().toFixed(2) );
				$('.wrapper.footer .invoice-tax input').val( get_invoice_tax().toFixed(2) );
				
			$('.wrapper.footer .invoice-total span').text( get_invoice_total().toFixed(2) );
				$('.wrapper.footer .invoice-total input').val( get_invoice_total().toFixed(2) );
		}
		
		function get_invoice_subtotal() {
			var temp_total = 0;
			//Be sure not to get the subtotal from the .tocopy box as if will cause an empty array()
			$('.subtotal input').not('.wpa_group.tocopy .subtotal input').each(function() {
				temp_total += parseFloat( $(this).val() );
			});	
			return temp_total;
		}
		
		function get_invoice_tax() {
			var tax = $('input[name="_wp_invoice_project_details[invoice_tax]"]').val(); tax = parseFloat(tax);
			var temp_total = parseFloat(tax * get_invoice_subtotal());
			return temp_total;
		}
		
		function get_invoice_total() {
			var temp_total = parseFloat(get_invoice_subtotal() + get_invoice_tax());
			return temp_total;
		}
		
		/**
		 *********** Initiate Subtotal **********
		 ****************************************/
		function init_subtotal_update() {
			$('.wrapper.body').each(function() {
				var detail = $(this);
				
				update_subtotal(detail);
				
				$(this).find('.rate input').on('change',function() { 
					update_subtotal(detail);
				}).bind("change keyup", function() {
					update_subtotal(detail);
				});
				
				$(this).find('.time input').on('change',function() {
					update_subtotal(detail);
				}).bind("change keyup", function() {
					update_subtotal(detail);
				});
				
				$(this).find('.type select').on('change',function() {
					update_subtotal(detail);
				});
				
			});
		}
		init_subtotal_update();		
		
		/**
		 *********** Bind wpalchemy *************
		 ****************************************/
		function breakdown_sortable() {
			setTimeout(function() {
				$('#wpa_loop-detail.wpa_loop.wpa_loop-detail').not('.tocopy').sortable({
					axis	: 'y',
					delay	: 150,
					handle	: '.move',
					items	: '> .wpa_group.wpa_group-detail',
				});
			}, 400);
		}
		breakdown_sortable();
		
		/**
		 *********** Bind wpalchemy *************
		 ****************************************/
		if ( $.wpalchemy.length > 0 ) {
			$.wpalchemy.bind('wpa_copy', function(clone) {
				init_subtotal_update();
				breakdown_sortable();
			});
			$.wpalchemy.bind('wpa_delete', function(clone) {
				init_subtotal_update();
				breakdown_sortable();
			});
		}
	
	}// end if
	
	
	if ( $('.wp-invoice_page_settings').length > 0 ) {
		
		var gateway = $('select[name="wp_invoice_settings[payment_gateway]"]');
		var account = $('input#payment_gateway_account');
		
		if ( $(gateway).val() == '-' ) $(account).parent().parent().hide();
		
		$(gateway).on('change',function() { 
			var val = $(this).val();
			//console.log( val );
			if ( val == '-' ) {
				$(account).parent().parent().hide();
			} else {
				$(account).parent().parent().show();
			}
		});		
		
	}// end if
		
});