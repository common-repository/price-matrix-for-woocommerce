<?php
class WPPM_Admin {

	/**
	 * Holds the global `$pagenow` variable's value.
	 *
	 * @var string
	 */
	private $pagenow;

	private $is_editor;

	public $_pm_notice_admin;

	static $postname_allowed = array(
    	'bh_pricematrix_position' => 'woocommerce_before_single_variation',
    	'bh_pricematrix_addtocart' => false,
    	'bh_pricematrix_hide_dropdown' => false,
    	'bh_pricematrix_quantity' => false,
		'bh_pricematrix_style_bg' => '#fafafa',
		'bh_pricematrix_style_bordercolor' => '#eee',
    	'bh_pricematrix_style_textcolor' => '#3C4858',
		'bh_pricematrix_style_fontsize' => 14,
		'bh_pricematrix_style_headingcolor' => '#fafafa',
    	'bh_pricematrix_style_test' => 5,
    	'bh_pricematrix_showtooltips' => false,
    	'bh_pricematrix_tooltips_bg' => '#fafafa',
    	'bh_pricematrix_tooltips_colortext' => '#3C4858',
    	'bh_pricematrix_tooltips_colorborder' => '#eee',
    	'bh_pricematrix_tooltips_fontsize' => 13,
    	'bh_pricematrix_show_price' => false
    );

	/**
	 * Class constructor
	 */
	public function __construct() {
		$this->pagenow = $GLOBALS['pagenow'];

		$this->is_editor = $this->pagenow === 'post.php' || $this->pagenow === 'post-new.php' || $this->pagenow === 'edit.php';

		$this->load_meta_boxes();
		$this->hooks();
		$this->settings_page();

		add_filter( 'admin_body_class', array($this, 'add_bodyclass_settings_page'), 999, 1 );
		add_filter( 'plugin_action_links_' . WPPM_PLUGIN, array( $this, 'add_settings_link' ) );
	}

