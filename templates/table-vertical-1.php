<div class="table-responsive is-layout-<?php echo $is_admin;?>" data-layout="1">
    <table class="wppm-enter-table has-one-attribute one-vertical-attribute">
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
        <tr>
            <td class="attribute-name center"><?php echo $attr_one->name;?></td>
            <td class="attribute-price pm-tippy<?php echo esc_attr($this->show_tooltips);?>" title="<?php echo $table_tooltips;?>" data-attributes="<?php echo htmlspecialchars( wp_json_encode( $new_attribute ) ) ?>">
                <?php
                if( $is_admin ) {?>
                <input type="hidden" name="attribute[0]" value="<?php echo empty($vertical[0]) ? $attr_one->taxonomy : $vertical[0];?>" />
                <input type="text" name="price[<?php echo $attr_one->slug;?>]" class="form-control" value="<?php echo $price;?>">
                <?php
                }else {
                    echo $price;
                }?>
            </td>
        </tr>
        <?php endforeach;?>
    </table>
</div>