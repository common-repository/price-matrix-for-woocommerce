jQuery( function( $ ) {
	var pricematrix_load = {
		/**
		 * Init jQuery.BlockUI
		 */
		block: function($el) {
			$el.block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
		},

		/**
		 * Remove jQuery.BlockUI
		 */
		unblock: function($el) {
			$el.unblock();
		}
	}
	
	var pricematrix_frontend = {

		/**
		 * Initialize variations actions
		 */
		init: function() {
        	$(document).on('click', '.attribute-price', this.selected_price);
			
			if( $('.pm-disable-qty').length ) {
				$('.pm-disable-qty input').each(function( index ) {
					$(this).attr( 'min', $(this).attr('max') );
					$(this).prop('readonly', true);
				});
			}
			
			if( $('body').hasClass('single-product') ) {
				var disable_qty = $('.pm-table-variations').attr('data-disable-qty');
				
				if (typeof disable_qty !== typeof undefined && disable_qty !== false) {
					$('.input-text.qty').hide();
				}
			}
			
			if( wppm.show_tooltips ) {
				Tippy('.pm-tippy1', {
					animation: 'scale',
					duration: 200,
					arrow: true,
					position: 'bottom'
				});
			}
     
		},
		
		selected_price: function() {
			var params = '',
				attribute = $(this).data('attributes'),
				$table = $(this).closest('.pm-table-variations'),
				$tr = $( "table.variations > tbody > tr > td.value select" ),
				$tr_count = $tr.length - 1;

			$('.reset_variations').trigger('click');
			$.each(attribute, function(key, value) {
				if( $('select[name="' + key + '"]').length ) {
					$('select[name="' + key + '"]').val(value);
				}
			});

			$tr.each(function( index ) {
				if( index == $tr_count) {
					$(this).addClass('pm-last-select');
				}
			});

			if( wppm.attribute_qty ) {
				var attribute_qty = 'attribute_' + wppm.attribute_qty;
				$('[name="quantity"]').val(attribute[attribute_qty]);
				$('[name="quantity"]').prop('readonly', true);
				$('[name="quantity"]').css( "cursor", "not-allowed" );
			}

			if( wppm.add_to_cart ) {
				pricematrix_load.block( $('form.variations_form') );
 				var formData = new FormData();
				formData.append( 'product_id', $table.attr('data-product_id') );
				formData.append( 'attribute', JSON.stringify(attribute) );
				$.ajax( {
					type: 'POST',
					url: wppm.apiSettings + '/add_to_cart',
					data: formData,
					dataType: 'json',
					processData: false,
					contentType: false
				}).done( function( data, status, xhr ) {
					if( data.status == 'complete') {
						if( data.url != undefined ) {
							window.location.href = data.url;
						}else {
							var this_page = window.location.toString();
							window.location.href = this_page;
						}
					}

					pricematrix_load.unblock( $('form.variations_form') );
				});
			}
			
			$('.pm-last-select').trigger('change');
/* 			var $heightPrice = $('.single_variation').outerHeight();
			console.log($heightPrice);
			 */
			return false;
		},
	}
	
	pricematrix_frontend.init();
});