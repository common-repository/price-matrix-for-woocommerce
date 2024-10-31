<ul class="pm-nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#pm-tab-general" class="pm-tab-general"><span>General</span></a></li>
    <li><a data-toggle="tab" href="#pm-tab-order" class="pm-tab-order"><span>Order</span></a></li>
</ul>

<div class="pm-tab-content">
    <div id="pm-tab-general" class="tab-pane fade in active">
        <table class="form-table" id="pm-table-general">
            <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label for="is_price_matrix">Enable</label>
                    </th>
                    <td>
                        <div class="ui toggle checkbox">
                            <input type="checkbox" name="is_price_matrix" id="is_price_matrix" class="hidden"<?php if($this->is_pricematrix) { echo ' checked';}?>>
                            <label></label>
                        </div>
                    </td>
                </tr>

                <tr valign="top" class="tr-logic"<?php if( ! $this->is_pricematrix ) { echo ' style="display: none"';}?>>
                    <th scope="row" colspan="2">
                        <label for="attributes_direction">Attributes</label>
                    </th>
                </tr>


				<tr class="tr-logic"<?php if( ! $this->is_pricematrix ) { echo ' style="display: none"';}?>>
                    <td colspan="2" class="pm-cols">
                        <div id="wppm_wc_variations">
                            <?php if( ! empty($has_attribute) ) {?>
                            <table class="pm-repeater">
                                <thead>
                                <tr>
                                	<th class="one"></th>
                                    <th class="two">Attributes</th>
                                    <th class="three">Direction</th>
                                    <th class="four">Action</th>
                                </tr>
                                </thead>
                                <tbody class="ui-sortable">
                                    <?php
                                    foreach ( pm_sort_data($data_attribute) as $k_attr => $attribute) {
                                        $_attr = $attribute['attribute'];?>
                                    <tr class="wppm_wc_row">
                                        <td class="wppm_wc_drag"><i class="dashicons dashicons-move"></i></td>
                                        <td>
                                            <select name="attributes[]" class="wppm-select wppm-attributes">
                                                <?php
                                                if( isset($lists_attribute[$_attr]) ) { ?>
                                                    <option value="<?php echo $_attr;?>" selected><?php echo $lists_attribute[$_attr];?></option>
                                                <?php }?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="direction[]" class="wppm-select wppm-direction">
                                                <?php foreach (bh_wppm_direction() as $k_direction => $vl_direction) {
                                                    $direction_selected = '';
                                                    if( $k_direction == $attribute['direction'] ) {
                                                        $direction_selected = ' selected';
                                                    }?>
                                                    <option value="<?php echo $k_direction;?>" <?php  echo $direction_selected;?>><?php echo $vl_direction;?></option>
                                                    <?php
                                                }?>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="pm-button pm-remove-row" data-attribute="<?php echo $k_attr;?>"><i class="icon-trash-o"></i></div>
                                        </td>
                                    </tr>
                                    <?php }?>
                                </tbody>
                                <tfoot class="full-width">
                                <tr>
                                    <th colspan="4">
                                        <button type="button" class="pm-with-icon pm-button pm-input-price"><i class="pm-i-icon icon-pencil-square-o"></i> <?php _e('Input Price', 'wc-price-matrix');?></button>
                                        <div class="pm-with-icon pm-button pm-add-attr">
                                            <i class="pm-i-icon icon-plus"></i> Add Row</div>
                                    </th>
                                </tr>
                                </tfoot>
                            </table>
                            <?php }else {
                                echo bh_wppm_notice_attributes();
                            }?>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div id="pm-tab-order" class="tab-pane fade">
        <div id="pm-order-wrapper">
            <?php include WPPM_PATH .'admin/metabox/tpl.order.php';?>
        </div>
    </div>
</div>