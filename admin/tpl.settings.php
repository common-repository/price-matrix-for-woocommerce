<div class="wrap">
	<?php if( isset($_POST['bh_pricematrix_save']) ) {?>
	<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
		<p><strong><?php _e( 'Settings saved.' ); ?></strong></p>
		<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>
	<?php }?>
	
	<div class="bh-options-wrap">
		<h3><?php esc_html_e('General Settings', WPPM_Price_Matrix::$plugin_id);?></h3>
		<form  method="post">
			<div id="pm-settings-page">
				<table class="form-table">
				   <tr valign="top">
				      <th scope="row" class="titledesc">
				         <label for="yith_woocompare_is_button"><?php esc_html_e('Position', WPPM_Price_Matrix::$plugin_id);?></label>
				         <span class="woocommerce-help-tip" data-tip="Choose if you want to use a link or a button for the comepare actions."></span>							
				      </th>
				      <td class="forminp forminp-select">
				      	<?php $table_position = bh_wppm_get('bh_pricematrix_position');?>
				         <select name="bh_pricematrix_position" class="pm-enhanced-select">
				            <option value="woocommerce_before_single_variation"<?php if( $table_position == 'woocommerce_before_single_variation' ) { echo ' selected'; }?>>woocommerce_before_single_variation</option>
				            <option value="woocommerce_after_single_product_summary"<?php if( $table_position == 'woocommerce_after_single_product_summary' ) { echo ' selected'; }?>>woocommerce_after_single_product_summary</option>
				         </select>
				      </td>
				   </tr>
				   <tr valign="top">
				      <th scope="row" class="titledesc">
				         <label for="yith_woocompare_is_button"><?php esc_html_e('Hide Variations Dropdown', WPPM_Price_Matrix::$plugin_id);?></label>					
				      </th>
				      <td class="forminp forminp-select">
				      	<?php $hide_dropdown = bh_wppm_get('bh_pricematrix_hide_dropdown');?>
				         <select name="bh_pricematrix_hide_dropdown" class="pm-enhanced-select">
				            <option value=""<?php if( empty($hide_dropdown) ) { echo ' selected'; }?>><?php esc_html_e('Yes', WPPM_Price_Matrix::$plugin_id);?></option>
				            <option value="1"<?php if( !empty($hide_dropdown) ) { echo ' selected'; }?>><?php esc_html_e('No', WPPM_Price_Matrix::$plugin_id);?></option>
				         </select>
				      </td>
				   </tr>
				   <tr valign="top">
				      <th scope="row" class="titledesc">
				         <label for="yith_woocompare_is_button"><?php esc_html_e('Quantity Attribute', WPPM_Price_Matrix::$plugin_id);?></label>
				         <span class="woocommerce-help-tip" data-tip="Choose if you want to use a link or a button for the comepare actions."></span>							
				      </th>
				      <td class="forminp forminp-select">
				         <select name="bh_pricematrix_quantity" class="pm-enhanced-select">
				            <option value=""><?php _e( 'None', WPPM_Price_Matrix::$plugin_id );?></option>
				            <?php
				            $attribute_taxonomies = wc_get_attribute_taxonomies();
							if ( ! empty( $attribute_taxonomies ) ) {
								foreach ( $attribute_taxonomies as $tax ) {
									$attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
									$label = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;

									$selected = '';
									if( bh_wppm_get('bh_pricematrix_quantity') == esc_attr( $attribute_taxonomy_name ) ) {
										$selected = ' selected';
									}
									echo '<option value="' . esc_attr( $attribute_taxonomy_name ) . '"'.$selected.'>' . esc_html( $label ) . '</option>';
								}
							}
				            ?>
				         </select>
				      </td>
				   </tr>
				   <tr valign="top">
				      <th scope="row" class="titledesc">
				         <label for="yith_woocompare_is_button"><?php esc_html_e('Display price', WPPM_Price_Matrix::$plugin_id);?></label>						
				      </th>
				      <td class="forminp forminp-select">
				      	<?php $show_price = bh_wppm_get('bh_pricematrix_show_price');?>
				         <select name="bh_pricematrix_show_price" class="pm-enhanced-select">
				            <option value=""<?php if( empty($show_price) ) { echo ' selected'; }?>><?php esc_html_e('Display sale price', WPPM_Price_Matrix::$plugin_id);?></option>
				            <option value="1"<?php if( !empty($show_price) ) { echo ' selected'; }?>><?php esc_html_e('Display regular & sale price', WPPM_Price_Matrix::$plugin_id);?></option>
				         </select>
				      </td>
				   </tr>
				   <tr valign="top" class="">
				      <th scope="row" class="titledesc">
				      	 <label for="yith_woocompare_is_button"><?php esc_html_e('Add to cart behaviour', WPPM_Price_Matrix::$plugin_id);?></label>
				         <span class="woocommerce-help-tip" data-tip="Choose if you want to use a link or a button for the comepare actions."></span>	
				     </th>
				      <td class="forminp forminp-checkbox">
				         <fieldset>
				            <legend class="screen-reader-text"><span>Show button in single product page</span></legend>
				            <label for="bh_pricematrix_addtocart">
				            <input id="bh_pricematrix_addtocart" name="bh_pricematrix_addtocart" type="checkbox" value="1" <?php if ( bh_wppm_get('bh_pricematrix_addtocart') ) echo 'checked'; ?>/> Say if you want to enable click price auto add to cart.</label> 																
				         </fieldset>
				      </td>
				   </tr>
				   <tr valign="top" class="">
				      <th scope="row" class="titledesc">Show tooltips</th>
				      <td class="forminp forminp-checkbox">
				         <fieldset>
				            <legend class="screen-reader-text"><span>Show tooltips when hover price of table</span></legend>
				            <label for="bh_pricematrix_showtooltips">
				            <input id="bh_pricematrix_showtooltips" name="bh_pricematrix_showtooltips" type="checkbox" value="1" <?php if ( bh_wppm_get('bh_pricematrix_showtooltips') ) echo 'checked'; ?>/> Show tooltips when hover price of table</label> 																
				         </fieldset>
				      </td>
				   </tr>
				</table>

				<h3><?php esc_html_e('Table Styles', WPPM_Price_Matrix::$plugin_id);?></h3>
				<table class="form-table">
				   <tr valign="top">
				      <th scope="row" class="titledesc">
				         <label for="yith_woocompare_is_button"><?php esc_html_e('Background color', WPPM_Price_Matrix::$plugin_id);?></label>						
				      </th>
				      <td class="forminp forminp-select">
				         <input type="text" value="<?php echo bh_wppm_get('bh_pricematrix_style_bg');?>" name="bh_pricematrix_style_bg" class="pm-color-field" data-default-color="<?php echo bh_wppm_get('bh_pricematrix_style_bg');?>" />
				      </td>
				   </tr>
				   <tr valign="top">
				      <th scope="row" class="titledesc">
				         <label for="yith_woocompare_is_button"><?php esc_html_e('Border color', WPPM_Price_Matrix::$plugin_id);?></label>						
				      </th>
				      <td class="forminp forminp-select">
				         <input type="text" value="<?php echo bh_wppm_get('bh_pricematrix_style_bordercolor');?>" name="bh_pricematrix_style_bordercolor" class="pm-color-field" data-default-color="<?php echo bh_wppm_get('bh_pricematrix_style_bordercolor');?>" />
				      </td>
				   </tr>

				   <tr valign="top">
				      <th scope="row" class="titledesc">
				         <label for="yith_woocompare_is_button"><?php esc_html_e('Color text', WPPM_Price_Matrix::$plugin_id);?></label>						
				      </th>
				      <td class="forminp forminp-select">
				         <input type="text" value="<?php echo bh_wppm_get('bh_pricematrix_style_textcolor');?>" name="bh_pricematrix_style_textcolor" class="pm-color-field" data-default-color="<?php echo bh_wppm_get('bh_pricematrix_style_textcolor');?>" />
				      </td>
				   </tr>
				   <tr valign="top">
				      <th scope="row" class="titledesc">
				         <label for="yith_woocompare_is_button"><?php esc_html_e('Font size', WPPM_Price_Matrix::$plugin_id);?></label>						
				      </th>
				      <td class="forminp forminp-select">
				      		<input type="number" value="<?php echo bh_wppm_get('bh_pricematrix_style_fontsize');?>" name="bh_pricematrix_style_fontsize" style="width: 50px;" /><span class="pm-px"> px</span>
				      </td>
				   </tr>
				</table>
				<h3><?php esc_html_e('Tooltips Styles', WPPM_Price_Matrix::$plugin_id);?></h3>
				<table class="form-table">
				   <tr valign="top">
				      <th scope="row" class="titledesc">
				         <label for="yith_woocompare_is_button"><?php esc_html_e('Background color', WPPM_Price_Matrix::$plugin_id);?></label>						
				      </th>
				      <td class="forminp forminp-select">
				         <input type="text" value="<?php echo bh_wppm_get('bh_pricematrix_tooltips_bg');?>" name="bh_pricematrix_tooltips_bg" class="pm-color-field" data-default-color="<?php echo bh_wppm_get('bh_pricematrix_tooltips_bg');?>" />
				      </td>
				   </tr>

				   <tr valign="top">
				      <th scope="row" class="titledesc">
				         <label for="yith_woocompare_is_button"><?php esc_html_e('Color text', WPPM_Price_Matrix::$plugin_id);?></label>						
				      </th>
				      <td class="forminp forminp-select">
				         <input type="text" value="<?php echo bh_wppm_get('bh_pricematrix_tooltips_colortext');?>" name="bh_pricematrix_tooltips_colortext" class="pm-color-field" data-default-color="<?php echo bh_wppm_get('bh_pricematrix_tooltips_colortext');?>" />
				      </td>
				   </tr>
				   <tr valign="top">
				      <th scope="row" class="titledesc">
				         <label for="yith_woocompare_is_button"><?php esc_html_e('Border color', WPPM_Price_Matrix::$plugin_id);?></label>						
				      </th>
				      <td class="forminp forminp-select">
				         <input type="text" value="<?php echo bh_wppm_get('bh_pricematrix_tooltips_colorborder');?>" name="bh_pricematrix_tooltips_colorborder" class="pm-color-field" data-default-color="<?php echo bh_wppm_get('bh_pricematrix_tooltips_colorborder');?>" />
				      </td>
				   </tr>
				   <tr valign="top">
				      <th scope="row" class="titledesc">
				         <label for="yith_woocompare_is_button"><?php esc_html_e('Font size', WPPM_Price_Matrix::$plugin_id);?></label>						
				      </th>
				      <td class="forminp forminp-select">
				      		<input type="number" value="<?php echo bh_wppm_get('bh_pricematrix_tooltips_fontsize');?>" name="bh_pricematrix_tooltips_fontsize" style="width: 50px;" /><span class="pm-px"> px</span>
				      </td>
				   </tr>
				</table>
			</div>


			<input style="float: left; margin-right: 10px;" class="button-primary" type="submit" value="Save Changes" name="bh_pricematrix_save">
			<input type="submit" name="pm_reset" class="button-secondary" value="Reset Defaults" onclick="return confirm('If you continue with this action, you will reset all options in this page.\nAre you sure?');">
		</form>
	</div>
</div>