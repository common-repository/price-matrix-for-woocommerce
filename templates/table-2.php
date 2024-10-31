<div class="table-responsive is-layout-<?php echo $is_admin;?>" data-layout="2">
    <table class="wppm-enter-table has-two-attribute">
        <tbody>
            <tr>
                <td class="attribute-name"></td>
                <?php foreach ($vertical_array as $kat => $attr_one) :?>
                <td class="attribute-name center"><?php echo $attr_one->name;?></td>
                <?php endforeach;?>
            </tr>
            <?php foreach ($horizontal_array as $kats => $attr_two) :?>
            <tr>
                <td class="attribute-name"><?php echo $attr_two->name;?></td>
                <?php foreach ($vertical_array as $kat => $attr_one) :
                    $new_attribute = array(
                        'attribute_'.$horizontal[0] => $attr_two->slug,
                        'attribute_'.$vertical[0] => $attr_one->slug
                    );
                    if( $is_admin ) {
                        $price = bh_wppm_price_input($new_attribute, $product_id);
                    }else {
                        $price = bh_wppm_price_input($new_attribute, $product_id, true);
                    }

                    /* Show Tooltips */
                    $table_tooltips = '';
                    if( ! empty($show_tooltips) ) {
                        $table_tooltips .= '<table><tr><td>'. pm_attribute_tax($attr_one->taxonomy, $product_id) .'</td><td>'.$attr_one->name.'</td></tr>';
                        $table_tooltips .= '<tr><td>'. pm_attribute_tax($attr_two->taxonomy, $product_id) .'</td><td>'.$attr_two->name.'</td></tr></table>';
                    }

                    ?>
                    <td class="attribute-price pm-tippy<?php echo empty($show_tooltips) ? '' : esc_attr($show_tooltips);?>" title="<?php echo $table_tooltips;?>" data-attributes="<?php echo htmlspecialchars( wp_json_encode( $new_attribute ) ) ?>">
                        <?php
                        if( $is_admin ) {?>
                        <input type="hidden" name="attribute[0]" value="<?php echo $horizontal[0];?>" />
                        <input type="hidden" name="attribute[1]" value="<?php echo $vertical[0];?>" />
                        <input type="text" name="price[<?php echo htmlspecialchars($attr_two->slug);?>][<?php echo htmlspecialchars($attr_one->slug);?>]" class="form-control" value="<?php echo $price;?>">
                        <?php
                        }else {
                            echo $price;
                        }?>
                    </td>
                <?php endforeach;?>
           	</tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>