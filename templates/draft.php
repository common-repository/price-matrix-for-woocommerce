
            <?php
            mang($horizontal_two_array);
            end($vertical_one_array);
            $last_one = key($vertical_one_array);
            foreach ($horizontal_one_array as $k_one => $attr_one) :
            if($k_one == 0):
                echo '<tr>';
            endif;?>
                <?php foreach ($horizontal_two_array as $k_two => $attr_two) :?>
                <td class="attribute-name center"><?php echo $attr_two->name;?></td>
                <?php endforeach;?>
            <?php
            if($k_one == $last_one):
                echo '</tr>';
            endif;
            endforeach;?>

            <?php foreach ($vertical_one_array as $k_three => $attr_three) :?>
            <tr>
                <td class="attribute-name attribute-first"><?php echo $attr_three->name;?></td>
                <?php
                foreach ($horizontal_one_array as $kat => $attr_one) :
                    foreach ($horizontal_two_array as $k_two => $attr_two) :
                    $new_attribute = array(
                        'attribute_'.$horizontal_one => $attr_one->slug,
                        'attribute_'.$horizontal_two => $attr_two->slug,
                        'attribute_'.$vertical_one => $attr_three->slug
                    );

                    if( $is_admin ) {
                        $price = bh_wppm_price_input($new_attribute, $product_id);
                    }else {
                        $price = bh_wppm_price_input($new_attribute, $product_id, true);
                    }

                    /* Show Tooltips */
                    $table_tooltips = '';
                    if( $this->show_tooltips ) {
                        $table_tooltips .= '<table><tr><td>sss</td><td>'.$attr_one->name.'</td></tr>';
                        $table_tooltips .= '<tr><td>bbbb</td><td>'.$attr_two->name.'</td></tr></table>';
                    }
                    ?>
                    <td class="attribute-price pm-tippy<?php echo esc_attr($this->show_tooltips);?>" title="<?php echo $table_tooltips;?>" data-attributes="<?php echo htmlspecialchars( wp_json_encode( $new_attribute ) ) ?>">
                        <?php
                        if( $is_admin ) {?>
                            <input type="hidden" name="attribute[0]" value="<?php echo $horizontal_one;?>" />
                            <input type="hidden" name="attribute[1]" value="<?php echo $horizontal_two;?>" />
                            <input type="hidden" name="attribute[2]" value="<?php echo $vertical_one;?>" />
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