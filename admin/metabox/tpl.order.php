<ul>
    <?php
    foreach ($new_attribute as $k_attr => $value) {?>
    <li data-taxonomy="<?php echo $k_attr;?>">
        <div class="pm-order-heading">
            <h3><?php echo $value['label'];?></h3>
            <span class="pm-order-handle" title="Click to toggle" aria-expanded="true"></span>
        </div>

        <div class="pm-order-content pm-clearfix">
            <?php
            foreach ($value['terms'] as $key => $term) {
                if( is_object($term) ) {?>
                <div class="pm-order-col" data-id="<?php echo $term->slug;?>">
                    <div class="pm-order-item">
                        <?php echo $term->name;?>
                    </div>
                </div>
                <?php }else { ?>    
                <div class="pm-order-col" data-id="<?php echo $term['slug'];?>">
                    <div class="pm-order-item">
                        <?php echo $term['name'];?>
                    </div>
                </div>
                <?php }
            }?>
        </div>
    </li>
    <?php }?>
</ul>