<?php
if( ! class_exists('WPPM_Frontend_Ajax') ) {
	class WPPM_Frontend_Ajax {

		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'rest_api_init', array(&$this, 'rest_api_init') );
			add_action( 'wp_ajax_pricematrix_save_price', array($this, 'wppm_save_price') );
		}

		public function rest_api_init() {
			$namespace = 'wppm/v1';

			register_rest_route( $namespace, '/add_to_cart', array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array($this, 'wppm_add_to_cart'),
			));
		}

		public function wppm_add_to_cart( WP_REST_Request $request ) {
			global $wpdb;
			$response = array();
			$product_id = (int) $request->get_param( 'product_id' );
			$attributes = $request->get_param( 'attribute' );

			$product = wc_get_product($product_id);

			/* Overwrite */
			if( $attributes ) {
				$attributes = json_decode($attributes, true);

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
				}

				$rs = $wpdb->get_row($sql);

				if( $rs ) {
					$rs_cart = WC()->cart->add_to_cart( $product_id, 1, $rs->post_id, $attributes, null );

					if( $rs_cart ) {
						wc_add_to_cart_message( array( $product_id => 1 ), true );

						$response = array(
							'status' => 'complete',
							'msg' => sprintf( _n( '%s has been added to your cart.', '%s have been added to your cart.', $count, 'woocommerce' ), $product->get_title() )
						);

						if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
							
							$response['url'] = esc_url( wc_get_page_permalink( 'cart' ) );
						}
					}
				}
			}

			return rest_ensure_response( $response );
		}


	}

	new WPPM_Frontend_Ajax();
}
