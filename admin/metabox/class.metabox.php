<?php
class WPPM_Metabox {

	public $is_pricematrix;
	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
	}

	/**
	 * Adds the Yoast SEO meta box to the edit boxes in the edit post, page,
	 * attachment, and custom post types pages.
	 *
	 * @return void
	 */
	public function add_meta_box() {
		global $post_type;

		if( isset($post_type) && $post_type != 'product' ) {
			return;
		}

		add_meta_box( 'wppm_meta', 'Price Matrix', array($this, 'metabox_price_matrix_output'), 'product', 'normal' );

	}

	public function metabox_price_matrix_output( $post ) {
		$product = wc_get_product($post->ID);
		$is_price_matrix = get_post_meta($product->get_id(), '_is_price_matrix', true);
		$pm_variation_attributes = get_post_meta($product->get_id(), '_pm_variation_attributes', true);
		$_pm_order_attributes = get_post_meta($product->get_id(), '_pm_order_attributes', true);

		$this->is_pricematrix = get_post_meta($post->ID, '_is_price_matrix', true);




		$get_attributes = $product->get_attributes( 'edit' );

		$data_attribute = array();
		$new_attribute = array();
		$has_attribute = false;
		if( $get_attributes ) {
			$lists_attribute = array();
			$total_attribute = 0;

			foreach ($get_attributes as $attr => $attribute) {
				if( $attribute->get_variation() ) {
					if ( $attribute->is_taxonomy() ) {
						$lists_attribute[$attr] = esc_html( wc_attribute_label( $attribute->get_name() ) );

							$new_terms = array();
							foreach ($attribute->get_terms() as $key => $value) {
								$new_terms[$value->slug] = $value;								
							}
							
							$new_attribute[$attr] = array(
								'label' => esc_html( wc_attribute_label( $attribute->get_name() ) ),
								'terms' => $new_terms
							);

					}else {
						$lists_attribute[$attr] = esc_attr( $attribute->get_name() );
						$value_array = $attribute->get_options();
						$array = array();

						foreach ($value_array as $key => $value) {
							$slug = trim($value);
							$array[$slug] = array(
								'taxonomy' => '',
								'name' => $slug,
								'slug' => $slug,
								'is_taxonomy' => false
							);
						}
						$new_attribute[$attr] = array(
							'label' => esc_attr( $attribute->get_name() ),
							'terms' => $array
						);
					}
					$data_attribute[$attr] = array(
						'attribute' => $attr,
						'direction' => 'vertical'
					);
					
					$total_attribute += 1;
				}
			}

			if( $_pm_order_attributes ) {
				foreach ($_pm_order_attributes as $k => $value_attrs) {
					$n = array();
					foreach ($value_attrs as $key => $value_attr) {
						if( isset($new_attribute[$k]['terms'][$value_attr]) ) {
							
							$n[$value_attr] = $new_attribute[$k]['terms'][$value_attr];
							unset($new_attribute[$k]['terms']->$value_attr);
						}
					}


					if( ! empty($n) ) {
						$final_array = $n;
					}else {
						$final_array = $new_attribute[$k]['terms'];
					}

					//$final_array = array_merge($n, $new_attribute[$k]['terms']);
					$new_attribute[$k]['terms'] = json_decode(json_encode($final_array), FALSE);


				}
			}




			if( $total_attribute ) {
				$has_attribute = true;
			}

			if( !empty($pm_variation_attributes) ) {
				$data_attribute = $pm_variation_attributes;
			}
		}

		include_once WPPM_PATH . 'admin/metabox/tpl.metabox.php';
	}
}