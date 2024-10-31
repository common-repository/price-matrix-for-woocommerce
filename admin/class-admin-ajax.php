<?php
if( ! class_exists('WPPM_Admin_Ajax') ) {
	class WPPM_Admin_Ajax {

		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'rest_api_init', array(&$this, 'rest_api_init') );
			add_action( 'wp_ajax_pricematrix_save_price', array($this, 'wppm_save_price') );
		}

		public function rest_api_init() {
			$namespace = 'wppm/v1';

			register_rest_route( $namespace, '/load', array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array($this, 'wppm_get_attributes'),
				'permission_callback' => '__return_true'
			));

			register_rest_route( $namespace, '/add_row', array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array($this, 'wppm_add_row'),
				'permission_callback' => '__return_true'
			));

			register_rest_route( $namespace, '/save_attributes', array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array($this, 'wppm_save_attributes'),
				'permission_callback' => '__return_true'
			));

			register_rest_route( $namespace, '/remove_attributes', array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array($this, 'wppm_remove_attributes'),
				'permission_callback' => '__return_true'
			));

			register_rest_route( $namespace, '/input_price', array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array($this, 'wppm_input_price'),
				'permission_callback' => '__return_true'
			));

			register_rest_route( $namespace, '/order_attributes', array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array($this, 'wppm_order_attributes'),
				'permission_callback' => '__return_true'
			));

			register_rest_route( $namespace, '/enable', array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array($this, 'wppm_enable_pm'),
				'permission_callback' => '__return_true'
			));
			
			register_rest_route( $namespace, '/order_table', array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array($this, 'wppm_order_table'),
				'permission_callback' => '__return_true'
			));
		}

		public function wppm_enable_pm( WP_REST_Request $request ) {
			$response = array();
			$product_id = (int) $request->get_param( 'product_id' );
			$enable = filter_var($request->get_param( 'enable' ), FILTER_VALIDATE_BOOLEAN);
			$product = wc_get_product($product_id);

			if($product) {
				update_post_meta( $product_id, '_is_price_matrix', $enable );
			}
		}

		
		public function wppm_add_row( WP_REST_Request $request ) {
			$json		= array();
			$html		= '';
			$product_id = (int) $request->get_param( 'product_id' );
			$product	= wc_get_product($product_id);
			$lists_attribute = array();

			if( empty($product) ) {
				return;
			}

			$get_attributes 		 = $product->get_attributes( 'edit' );
			$pm_variation_attributes = get_post_meta($product->get_id(), '_pm_variation_attributes', true);

			if( $get_attributes ) {
				foreach ($get_attributes as $attr => $attribute) {
					if( $attribute->get_variation() ) {
						if ( $attribute->is_taxonomy() ) {
							$lists_attribute[$attr] = esc_html( wc_attribute_label( $attribute->get_name() ) );
						}else {
							$lists_attribute[$attr] = esc_attr( $attribute->get_name() );
						}
					}
				}
			}


			if( $pm_variation_attributes && WPPM_Price_Matrix::$settings_other && count($pm_variation_attributes) < 2 ) {
				$data_attr = $option_selected = '';
				$direction_to_attribute = pm_direction_to_attribute($pm_variation_attributes);
				$html .= '<tr class="wppm_wc_row">
					<td class="wppm_wc_drag"><i class="dashicons dashicons-move"></i></td>
					<td>
						<select name="attributes[]" class="wppm-select wppm-attributes">
							<optgroup label="'. __('Select an Attributes', 'woocommerce') . '">';
							$i = 0;
							foreach ($lists_attribute as $key => $value) {
								if( ! isset($direction_to_attribute[$key]) ) {
									$selected = '';
									if( $i == 0) {
										$data_attr = ' data-attribute="' . $key . '"';
										$option_selected = $key;
										$pm_variation_attributes['vertical'][] = $key;
										$selected = ' selected';
									}
									$html .= '<option value="'. $key .'"'.$selected.'>'. $value .'</option>';
									$i++;
								}
							}
						$html .= '</optgroup></select>
					</td>
					<td>
						<select name="direction[]" class="wppm-select wppm-direction">';
							foreach (bh_wppm_direction() as $k_direction => $vl_direction) {
								$html .= '<option value="'. $k_direction .'">'. $vl_direction .'</option>';
							}
						$html .= '</select>
					</td>
					<td>
						<div class="pm-button pm-remove-row"'.$data_attr.'><i class="icon-trash-o"></i></div>
					</td>
				</tr>';

				update_post_meta( $product->get_id(), '_pm_variation_attributes', $pm_variation_attributes);
				$json['complete'] = true;
				$json['html'] = $html;
				$json['option'] = $option_selected;
			}else {
				$json['title'] = 'You can\'t add row';
				$json['message'] = 'Plugin <strong>Price Matrix for Woocommerce Lite</strong> only support two attributes. <a href="http://bit.ly/2CzPObN" target="_blank">Click here</a> to use three or more attributes, please use plugin <strong>Price Matrix for Woocommerce PRO</strong>.';
			}

			wp_send_json($json);
		}



		public function wppm_get_attributes( WP_REST_Request $request ) {
			$response = $save_attribute = array();
			$html = $table = '';
			$product_id = (int) $request->get_param( 'product_id' );
			$product = wc_get_product($product_id);
			$pm_variation_attributes = pm_sort_data(get_post_meta($product->get_id(), '_pm_variation_attributes', true));

			if( $get_attributes = $product->get_attributes( 'edit' ) ) {
				$html = null;
				$data_attribute = array();
				$total_attribute = 0;
				foreach( $get_attributes as $key_attribute => $attribute ) {
					if( $attribute->get_variation() ) {
						$data_attribute[$key_attribute] = array(
							'attribute' => $key_attribute,
							'direction' => 'vertical'
						);

						if( isset($pm_variation_attributes[$key_attribute]) ){
							$data_attribute[$key_attribute] = $pm_variation_attributes[$key_attribute];
						}

						if ( $attribute->is_taxonomy() ) {
							$data_attribute[$key_attribute]['label'] = esc_html( wc_attribute_label( $attribute->get_name() ) );
						}else {
							$data_attribute[$key_attribute]['label'] = esc_attr( $attribute->get_name() );
						}
						$total_attribute += 1;
					}
				}

				if( $total_attribute ) {


					$i = 0;
					foreach ($data_attribute as $attr => $attribute) {
						if( WPPM_Price_Matrix::$settings_other && $i < 2 ) {
							$save_attribute[$attribute['direction']][] = esc_attr($attr);
							$direction = $attribute['direction'];
							$html .= '
							<tr class="wppm_wc_row">
								<td class="wppm_wc_drag"><i class="dashicons dashicons-move"></i></td>
								<td>
									<select name="attributes[]" class="wppm-select wppm-attributes">
										<option value="'. esc_attr($attr) .'">'. esc_attr($attribute['label']) .'</option>
									</select>
								</td>
								<td>
									<select name="direction[]" class="wppm-select wppm-direction">';
									foreach (bh_wppm_direction() as $k_direction => $vl_direction) {
										$direction_selected = '';
										if( isset($data_attribute[$attr]['direction']) && $k_direction == $data_attribute[$attr]['direction'] ) {
											$direction_selected = ' selected="selected"';
										}
										$html .= '<option value="'.$k_direction.'"'.$direction_selected.'>'. $vl_direction .'</option>';
									}
									$html .= '
									</select>
								</td>
								<td>
										<div class="pm-button pm-remove-row" data-attribute="'. $attr .'"><i class="icon-trash-o"></i></div>
								</td>
							</tr>';
						}

						$i++;
					}

					update_post_meta($product_id, '_pm_variation_attributes', $save_attribute);

					ob_start();
					include_once WPPM_PATH . 'admin/templates/general.php';
					$table = ob_get_clean();

					$response = array(
						'status' => 'complete',
						'table' => $table,
						'order' => $this->wppm_load_order($product, $get_attributes)
					);
				}
			}else {
				$response = array(
					'status' => 'error',
					'message' => bh_wppm_notice_attributes()
				);
			}

			return rest_ensure_response( $response );
		}


		public function wppm_save_attributes(WP_REST_Request $request ) {
			$response = array();
			$product_id = (int) $request->get_param( 'product_id' );
			$attributes = json_decode($request->get_param( 'attributes' ));
			$direction = json_decode($request->get_param( 'direction' ));

			$save_data = array();
			if( $attributes && is_numeric($product_id) ) {
				foreach ($attributes as $k => $attribute) {
					if( $attribute && isset($direction[$k])) {
						$save_data[$direction[$k]][] =  $attribute;
 					}
				}

				update_post_meta($product_id, '_pm_variation_attributes', $save_data);
			}
		}

		public function wppm_remove_attributes( WP_REST_Request $request ) {
			$response = array();
			$product_id = (int) $request->get_param( 'product_id' );
			$attribute = $request->get_param( 'attribute' );

			$product = wc_get_product($product_id);
			$pm_variation_attributes = get_post_meta($product->get_id(), '_pm_variation_attributes', true);

			if( $product ) {
				if( $pm_variation_attributes ) {
					$new_array = array();

					$direction_to_attribute = pm_direction_to_attribute($pm_variation_attributes);
					if( isset($direction_to_attribute[$attribute]) && $attr = $direction_to_attribute[$attribute] ) {
						unset($pm_variation_attributes[$attr['direction']][$attr['key']]);
					}

					$pm_variation_attributes = array_filter($pm_variation_attributes);

					update_post_meta($product_id, '_pm_variation_attributes', $pm_variation_attributes);

					$response = array(
						'status' => 'complete'
					);
				}else {
					if( $get_attributes = $product->get_attributes( 'edit' ) ) {
						$data_attribute = array();
						foreach( $get_attributes as $key_attribute => $_attribute ) {
							if( $_attribute->get_variation() ) {
								if( $key_attribute != $attribute ) {
									$response = array(
										'status' => 'complete'
									);
									$data_attribute['vertical'][] = $key_attribute;
								}
							}
						}

						update_post_meta($product_id, '_pm_variation_attributes', $data_attribute);


					}
				}
			}

			return rest_ensure_response( $response );
		}

		public function wppm_input_price( WP_REST_Request $request ) {
			$response = array();
			$product_id = (int) $request->get_param( 'product_id' );
			$product = wc_get_product($product_id);
			$new_direction = get_post_meta($product->get_id(), '_pm_variation_attributes', true);
			$get_attributes = $product->get_attributes( 'edit' );

			$total_attribute = count($get_attributes);

			// Validate direction
			$validate = false;
			if( $total_attribute == 2 || $total_attribute == 3 || $total_attribute == 4 ) {	
				if( empty($new_direction['horizontal']) || empty($new_direction['vertical']) ) {
					return rest_ensure_response(array(
						'status' => 'error',
						'message' => __('Please select the direction of horizontal/vertical attributes!', WPPM_Price_Matrix::$plugin_id )
					));
				}
			}
			
			$is_admin = true;
			if( $new_direction ) {
				$vertical = $new_direction['vertical'];
				$horizontal = $new_direction['horizontal'];

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
						# code...
						break;
				}

				
				ob_start();
				$file_template = WPPM_PATH .'templates/table-' . $file_attribute .'.php';
				if( ! file_exists($file_template) ) {
					echo 'trog';
				}else {
					include $file_template;
				}
				
				$html = ob_get_clean();

				$response = array(
					'status' => 'complete',
					'html' => $html
				);
			}else {
				

				if( count($get_attributes) == 1) {
					$data_array = bh_wppm_attributes(array_keys($get_attributes)[0], $product);
					ob_start();
					include WPPM_PATH .'templates/table-vertical-1.php';
					$html = ob_get_clean();
	
					$response = array(
						'status' => 'complete',
						'html' => $html
					);
				}else {
					$response = array(
						'status' => 'error',
						'message' => __('Please select the direction of horizontal/vertical attributes!', WPPM_Price_Matrix::$plugin_id )
					);
				}
				
			}

			return rest_ensure_response( $response );
		}

		public function wppm_save_price() {
			global $wpdb;

			$response 	= array();
			$product_id = (int) $_POST['product_id'];
			$post       = get_post( $product_id );
			$product 	= wc_get_product($product_id);
			$pm_variation_attributes = get_post_meta($product->get_id(), '_pm_variation_attributes', true);
			$data = $_POST['data'];
			
			$layout = (int) $_POST['layout'];

			parse_str($data, $output);


			if( isset($output['attribute']) && isset($output['price']) && $layout ) {
				$attributes = $output['attribute'];
				$prices = $output['price'];


				$price_array = array();
				$new = '';
				$sql = '';
				$attributes_one = bh_wppm_attributes($attributes[0], $product);
				
				if( $attributes_one || empty($pm_variation_attributes) ) {
					$attributes_two = bh_wppm_attributes($attributes[1], $product);
					$new_data = array();

					if( $layout == 1 ) {
						if($pm_variation_attributes) {
							foreach ($attributes_one as $key => $attr_one) {
								if( isset($prices[$attr_one->slug]) ) {
									$price_one = $prices[$attr_one->slug];
									if( preg_match("/(.*)-(.*)/", $price_one, $output_array) ) {
										$price = array(
											'_regular_price' => trim($output_array[1]),
											'_sale_price' => trim($output_array[2]),
											'_price' => trim($output_array[2])
										);
									}else {
										$price = array(
											'_regular_price' => trim($price_one),
											'_price' => trim($price_one)
										);
									}

									$new_data[] = array_merge(array(
										'attribute' => array(
											'attribute_' . $attributes[0] => $attr_one->slug
										)
									), $price);
								}
							}
						}else {
							foreach ($product->get_attributes( 'edit' ) as $key => $attribute) {
								if( $attribute->get_variation() ) {
									if ( $attribute->is_taxonomy() ) {
										$new_terms = array();
										foreach ($attribute->get_terms() as $key => $value) {
											if( isset($prices[$value->slug]) ) {
												$price_one = $prices[$value->slug];
												if( preg_match("/(.*)-(.*)/", $price_one, $output_array) ) {
													$price = array(
														'_regular_price' => trim($output_array[1]),
														'_sale_price' => trim($output_array[2]),
														'_price' => trim($output_array[2])
													);
												}else {
													$price = array(
														'_regular_price' => trim($price_one),
														'_price' => trim($price_one)
													);
												}
			
												$new_data[] = array_merge(array(
													'attribute' => array(
														'attribute_' . $attributes[0] => $value->slug
													)
												), $price);
											}
																		
										}
									} else {
										$array = array();
										foreach ($attribute->get_options() as $key => $value) {
											$slug = trim($value);
											if( isset($prices[$slug]) ) {
												$price_one = $prices[$slug];
												if( preg_match("/(.*)-(.*)/", $price_one, $output_array) ) {
													$price = array(
														'_regular_price' => trim($output_array[1]),
														'_sale_price' => trim($output_array[2]),
														'_price' => trim($output_array[2])
													);
												}else {
													$price = array(
														'_regular_price' => trim($price_one),
														'_price' => trim($price_one)
													);
												}
			
												$new_data[] = array_merge(array(
													'attribute' => array(
														'attribute_' . $attributes[0] => $slug
													)
												), $price);
											}
										}
									}

								}
							}
						}
					}elseif( $layout == 2 ) {

						foreach ($attributes_one as $key => $attr_one) {
							if( isset($prices[$attr_one->slug]) ) {
								$price_one = $prices[$attr_one->slug];
								
								$i = 0;
								foreach ($attributes_two as $key => $attr_two) {
									if( isset($price_one[$attr_two->slug]) ) {
										$price_two = $price_one[$attr_two->slug];

										if( preg_match("/(.*)-(.*)/", $price_two, $output_array) ) {
											$price = array(
												'_regular_price' => trim($output_array[1]),
												'_sale_price' => trim($output_array[2]),
												'_price' => trim($output_array[2])
											);
										}else {
											$price = array(
												'_regular_price' => trim($price_two),
												'_price' => trim($price_two)
											);
										}

										$new_data[] = array_merge(array(
											'attribute' => array(
												'attribute_' . $attributes[0] => $attr_one->slug,
												'attribute_' . $attributes[1] => $attr_two->slug
											)
										), $price);
									}
									$i++;
								}
								
							}
						}
					}
					

					/* Insert Data */

					if( $new_data ) {
						foreach ($new_data as $k => $val) {
							$sql = "SELECT posts.ID as id, posts.post_parent as parent FROM {$wpdb->posts} as posts";
							if(is_array($val['attribute']) && !empty($val['attribute'])){
								$i = 0;
								foreach ($val['attribute'] as $k_attr => $attr) {
									$sql .= " INNER JOIN {$wpdb->postmeta} AS postmeta".$i." ON posts.ID = postmeta".$i.".post_id";
									$i++;
								}
							}

							$sql .= " WHERE posts.post_parent = '".$product_id."' AND posts.post_type IN ( 'product', 'product_variation' ) AND posts.post_status = 'publish'";

							if(is_array($val['attribute']) && !empty($val['attribute'])){
								$index_value = 0;
								foreach ($val['attribute'] as $k_attr => $attr) {
									$sql .= " AND postmeta".$index_value.".meta_key = '".sanitize_text_field($k_attr)."' AND postmeta".$index_value.".meta_value = '". sanitize_text_field($attr) ."'";
									$index_value++;
								}
							}

							//echo $sql."\n";

							$items = $wpdb->get_row($sql);
		

							if( $items ){
								$post_id = $items->id;
							}else {
								$my_post = array(
									'post_title'    => wp_strip_all_tags( $product->get_name() ),
									'post_comment'  => 'closed',
									'post_status'   => 'publish',
									'ping_status'   => 'closed',
									'post_author'   => $post->post_author,
									'post_name'   => $post->post_name,
									'post_parent'   => $product->get_id(),
									'guid'   => get_permalink($product->get_id()),
									'post_type' => 'product_variation'
								);
								$post_id = wp_insert_post( $my_post );
							}


							update_post_meta($post_id, 'attribute_price_matrix', $val['attribute']);
							foreach ($val['attribute'] as $attribute_key => $attribute_name) {
								unset($val['attribute']);
								foreach ($val as $meta_key => $meta_value) {
									update_post_meta($post_id, $meta_key, $meta_value);
								}

								update_post_meta($post_id, $attribute_key, $attribute_name);
								update_post_meta($post_id, '_stock_status', 'instock');
							}


						}

						$response = array(
							'status' => 'complete',
							'message'	=> __( 'Successfully saved, press the Update button again to complete!', WPPM_Price_Matrix::$plugin_id )
						);
					}
					/* End Send Data */
					
				}
				

			}

			wp_send_json( $response );
			wp_die();
		}

		public function wppm_load_order($product, $get_attributes) {
			if( $get_attributes ) {
				$_pm_order_attributes = get_post_meta($product->get_id(), '_pm_order_attributes', true);

				$new_attribute = array();
				$total_attribute = 0;
				foreach ($get_attributes as $attr => $attribute) {
					if( $attribute->get_variation() ) {
						if ( $attribute->is_taxonomy() ) {
							$new_terms = array();
							foreach ($attribute->get_terms() as $key => $value) {
								$new_terms[$value->slug] = $value;								
							}
							
							$new_attribute[$attr] = array(
								'label' => esc_html( wc_attribute_label( $attribute->get_name() ) ),
								'terms' => $new_terms
							);
						} else {
							$value_array = $attribute->get_options();
							$array = array();

							foreach ($value_array as $key => $value) {
								$slug = trim($value);
								$array[$slug] = array(
									'taxonomy' => $tax,
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
						$total_attribute += 1;
					}
				}

				if( $total_attribute ) {
					if( $_pm_order_attributes ) {
						foreach ($_pm_order_attributes as $k => $value_attrs) {
							$n = array();
							foreach ($value_attrs as $key => $value_attr) {
								if( isset($new_attribute[$k]['terms'][$value_attr]) ) {
									
									$n[$value_attr] = $new_attribute[$k]['terms'][$value_attr];
									unset($new_attribute[$k]['terms']->$value_attr);
								}
							}
							$final_array = array_merge($n, $new_attribute[$k]['terms']);
							$new_attribute[$k]['terms'] = json_decode(json_encode($final_array), FALSE);
						}
					}

					ob_start();
					include WPPM_PATH .'admin/metabox/tpl.order.php';
					return ob_get_clean();
				}
			}
		}

		public function wppm_order_attributes( WP_REST_Request $request ) {
			$response = array();
			$product_id = (int) $request->get_param( 'product_id' );
			$_attribute = $request->get_param( 'attribute' );
			$taxonomy = $request->get_param( 'taxonomy' );
			$product = wc_get_product($product_id);

			$get_attributes = $product->get_attributes( 'edit' );
			$_pm_order_attributes = get_post_meta($product->get_id(), '_pm_order_attributes', true);

			if( $get_attributes ) {
				$new_attribute = array();
				foreach ($get_attributes as $attr => $attribute) {
					if( $attribute->get_variation() ) {
						if ( $attribute->is_taxonomy() ) {
							$new_terms = array();
							foreach ($attribute->get_terms() as $key => $value) {
								$new_attribute[$attr][$value->slug] = $value->slug;
							}
						} else {
							$value_array = $attribute->get_options();
							foreach ($value_array as $key => $value) {
								$slug = trim($value);
								$new_attribute[$attr][$slug] = $slug;
							}
						}
						$total_attribute += 1;
					}
				}



				if( $_pm_order_attributes ) {

					foreach ($_pm_order_attributes as $k => $value_attrs) {
						$n = array();
						foreach ($value_attrs as $key => $value_attr) {
							if( isset( $new_attribute[$k][$value_attr] ) ) {
								unset($new_attribute[$k][$value_attr]);
								$n[$value_attr] = $value_attr;
							}
						}
						$new_attribute[$k] = array_merge($n, $new_attribute[$k]);
					}
				}

				/* Overwrite */
				if( $_attribute ) {
					$_attribute = str_replace('"', '', $_attribute);
					$_attribute = explode(',', $_attribute);
					
					$resort = array();
					if( isset($new_attribute[$taxonomy]) ) {
						$new = array();
						foreach ($_attribute as $k => $at) {
							$new[$at] = $at;
						}
						$new_attribute[$taxonomy] = array_merge($new, $new_attribute[$taxonomy]);

						update_post_meta($product_id, '_pm_order_attributes', $new_attribute);
					}
				}
			}

			return rest_ensure_response( $response );
		}
		
		public function wppm_order_table( WP_REST_Request $request ) {
			$response = array();
			$product_id = (int) $request->get_param( 'product_id' );
			$attributes = json_decode( $request->get_param( 'attribute' ) );
			$direction = json_decode( $request->get_param( 'direction' ) );
			
			$product = wc_get_product($product_id);
			
			if( ! empty($product) ) {
				$save_data = array();
				if( $attributes && is_numeric($product_id) ) {
					foreach ($attributes as $k => $attribute) {
						if( $attribute && isset($direction[$k])) {
							$save_data[$direction[$k]][] =  $attribute;
						 }
					}
	
					update_post_meta($product_id, '_pm_variation_attributes', $save_data);
				}
			}
		}


	}

	new WPPM_Admin_Ajax();
}