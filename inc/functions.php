<?php
if( ! function_exists('bh_wppm_notice_attributes') ) {
	function bh_wppm_notice_attributes() {
		ob_start();

		include WPPM_PATH . 'admin/metabox/tpl.notice.php';

		return ob_get_clean();
	}
}

if( ! function_exists('bh_wppm_direction') ) {
	function bh_wppm_direction() {
		return array(
			'vertical' => 'Vertical',
			'horizontal' => 'Horizontal'
		);
	}
}

if( ! function_exists('bh_wppm_attributes') ) {
	function bh_wppm_attributes($term, $product) {
		$get_attributes = $product->get_attributes( 'edit' );
		
		if(isset($get_attributes[$term])) {
			$attribute = $get_attributes[$term];
			$_pm_order_attributes = get_post_meta($product->get_id(), '_pm_order_attributes', true);

			if ( $attribute->is_taxonomy() ) {
				$new_terms = array();
				foreach ($attribute->get_terms() as $key => $value) {
					$new_terms[$value->slug] = $value;
				}

				$array_terms = array();
				if( isset($_pm_order_attributes[$term]) ) {
					foreach ($_pm_order_attributes[$term] as $k => $val) {
						if( isset($new_terms[$val]) ) {
							$array_terms[$val] = $new_terms[$val];
						}
					}

					//return $array_terms;
				}
				$array_terms = array_merge($array_terms, $new_terms);
				return $array_terms;
			}else {
				$new_terms = array();
				foreach ($attribute->get_options() as $key => $value) {
					$slug = trim($value);
					$new_terms[$slug] = json_decode(json_encode(array(
						'taxonomy' => $term,
						'name' => $slug,
						'slug' => $slug,
						'is_taxonomy' => false
					)), FALSE);
				}

				$array_terms = array();
				if( isset($_pm_order_attributes[$term]) ) {
					foreach ($_pm_order_attributes[$term] as $k => $val) {
						if( isset($new_terms[$val]) ) {
							$array_terms[$val] = $new_terms[$val];
						}
					}
				}

				if( empty($array_terms) ) {
					$array_terms = $new_terms;
				}

				//$array_terms = array_merge($array_terms, $new_terms);

				return $array_terms;
			}
		}
	}
}

if( ! function_exists('bh_wppm_price_input') ) {
	function bh_wppm_price_input($attributes, $product_id, $show_price = false) {
		global $wpdb;

		$sql = "SELECT posts.ID as post_id, posts.post_parent as parent FROM {$wpdb->posts} as posts";

		if( $attributes ) {
			$i = 0;
			foreach ($attributes as $value) {
				$sql .= " INNER JOIN {$wpdb->postmeta} AS postmeta".$i." ON posts.ID = postmeta".$i.".post_id";
				$i++;
			}
		}
		$sql .= " WHERE posts.post_parent = '".$product_id."' AND posts.post_type IN ( 'product', 'product_variation' ) AND posts.post_status = 'publish'";
		if( $attributes ) {
			$index_value = 0;
			foreach ($attributes as $k_attr => $attr) {
				$sql .= " AND postmeta".$index_value.".meta_key = '".$k_attr."' AND postmeta".$index_value.".meta_value = '". $attr ."'";
				$index_value++;
			}

			$rs = $wpdb->get_row($sql);

			if( $rs ) {
				$_regular_price = get_post_meta($rs->post_id, '_regular_price', true);
				$_sale_price = get_post_meta($rs->post_id, '_sale_price', true);
				$_price = get_post_meta($rs->post_id, '_price', true);

				$show_price_r = bh_wppm_get('bh_pricematrix_show_price');

				if( $show_price ) {
					if( $_regular_price && $_sale_price && $show_price_r) {
						return '<del>' . wc_price($_regular_price) .'</del><ins>' . wc_price($_price) .'</ins>';
					}else {
						return '<ins>' . wc_price($_price) .'</ins>';
					}
				}else {
					if( $_regular_price && $_sale_price ) {
						return $_regular_price . '-' . $_sale_price;
					}else {
						return $_regular_price;
					}	
				}
			}
		}
	}
}

if( ! function_exists('bh_wppm_limit') ) {
	function bh_wppm_limit() {
		$limit = get_option('bh_wppm_limit');
		if( $limit < 3) {
			return false;
		}else {
			return true;
		}
	}
}

if( ! function_exists('bh_wppm_get') ) {
	function bh_wppm_get($name) {
		$get_data = get_option( WPPM_Price_Matrix::$plugin_id .'_fields' );
		if( empty($get_data[$name]) ) {
			$data = WPPM_Admin::$postname_allowed[$name];
		}else {
			$data = $get_data[$name];
		}

		return $data;
	}
}

if( ! function_exists('pm_sort_data') ) {
	function pm_sort_data($data_attribute) {
		$new_array = array();

		if( isset($data_attribute['vertical']) || isset($data_attribute['horizontal']) ) {
			foreach ($data_attribute as $k => $values) {
				foreach ($values as $key => $value) {
					$new_array[$value] = array(
						'attribute' => $value,
						'direction' => $k
					);
				}
			}
		}else {
			$new_array = $data_attribute;
		}

        return $new_array;
	}
}

if( ! function_exists('pm_attribute_tax') ) {
	function pm_attribute_tax($tax, $product_id){
		global $wpdb;

		$_product_attributes = get_post_meta($product_id, '_product_attributes', TRUE);
		if(isset($_product_attributes[$tax])){
			$data = $_product_attributes[$tax];
			if($data['is_taxonomy']){
				$tax = str_replace('pa_', '', $tax);
				$rs =  $wpdb->get_row($wpdb->prepare("SELECT attribute_label FROM ".$wpdb->prefix."woocommerce_attribute_taxonomies WHERE attribute_name = '%s'", $tax), OBJECT);
				if($rs){
					return $rs->attribute_label;
				}
			}else{
				return $data['name'];
			}
		}
	}
}

if( ! function_exists('pm_direction_to_attribute') ) {
	function pm_direction_to_attribute( $arrays ) {
		$new = array();

		if( isset($arrays['vertical']) ) {
			foreach( $arrays['vertical'] as $k => $array) {
				$new[$array] = array(
					'direction' => 'vertical',
					'key'		=> $k
				);
			}
		}

		if( isset($arrays['horizontal']) ) {
			foreach( $arrays['horizontal'] as $k => $array) {
				$new[$array] = array(
					'direction' => 'horizontal',
					'key'		=> $k
				);
			}
		}

		return $new;
	}
}