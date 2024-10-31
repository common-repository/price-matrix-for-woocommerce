<div class="table-responsive is-layout-<?php echo $is_admin;?>" data-layout="1">
    <table class="wppm-enter-table has-one-attribute one-horizontal-attribute">
    <tr>
        <?php foreach ($data_array as $kat => $attr_one) :?>
        <td class="attribute-name center"><?php echo $attr_one->name;?></td>
        <?php endforeach;?>
    </tr>
    <tr>
        <?php foreach ($data_array as $kat => $attr_one) :
            $new_attribute = array(
                'attribute_'.$attr_one->taxonomy => $attr_one->slug
            );


            if( $is_admin ) {
                $price = bh_wppm_price_input($new_attribute, $product_id);
            }else {
                $price = bh_wppm_price_input($new_attribute, $product_id, true);
            }

            /* Show Tooltips */
            $table_tooltips = '';
            if( $this->show_tooltips ) {
                $table_tooltips .= '<table><tr><td>'. pm_attribute_tax($attr_one->taxonomy, $product_id) .'</td><td>'.$attr_one->name.'</td></tr></table>';
            }
            ?>
                <td class="attribute-price pm-tippy<?php echo esc_attr($this->show_tooltips);?>" title="<?php echo $table_tooltips;?>" data-attributes="<?php echo htmlspecialchars( wp_json_encode( $new_attribute ) ) ?>">
                    <?php
                    if( $is_admin ) {?>
                    <input type="hidden" name="attribute[0]" value="<?php echo $horizontal[0];?>" />
                    <input type="text" name="price[<?php echo $attr_one->slug;?>]" class="form-control" value="<?php echo $price;?>">
                    <?php
                    }else {
                        echo $price;
                    }?>
                </td>
        <?php endforeach;?>
    </tr>
    </table>
</div>