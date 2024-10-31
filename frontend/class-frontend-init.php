<?php
class WPPM_Frontend {

	public $table_position;
	public $show_tooltips;
	public $attribute_qty;

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->hooks();
	}

	private function hooks() {
		global $wp_query;

		add_shortcode( 'wc_pricematrix', array($this, 'add_shortcode_wc_pricematrix') );
		
		$this->table_position = bh_wppm_get('bh_pricematrix_position');
		$this->show_tooltips = bh_wppm_get('bh_pricematrix_showtooltips');
		$this->attribute_qty = bh_wppm_get('bh_pricematrix_quantity');

		if( $this->table_position == 'woocommerce_after_single_product_summary' ) {
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
			add_action('woocommerce_after_single_product_summary',  'woocommerce_template_single_add_to_cart', 5);
		}

		add_action('woocommerce_before_single_variation', array($this, 'woocommerce_single_variation_callback'));
		add_action('wp_enqueue_scripts', array($this, 'embed_style'));

		add_filter( 'body_class', array( $this, 'body_class_price_matrix'), 10, 1 );
		add_filter('woocommerce_cart_item_quantity', array($this, 'wc_cart_item_quantity'), 9999, 3 );
		add_filter( 'woocommerce_checkout_cart_item_quantity', array($this, 'wc_checkout_cart_item_quantity'), 10, 3);
	}

	public function wc_cart_item_quantity($product_quantity, $cart_item_key, $cart_item) {
		$variation = $cart_item['variation'];

		if( $variation ) {
			if( isset($variation['attribute_' . $this->attribute_qty]) ) {
				$quantity = $variation['attribute_' . $this->attribute_qty];
				$product_quantity = sprintf( '<div class="pm-disable-qty"><input type="hidden" name="cart[%s][qty]" value="%d" />', $cart_item_key, 1 );
				ob_start();
				echo $product_quantity;
				wc_get_template( 'global/quantity-input.php', array(
					'input_name'    => "cart[{$cart_item_key}][quantity]",
					'input_value'   => $quantity,
					'max_value' => $quantity,
					'min_value' => 1,

				) );
				echo '</div>';
				return ob_get_clean();
			}
		}

		return $product_quantity;
	}

	public function wc_checkout_cart_item_quantity($html, $cart_item, $cart_item_key) {
		$variation = $cart_item['variation'];

		if( $variation ) {
			if( isset($variation['attribute_' . $this->attribute_qty]) ) {
				$quantity = $variation['attribute_' . $this->attribute_qty];

				return ' <strong class="product-quantity">' . sprintf( '&times; %s', $quantity ) . '</strong>';

			}
		}

	}

	public function body_class_price_matrix( $classes ) {
		$classes[] = 'has-price-matrix';
		$hide_dropdown = bh_wppm_get('bh_pricematrix_hide_dropdown');
	    
	    if( empty($hide_dropdown) ) {
	    	$classes[] = 'pm-hide-dropdown';
	    }

	    if( $this->table_position == 'woocommerce_after_single_product_summary' ) {
	    	$classes[] = 'pm-position-two';
	    }
	    return $classes;
	}

	public function woocommerce_single_variation_callback() {
		global $post;
		echo do_shortcode('[wc_pricematrix product_id="' . $post->ID . '"]');
	}

	public function add_shortcode_wc_pricematrix( $atts ) {
		$atts = shortcode_atts( array(
			'product_id' => '',
		), $atts);

		if( isset($atts['product_id']) ) {
			$product_id 	= $atts['product_id'];
			$product		= wc_get_product($product_id);
			$new_direction  = get_post_meta($product_id, '_pm_variation_attributes', true);

			if( $new_direction ) {
				$vertical		= $new_direction['vertical'];
				$horizontal 	= $new_direction['horizontal'];
				$file_attribute = count($vertical) + count($horizontal);

				switch ($file_attribute) {
					case '1':
						$one_direction = array_keys($new_direction)[0];
						$data_array = bh_wppm_attributes($new_direction[$one_direction][0], $product);
						$file_attribute = $one_direction . '-' . $file_attribute;
						break;
					case '2':
						$vertical_array = bh_wppm_attributes($vertical[0], $product);
						$horizontal_array = bh_wppm_attributes($horizontal[0], $product);
						break;
					default:
						break;
				}
			}else {
				$attribute_one = array_keys( $product->get_attributes( 'edit' ) )[0];
				$data_array = bh_wppm_attributes($attribute_one, $product);
				$file_attribute = 'vertical-1';
			}

			$is_admin = false;
			$show_tooltips = $this->show_tooltips;
			$filename = WPPM_PATH .'templates/table-' . $file_attribute .'.php';
			if( file_exists($filename) ) {
				if( $vertical_array || $data_array ) {
					ob_start();
					$data_attr = ' data-product_id="'. absint($product->get_id()) .'"';
	
					if( isset($pm_variation_attributes[$this->attribute_qty]) ) {
						$data_attr .= ' data-disable-qty="true"';
					}
					echo '<div class="pm-table-variations"'.$data_attr.'>';
						include $filename;
					echo '</div>';
					return ob_get_clean();
				}else {
					return '<p class="wppm-notice-content">'. __('Please Price Matrix table settings and enter the price of the product', WPPM_Price_Matrix::$plugin_id) .'</p>';
				}

			}else {
				return '<p class="wppm-notice-content">'. sprintf( __('The template %s does not exist', WPPM_Price_Matrix::$plugin_id), $file_attribute ) .'</p>';
			}

		}
		
	}

	public function embed_style() {

		if ( $this->show_tooltips ) {
			wp_enqueue_style( 'price-matrix-tooltips', WPPM_URL . 'frontend/assets/css/tippy.css',false,'1.1','all');
			wp_enqueue_script( 'price-matrix-tooltips', WPPM_URL . 'frontend/assets/js/tippy.min.js', null, null, true );
		}

		wp_enqueue_style( 'price-matrix', WPPM_URL . 'frontend/assets/css/style.css',false,'1.1','all');
		wp_enqueue_script( 'price-matrix', WPPM_URL . 'frontend/assets/js/frontend.js', null, null, true );
		wp_localize_script( 'price-matrix', 'wppm', array(
			'apiSettings' => esc_url_raw( rest_url( 'wppm/v1' ) ),
			'ajax_url' => admin_url('/admin-ajax.php'),
			'show_tooltips' => $this->show_tooltips,
			'add_to_cart' => bh_wppm_get('bh_pricematrix_addtocart'),
			'attribute_qty'	=> bh_wppm_get('bh_pricematrix_quantity')
		));
		
		$css_background_table = bh_wppm_get('bh_pricematrix_style_bg');
		$css_bordercolor_table = bh_wppm_get('bh_pricematrix_style_bordercolor');
		$css_textcolor_table = bh_wppm_get('bh_pricematrix_style_textcolor');
		$css_fontsize_table = bh_wppm_get('bh_pricematrix_style_fontsize');

        $custom_css = "
            .wppm-enter-table .attribute-name{
                background: {$css_background_table};
			}
			.wppm-enter-table .attribute-price:hover {
				background: {$css_background_table};
			}
            .wppm-enter-table .attribute-name {
            	color: {$css_textcolor_table}
            }
            .wppm-enter-table td {
            	font-size: {$css_fontsize_table}px;
			}
			.wppm-enter-table td {
				border-color: {$css_bordercolor_table}
			}";

        if( $this->show_tooltips ) {
        	$css_tooltips_bg = bh_wppm_get('bh_pricematrix_tooltips_bg');
        	$css_tooltips_colortext = bh_wppm_get('bh_pricematrix_tooltips_colortext');
        	$css_tooltips_fontsize = bh_wppm_get('bh_pricematrix_tooltips_fontsize');
        	$css_tooltips_bordercolor = bh_wppm_get('bh_pricematrix_tooltips_colorborder');

        	$custom_css .= "
        		.tippy-tooltip {
        			background-color: {$css_tooltips_bg};
        			color: {$css_tooltips_colortext};
        			font-size: {$css_tooltips_fontsize}px;
        		}
        		.tippy-popper[x-placement^=bottom] [x-arrow] {
        			border-bottom: 7px solid {$css_tooltips_bg}
        		}
        		.tippy-popper[x-placement^=top] [x-arrow] {
        			border-top: 7px solid {$css_tooltips_bg}
        		}
        		.tippy-popper table > tbody > tr > td {
        			border-color: {$css_tooltips_bordercolor}
        		}

        	";
        }
        wp_add_inline_style( 'price-matrix', $custom_css );
	}
}

new WPPM_Frontend();