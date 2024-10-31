<?php
class WPPM_Admin_List_Table {

	/**
	 * Holds the global `$pagenow` variable's value.
	 *
	 * @var string
	 */
	private $pagenow;

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $list_table_type = 'product';

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->pagenow = $GLOBALS['pagenow'];
		add_filter( 'views_edit-product', array($this, 'add_subsub_link'), 40 );
		add_filter( 'query_vars', array( $this, 'add_custom_query_var' ) );
		add_filter( 'parse_query', array($this, 'query_filters') );
	}

	public function add_custom_query_var( $public_query_vars ) {
		$public_query_vars[] = 'filter';
		return $public_query_vars;
	}

	public function add_subsub_link( $views ) {
		global $wp_query, $wpdb;
		
		if( ( is_admin() ) && current_user_can( 'edit_others_pages' ) ) {

			$total = $wpdb->get_var($wpdb->prepare( 
				"SELECT COUNT(*) AS count FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", 
				'_is_price_matrix',
				true
			));

			$class            = ( isset( $wp_query->query['filter'] ) && 'price_matrix' === $wp_query->query['filter'] ) ? 'current' : '';
			$query_string     = remove_query_arg( array( 'orderby', 'order' ) );
			$query_string     = add_query_arg( 'filter', rawurlencode( 'price_matrix' ), $query_string );


			$views['price_matrix'] = '<a href="' . esc_url( $query_string ) . '" class="' . esc_attr( $class ) . '">' . __( 'Has Price Matrix', WPPM_Price_Matrix::$plugin_id ) . ' <span class="count">(' . $total .')</span></a>';
		}

		return $views;
	}

	/**
	 * Handle any custom filters.
	 *
	 * @param array $query_vars Query vars.
	 * @return array
	 */
	public function query_filters( $query ) {
		global $typenow;
		
        if ( 'product' == $typenow ) {
            if ( ! empty($_GET['filter']) && $_GET['filter'] == 'price_matrix' ) {
                $query->query_vars['meta_key']	 = '_is_price_matrix';
                $query->query_vars['meta_value'] = true;
            }    
		}
	}
}

new WPPM_Admin_List_Table();