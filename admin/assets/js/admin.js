jQuery( function( $ ) {
	 var admin_wppm_block = {
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
		},
	}
	
	var $el = $( '#woocommerce-product-data' );
	/**
	 * Variations Price Matrix actions
	 */
	var admin_wppm = {

		/**
		 * Initialize variations actions
		 */
		init: function() {
        	this.check_enable_price_matrix();
			$(document).on('change', '#product-type', this.is_variable);
			$(document).on('click', '.button.save_attributes', this.start_loading_attribute);
			$(document).on('click', '#wppm_meta .pm-nav a', this.nav_tabs);
			$(document).on('click', '#wppm_meta .ui label', this.enable_wppm);
			
			$(document).on('click', '.pm-add-attr', this.add_row);
			$(document).on('click', '.pm-remove-row', this.remove_row);
			
			$(document).on('click', '.pm-input-price', this.input_price);
			$(document).on('submit', '#frm-wppm-popup', this.save_price);
			
			$(document).ajaxComplete(function(event, xhr, options) {
				if( options.data != undefined && typeof options.data == 'string') {
					if( options.data.search('action=woocommerce_load_variations') >= 0 ) {
						admin_wppm.load_table_attributes();
					}
				}
			});
			
			$(document).on('change', '.wppm-select', this.change_attributes);
			$(document).on('click', '.pm-order-heading', this.order_heading);
			
			this.pm_order();
			this.call_init();
			this.sortable_row();
			
		},
		
		sortable_row: function() {
			$('#wppm_wc_variations tbody').sortable({
				revert: true,
				placeholder: 'sortable-placeholder',
				items: '.wppm_wc_row',
				handle: '.wppm_wc_drag',
				cursor: 'move',
				helper: function(e, tr) {
					var $originals = tr.children();
					var $helper = tr.clone();
					$helper.children().each(function(index)
					{
						// Set helper cell sizes to match the original sizes
						$(this).width($originals.eq(index).width());
					});
					return $helper;
				},
				update: function(e, ui) {
					var attribute = [];
					$( "#wppm_wc_variations .wppm_wc_row .wppm-attributes" ).each(function( index ) {
						attribute.push( $(this).val() );
					});
					
					var direction = [];
					$( "#wppm_wc_variations .wppm_wc_row .wppm-direction" ).each(function( index ) {
						direction.push( $(this).val() );
					});		
					

					if ( typeof window.FormData !== 'function' ) {
						return;
					}
					
					var formData = new FormData();
					formData.append( 'product_id', $('#post_ID').val() );
					formData.append( 'attribute', JSON.stringify(attribute) );
					formData.append( 'direction', JSON.stringify(direction) );
					
					$.ajax( {
						type: 'POST',
						url: wppm.apiSettings + '/order_table',
						data: formData,
						dataType: 'json',
						processData: false,
						contentType: false
					}).done( function( data, status, xhr ) {

					}).fail( function( xhr, status, error ) {
						
					});
				
				}
			});
	

		},
		
		call_init: function() {
			if(jQuery().select2) {
				$('.pm-enhanced-select').select2({ dropdownAutoWidth : true });
			}
			
			if(jQuery().wpColorPicker) {
				$('.pm-color-field').wpColorPicker();
			}
			if(jQuery().tipTip) {
				$( '.tips, .help_tip, .woocommerce-help-tip' ).tipTip( {
					'attribute': 'data-tip',
					'fadeIn': 50,
					'fadeOut': 50,
					'delay': 200
				} );
			}
		},
		
		pm_order: function() {
			$('.pm-order-content').sortable({
				placeholder: 'order-sortable-placeholder',
				connectWith: '.pm-order-content',
				revert: true,
                stop: function (event, ui) {
					if ( typeof window.FormData !== 'function' ) {
						return;
					}
			
					var $li = ui.item.closest('li');
					
					var $tax = $li.attr('data-taxonomy');
                    
					var attribute = $li.find('.pm-order-col').map(function() {
						return $(this).attr('data-id');
					}).get().join(',');
					
					console.log(attribute);
					
					var formData = new FormData();
					formData.append( 'product_id', $('#post_ID').val() );
					formData.append( 'attribute', JSON.stringify(attribute) );
					formData.append( 'taxonomy', $tax );
			
					$.ajax( {
						type: 'POST',
						url: wppm.apiSettings + '/order_attributes',
						data: formData,
						dataType: 'json',
						processData: false,
						contentType: false
					}).done( function( data, status, xhr ) {
						//$('.first-loading').remove();
						
						if( data.status == 'complete') {
							//$this.closest('tr').remove();
						}
					}).fail( function( xhr, status, error ) {
						//$('.first-loading').remove();
					});
                }
			}).disableSelection();			
		},
		
		add_row: function() {
			var wrapper_attributes = $( '#variable_product_options' ).find( '.woocommerce_variations' ).data( 'attributes' );
			var $tr = $('#tpl-wppm-addrow').html();
			var $count = $('#wppm_wc_variations tbody > tr').length;
			
			if( $count >= Object.keys(wrapper_attributes).length) {
				Swal.fire(
					wppm.wpLabel.error_title,
					wppm.wpLabel.max_add_row,
					'error'
				);
			}else {
				if ( typeof window.FormData !== 'function' ) {
					return;
				}

				var formData = new FormData();
				formData.append( 'product_id', $('#post_ID').val() );
				admin_wppm_block.block( $('.pm-tab-content' ));

				$.ajax( {
					type: 'POST',
					url: wppm.apiSettings + '/add_row',
					data: formData,
					dataType: 'json',
					processData: false,
					contentType: false
				}).done( function( data, status, xhr ) {
					admin_wppm_block.unblock( $('.pm-tab-content' ));

					if( typeof data.complete != 'undefined' ) {
						$( "#wppm_wc_variations .wppm_wc_row .wppm-attributes" ).each(function( index ) {
							$('option[value="' + data.option + '"]').remove();
						});
	
						$('#wppm_wc_variations tbody tr:last-child').after(data.html);
					}else {
						Swal.fire(
							data.title,
							data.message,
							'error'
						);
					}


				}).fail( function( xhr, status, error ) {
					admin_wppm_block.unblock( $('.pm-tab-content' ));
				});
			}
		},
		
		remove_row: function() {
			if ( typeof window.FormData !== 'function' ) {
				return;
			}

			
			var $this = $(this);
			var formData = new FormData();
			var attribute = $(this).attr('data-attribute');
			var $count = $('#wppm_wc_variations tbody > tr').length;
			formData.append( 'product_id', $('#post_ID').val() );
			if( attribute != undefined ) {
				formData.append( 'attribute', attribute );
			}else {
				formData.append( 'attribute', attribute );
			}
			
			
			if($count <= 1) {
				Swal.fire(
					wppm.wpLabel.error_title,
					wppm.wpLabel.min_remove_row,
					'error'
				);
			}else {


					admin_wppm_block.block( $('.pm-tab-content' ));
					$.ajax( {
						type: 'POST',
						url: wppm.apiSettings + '/remove_attributes',
						data: formData,
						dataType: 'json',
						processData: false,
						contentType: false
					}).done( function( data, status, xhr ) {
						admin_wppm_block.unblock( $('.pm-tab-content' ));
						
						if( data.status == 'complete') {
							$this.closest('tr').remove();
						}
					}).fail( function( xhr, status, error ) {
						admin_wppm_block.unblock( $('.pm-tab-content' ));
					});

			}
		},
		
		input_price: function() {
			if ( typeof window.FormData !== 'function' ) {
				return;
			}
			
			admin_wppm_block.block( $('.pm-tab-content') );
			
			var $this = $(this);
			var formData = new FormData();
			formData.append( 'product_id', $('#post_ID').val() );
			
			$.ajax( {
				type: 'POST',
				url: wppm.apiSettings + '/input_price',
				data: formData,
				dataType: 'json',
				processData: false,
				contentType: false
			}).done( function( data, status, xhr ) {
				$('.first-loading').remove();
				admin_wppm_block.unblock( $('.pm-tab-content') );

				if( data.status == 'complete') {
					$('#wppm-popup .wppm-popup-wrapper .wppm-popup-container').html(data.html);
					
					$.magnificPopup.open({
						items: {
							src: '#wppm-popup'
						},
						type: 'inline',
						midClick: true,
						mainClass: 'mfp-fade',
						callbacks: {
							open: function() {
								$('body').addClass('mfp-wppm-popup');
								
								var $current_window = $(window).width();
								

								if( $('.has-three-attribute').length && $current_window > 520 ) {
									$width_table = $current_window / 2;
									$('#wppm-popup').css({
										"maxWidth": $width_table
									});
								}
								
								$( ".three-horizontal-attribute .attribute-first" ).each(function( index ) {
									var $w = $(this).outerWidth();
									
									$('.three-horizontal-attribute');
									
									console.log($w);
								});

							},
							
							close: function() {
								$('body').removeClass('mfp-wppm-popup');
							}
						 }
					});
				}else {
					Swal.fire(
						wppm.wpLabel.error_title,
						data.message,
						'error'
					);
				}
			}).fail( function( xhr, status, error ) {
				$('.first-loading').remove();
			});
			
			return false;
		},
		
		save_price: function() {
			if ( typeof window.FormData !== 'function' ) {
				return;
			}
			
			var $btn = $(this).find('.wppm-popup-save');
			
			if( ! $btn.hasClass('ld-ajax-loading') ) {
				
				
				var $this = $(this);
				var formData = new FormData();
				formData.append( 'action', 'pricematrix_save_price' );
				formData.append( 'product_id', $('#post_ID').val() );
				formData.append( 'data', $('#frm-wppm-popup').serialize() );
				formData.append( 'layout', $('#frm-wppm-popup').find('.table-responsive').attr('data-layout') );
				$btn.addClass('ld-ajax-loading');
				admin_wppm_block.block( $('.wppm-popup-container') );

				$.ajax( {
					type: 'POST',
					url: wppm.ajax_url,
					data: formData,
					dataType: 'json',
					processData: false,
					contentType: false
				}).done( function( data, status, xhr ) {
					$btn.removeClass('ld-ajax-loading');
					admin_wppm_block.unblock( $('.wppm-popup-container') );
					
					$('body .wrap .wp-header-end').after(data.msg);
					
					if( data.status == 'complete') {
						$.magnificPopup.close();
						Swal.fire(
							wppm.wpLabel.success_title,
							data.message,
							'success'
						);
					}
				}).fail( function( xhr, status, error ) {
					$btn.removeClass('ld-ajax-loading');
				});
			}
			
			return false;
		},
		
		is_variable: function() {
			var $val = $(this).val();

			if( $val == 'variable' ) {
				$('#is_price_matrix').closest('.ui.toggle').removeClass('disabled').addClass('active');
			}else {
				$('#is_price_matrix').closest('.ui.toggle').removeClass('active').addClass('disabled');
				$('#is_price_matrix').prop('checked', false);
			}
		},
		
		enable_wppm: function() {
			var $ui = $(this).closest('.ui');

			if( ! $ui.hasClass('disabled') ) {
				var save_attributes = $( '#variable_product_options' ).find( '.woocommerce_variations' ).data( 'attributes' );
				var select_attributes = 0;
				
				$('#product_attributes .woocommerce_attribute').each(function( index ) {
					var checked = $(this).find('.enable_variation .checkbox:checked').length;
					if( $(this).find('select.multiselect').length > 0 ) {
						var val_select = $(this).find('select.multiselect').val();
					}else {
						var val_select = $(this).find('textarea').val();
						var val_select = val_select.split("|");
						val_select = val_select.map(function(a){return a.trim()});
					}
					
					
					if( val_select && checked > 0 ) {
						select_attributes += 1;
					}
				});

				if(save_attributes == undefined || select_attributes == 0 ) {
					Swal.fire(
						wppm.wpLabel.error_title,
						wppm.wpLabel.add_attributes,
						'error'
					);
				}else {
					if ( typeof window.FormData !== 'function' ) {
						return;
					}
					
					var $this = $ui.find('[type="checkbox"]');
					
					if($this.prop('checked') == true) {
						$('.tr-logic').hide();
						$this.prop('checked', false);
						var enable = false;
					} else {
						$('.tr-logic').show();
						$this.prop('checked', true);
						var enable = true;
					}
					
					var formData = new FormData();
					formData.append( 'product_id', $('#post_ID').val() );
					formData.append( 'enable', enable );
					
					$.ajax( {
						type: 'POST',
						url: wppm.apiSettings + '/enable',
						data: formData,
						dataType: 'json',
						processData: false,
						contentType: false
					});
				}
			}else {
				Swal.fire(
					wppm.wpLabel.error_title,
					wppm.wpLabel.show_if_variable,
					'error'
				);
			}
		},
		
		change_attributes: function() {
			if ( typeof window.FormData !== 'function' ) {
				return;
			}
			
			var wppm_attributes = $('#wppm_wc_variations table .wppm-attributes').map(function() {
				return $( this ).val();
			}).get();
			
			
			
			var wppm_direction = $('#wppm_wc_variations table .wppm-direction').map(function() {
				return $( this ).val();
			}).get();

			var formData = new FormData();
			formData.append( 'product_id', $('#post_ID').val() );
			formData.append( 'attributes', JSON.stringify(wppm_attributes) );
			formData.append( 'direction', JSON.stringify(wppm_direction) );
			
			if( $(this).hasClass('wppm-attributes') ) {
				$(this).closest('tr').find('.pm-remove-row').attr('data-attribute', $(this).val() );
			}
			
			$.ajax( {
				type: 'POST',
				url: wppm.apiSettings + '/save_attributes',
				data: formData,
				dataType: 'json',
				processData: false,
				contentType: false
			});
		},
		
		load_table_attributes: function() {
			if ( typeof window.FormData !== 'function' ) {
				return;
			}
			
			var enable_variation = $("#product_attributes .enable_variation input:checkbox:checked").length;
			var wrapper_attributes = $( '#variable_product_options' ).find( '.woocommerce_variations' ).data( 'attributes' );
			var formData = new FormData();

			formData.append( 'product_id', $('#post_ID').val() );
			var attribute = [];
			$( "#wppm_wc_variations .wppm_wc_row .wppm-attributes" ).each(function( index ) {
				attribute.push( $(this).val() );
			});
			
			var direction = [];
			$( "#wppm_wc_variations .wppm_wc_row .wppm-direction" ).each(function( index ) {
				direction.push( $(this).val() );
			});		

			var checked = false;
			if( $("#is_price_matrix").is(':checked') ) {
				var checked = true;
			}

			$.ajax( {
				type: 'POST',
				url: wppm.apiSettings + '/load',
				data: formData,
				dataType: 'json',
				processData: false,
				contentType: false
			}).done( function( data, status, xhr ) {
				admin_wppm_block.unblock( $('.pm-tab-content' ));
				
				if( data.status == 'complete') {
					$('#wppm_wc_variations').html(data.table);
					$('#pm-order-wrapper').html(data.order);
					admin_wppm.sortable_row();
					admin_wppm.pm_order();
				}else {
					$('#wppm_wc_variations').html(data.message);
				}
				
				$('#tpl-wppm-addrow').html(data.tr);
			}).fail( function( xhr, status, error ) {

			});
			
			console.log('--- load table');
		},
		
		nav_tabs: function() {
			$('#wppm_meta .pm-nav li').removeClass('active');
			var $li = $(this).closest('li');
			var $id = $(this).attr('href');
			$li.addClass('active');

			$('.pm-tab-content .tab-pane').removeClass('in').removeClass('active');
			
			$($id).addClass('in').addClass('active');
			
			return false;
		},
		
		/**
		 * Initial load variations
		 *
		 * @return {Bool}
		 */
		check_enable_price_matrix: function() {
			if( $('#product-type').length ) {
				if( $('#product-type').val() != 'variable' ) {
					$('#is_price_matrix').closest('.ui.toggle').removeClass('active').addClass('disabled');
				}
			}
			
			if( wppm.wpPriceMatrix.enable ) {
				$('#is_price_matrix').prop('checked', true);
			}
		},
		
		start_loading_attribute: function() {
			admin_wppm_block.block( $('.pm-tab-content' ));
		},
		
		order_heading: function() {
			var $li = $(this).closest('li');
			
			if( $li.hasClass('active') ) {
				$li.removeClass('active');
				$li.find('.pm-order-content').slideUp();
			}else {
				$li.addClass('active');
				$li.find('.pm-order-content').slideDown();
			}
			
			return false;
		},

	}
	
	admin_wppm.init();

});