	public function add_settings_link($links) {
        $settings_link = '<a href="'. admin_url( 'admin.php?page=bh-wppm' ) .'">' . __( 'Settings' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
    }

	public function add_bodyclass_settings_page( $classes ) {
		$screen = get_current_screen();

		if( $screen->id == 'bh-plugins_page_bh-wppm') {
			$classes .= ' woocommerce';
		}
		return $classes;
	}

	public function settings_page() {
        if( class_exists('BH_Plugins') ) {
            add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
        }
	}

    public function register_panel() {
        $args = array(
            'create_menu_page' => true,
            'parent_slug'   => '',
            'page_title'    => __( 'Price Matrix', WPPM_Price_Matrix::$plugin_id ),
            'menu_title'    => __( 'Price Matrix', WPPM_Price_Matrix::$plugin_id ),
            'capability'    => apply_filters( 'bh_settings_panel_capability', 'manage_options' ),
            'parent'        => '',
            'parent_page'   => 'bh_plugin_panel',
            'page'          => WPPM_Price_Matrix::$settings_page,
            'functions'     => array($this , 'bh_pm_settings_page')
        );

        $this->_panel = new BH_Plugins($args);
    }

    public function bh_pm_settings_page() {

    	if( isset($_POST['bh_pricematrix_save']) && is_array(self::$postname_allowed) ) {
    		$save_data = array();
    		foreach (self::$postname_allowed as $name => $value) {
    			if( isset($_POST[$name]) ) {
    				$value = $_POST[$name];
    			}

    			$save_data[$name] = $value;
			}

    		update_option( WPPM_Price_Matrix::$plugin_id .'_fields', $save_data );

    		delete_transient( WPPM_Price_Matrix::$plugin_id .'_fields' );

 			$get_data = $save_data;
    	}else {
    		$get_data = get_option( WPPM_Price_Matrix::$plugin_id .'_fields' );

    		foreach (self::$postname_allowed as $name => $val) {
    			if( !isset($get_data[$name]) ) {
    				$get_data[$name] = $val;
    			}
    		}
    		
    	}

    	/* Reset Default */
    	if( isset($_POST['pm_reset']) ) {
    		update_option( WPPM_Price_Matrix::$plugin_id .'_fields', self::$postname_allowed );
    		delete_transient( WPPM_Price_Matrix::$plugin_id .'_fields' );
    	}

    	
    	require_once( WPPM_PATH . 'admin/tpl.settings.php' );
    }

	/**
	 * Determine whether we should load the meta box class and if so, load it.
	 */
	private function load_meta_boxes() {
		
		$is_inline_save = filter_input( INPUT_POST, 'action' ) === 'inline-save';

		if( $this->is_editor || $is_inline_save ) {
			require_once( WPPM_PATH . 'admin/metabox/class.metabox.php' );
			$GLOBALS['wppm_metabox'] = new WPPM_Metabox();
		}
	}

	private function hooks() {
		add_action( 'admin_enqueue_scripts', array( &$this, 'product_admin_enqueue_script' ), 11 );
		add_action('save_post_product', array( &$this, 'save_product'), 10, 3);
		if( $this->is_editor ) {
			add_action('admin_footer', array( &$this, 'add_popup_html') );
		}

	}

	public function save_product() {
		global $wpdb;

		$total = $wpdb->get_var($wpdb->prepare( 
			"SELECT COUNT(*) AS count FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", 
			'_is_price_matrix',
			true
		));

		update_option('bh_wppm_limit', $total);
	}

	public function add_popup_html() {
		?>
		<div id="wppm-popup" class="white-popup mfp-hide">
			<h2 class="wppm-heading">Input Price</h2>
			<div class="wppm-popup-wrapper">
				<form action="" method="POST" id="frm-wppm-popup">
					<div class="wppm-notice">
						<p>To input the sale price, please use the "-" characters between prices. Eg: original price is $50, sale price is $45, the convention is 50-45</p>
					</div>
					<div class="wppm-popup-container"></div>
					<button type="submit" name="save_price" class="wppm-popup-save">Save Price <span class="ld ld-ring ld-spin"></span></button>
				</form>
			</div>
		</div>
		<?php
	}
	
	public function product_admin_enqueue_script() {
		global $post_type, $post, $wpdb;

		if( $this->is_editor && $post_type == 'product' || isset($_GET['page']) && $_GET['page'] == 'bh-wppm') {


			$enable_lists = $wpdb->get_results($wpdb->prepare( 
				"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", 
				'_is_price_matrix',
				true
			));

			$enable_product = array();
			if( $enable_lists ) {
				foreach( $enable_lists as $k => $enable) {
					$enable_product[] = $enable->post_id;
				}
			}



			if(isset($_GET['page']) && $_GET['page'] == 'bh-wppm') {
				wp_enqueue_style( 'woocommerce_admin_styles' );
				wp_enqueue_style( 'wp-color-picker' );

				wp_enqueue_script( 'select2' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'jquery-tiptip' );		
			}

			

			
			wp_enqueue_style("sweetalert2", WPPM_URL . "/admin/assets/css/sweetalert2.min.css", array(), '1.0', false );
			wp_enqueue_script("jquery.sweetalert2", WPPM_URL . "/admin/assets/js/sweetalert2.min.js", array(), '1.0', false );
			
			

			wp_enqueue_style("magnific-popup", WPPM_URL . "/admin/assets/css/magnific-popup.css", array(), '1.0', false );
			wp_enqueue_style("wppm-admin", WPPM_URL . "/admin/assets/css/style.css", array(), '1.0', false );
			wp_enqueue_script("jquery.magnific-popup", WPPM_URL . "/admin/assets/js/jquery.magnific-popup.min.js", array(), '1.0', false );
			wp_enqueue_script("wppm-js", WPPM_URL . "/admin/assets/js/admin.js", array( 'wp-color-picker' ), '1.0', false );

			$post_id = empty($post) ? 0 : $post->ID;
			wp_localize_script( 'wppm-js', 'wppm', array(
				'apiSettings' => esc_url_raw( rest_url( 'wppm/v1' ) ),
				'ajax_url' => admin_url('/admin-ajax.php'),
				'wpPriceMatrix' => array(
					'post_id' => $post_id,
					'enable' => get_post_meta($post_id, '_is_price_matrix', true)
				),
				'wpLabel' => array(
					'error_title' => __( 'Error!', WPPM_Price_Matrix::$plugin_id ),
					'success_title' => __( 'Successfully!', WPPM_Price_Matrix::$plugin_id ),
					'show_if_variable' => __( 'Price Matrix only available with Variable product.', WPPM_Price_Matrix::$plugin_id ),
					'add_attributes' => __( 'Please add attributes or check save attributes!', WPPM_Price_Matrix::$plugin_id ),
					'max_add_row'	=> __('The maximum number of rows has been exceeded, please add a new attribute!', WPPM_Price_Matrix::$plugin_id),
					'min_remove_row' => __("You cannot remove this row; at least one attribute must remain.", WPPM_Price_Matrix::$plugin_id),
					'limit' => __('Please upgrade to the PRO version to use this feature unlimited!', WPPM_Price_Matrix::$plugin_id ),
				),
				'limit' => bh_wppm_limit(),
				'enable_product' => $enable_product
			));
		}
		
	}
}