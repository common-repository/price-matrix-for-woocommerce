<div class="table-responsive is-layout-<?php echo $is_admin;?>" data-layout="3">
    <table class="wppm-enter-table has-three-attribute three-horizontal-attribute">
        <tbody>
            <tr>
                <td class="attribute-name attribute-first" rowspan="2"></td>
                <?php foreach ($horizontal_one_array as $attr_one) :?>
                <td class="attribute-name second center" colspan="<?php echo count($horizontal_two_array);?>"><?php echo $attr_one->name;?></td>
                <?php endforeach;?>
            </tr>

            <tr>
                <?php
                foreach ($horizontal_one_array as $k_one => $attr_one) :
                    foreach ($horizontal_two_array as $k_two => $attr_two) :?>
                    <td class="attribute-name center"><?php echo $attr_two->name;?></td>
                <?php endforeach;
                endforeach;?>
            </tr>

            <?php foreach ($vertical_one_array as $k_three => $attr_three) :?>
            <tr>
                <td class="attribute-name attribute-first"><?php echo $attr_three->name;?></td>
                <?php
                foreach ($horizontal_one_array as $kat => $attr_one) :
                    foreach ($horizontal_two_array as $k_two => $attr_two) :
                    $new_attribute = array(
                        'attribute_'.$horizontal[0] => $attr_one->slug,
                        'attribute_'.$horizontal[1] => $attr_two->slug,
                        'attribute_'.$vertical[0] => $attr_three->slug
                    );

                    if( $is_admin ) {
                        $price = bh_wppm_price_input($new_attribute, $product_id);
                    }else {
                        $price = bh_wppm_price_input($new_attribute, $product_id, true);
                    }

                    /* Show Tooltips */
                    $table_tooltips = '';
                    if( $this->show_tooltips ) {
                        $table_tooltips .= '<table><tr><td>'. pm_attribute_tax($attr_one->taxonomy, $product_id) .'</td><td>'.$attr_one->name.'</td></tr>';
                        $table_tooltips .= '<tr><td>'. pm_attribute_tax($attr_two->taxonomy, $product_id) .'</td><td>'.$attr_two->name.'</td></tr>';

                        $table_tooltips .= '<tr><td>'. pm_attribute_tax($attr_three->taxonomy, $product_id) .'</td><td>'.$attr_three->name.'</td></tr></table>';
                    }
                    ?>
                    <td class="attribute-price pm-tippy<?php echo esc_attr($this->show_tooltips);?>" title="<?php echo $table_tooltips;?>" data-attributes="<?php echo htmlspecialchars( wp_json_encode( $new_attribute ) ) ?>">
                        <?php
                        if( $is_admin ) {?>
                            <input type="hidden" name="attribute[0]" value="<?php echo $horizontal[0];?>" />
                            <input type="hidden" name="attribute[1]" value="<?php echo $horizontal[1];?>" />
                            <input type="hidden" name="attribute[2]" value="<?php echo $vertical[0];?>" />
                            <input type="text" name="price[<?php echo $attr_one->slug;?>][<?php echo $attr_two->slug;?>][<?php echo $attr_three->slug;?>]" class="form-control" value="<?php echo $price;?>">
                        <?php
                        }else {
                            echo $price;
                        }?>
                    </td>
                <?php endforeach;
                endforeach;?>
            </tr>
            <?php endforeach;?>

        </tbody>
    </table>
</div>