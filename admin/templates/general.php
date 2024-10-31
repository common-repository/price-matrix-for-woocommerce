<table class="pm-repeater">
    <thead>
        <tr>
            <th class="one"></th>
            <th class="two"><?php esc_html_e( 'Attributes', 'woocommerce' ); ?></th>
            <th class="three"><?php esc_html_e( 'Direction', 'wc-price-matrix' ); ?></th>
            <th class="four"><?php esc_html_e( 'Action', 'wc-price-matrix' ); ?></th>
        </tr>
    </thead>
    <tbody class="pm-repeater-body ui-sortable">
    <?php echo $html;?>
    </tbody>
    <tfoot class="full-width">
        <tr>
            <th colspan="4">
                <button type="button" class="pm-with-icon pm-button pm-input-price"><i class="pm-i-icon icon-pencil-square-o"></i> <?php esc_html_e( 'Input Price', 'wc-price-matrix' ); ?></button>
                <div class="pm-with-icon pm-button pm-add-attr"><i class="pm-i-icon icon-plus"></i> <?php esc_html_e( 'Add Row', 'wc-price-matrix' ); ?></div>
            </th>
        </tr>
    </tfoot>
</table>