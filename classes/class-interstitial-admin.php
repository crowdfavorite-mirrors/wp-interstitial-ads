<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Uji_Interst_Admin extends Uji_Interst_Admin_API{
	var $version;
	private $file;
	public static $plugin_url;
	public static $plugin_path;


	/**
	 * __construct function.
	 *
	 */
	public function __construct ( $file ) {
		parent::__construct(); // Required in extended classes.

		$this->token = 'ujinter';
		$this->page_slug = 'ujiinter-api';
		$this->opt_name = __( 'Interstitial Ads Options', 'ujinter' );

		$this->post_meta = 'interads_meta';

		self::$plugin_url = trailingslashit( plugins_url( '', $plugin = $file ) );
		self::$plugin_path = trailingslashit( dirname( $file ) );

		$this->labels = array();
		$this->setup_post_type_labels_base();

		//Add Post Type
		add_action( 'init', array( &$this, 'add_post_type_ads' ), 100 );

		//Add Columns
		add_filter( 'manage_edit-interads_columns', array( &$this, 'add_column_headings' ), 10, 1 );
		add_action( 'manage_posts_custom_column', array( &$this, 'add_column_data' ), 10, 2 );

		//Change Ad Title Here
		add_filter( 'enter_title_here', array( &$this, 'change_default_title' ) );

		//add admin .css
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_styles_interads' ) );
		//add admin .js
		add_action( 'admin_print_scripts', array( &$this, 'admin_enqueue_scripts' ) );

		//add menu
		add_action( 'admin_menu', array( &$this, 'ujinter_menu' ) );

		//Metaboxes
		add_action( 'add_meta_boxes', array( &$this, 'interads_meta_boxes' ) );

		//AdminInit::Save Post
		add_action( 'save_post', array( &$this, 'interads_save') );

		add_action( 'admin_init', array( &$this, 'remove_add' ) );

	}


	/**
	 * Setup the singular, plural and menu label names for the post types.
	 * @since  1.0.0
	 * @return void
	 */
	private function setup_post_type_labels_base () {
		$this->labels = array( 'interads' => array() );

		$this->labels['interads'] = array( 'singular' => __( 'Ad', 'ujinter' ), 'plural' => __( 'Ads', 'ujinter' ), 'menu' => __( 'Interstitial Ads', 'ujinter' ) );
	} // End setup_post_type_labels_base()



	/**
	 * Setup the "Interstitial Ads" post type
	 * @since  1.0.0
	 * @return void
	 */
	public function add_post_type_ads () {
		$args = array(
		    'labels' => $this->create_post_type_labels( 'interads', $this->labels['interads']['singular'], $this->labels['interads']['plural'], $this->labels['interads']['menu'] ),
		    'public' => false,
		    'publicly_queryable' => true,
		    'show_ui' => true,
		    'show_in_menu' => true,
		    'query_var' => true,
		    'rewrite' => array( 'slug' => 'interads', 'with_front' => false, 'feeds' => false, 'pages' => false ),
		    'capability_type' => 'post',
		    'has_archive' => false,
		    'hierarchical' => false,
		    'menu_position' => 100, // Below "Pages"
		    'supports' => array( 'title' )
		);

		register_post_type( 'interads', $args );
	} // End setup_zodiac_post_type()


	/**
	 * Add column headings to the "slides" post list screen.
	 * @access public
	 * @since  1.0.0
	 */
	public function add_column_headings ( $defaults ) {
		$new_columns['cb'] = '<input type="checkbox" />';
		// $new_columns['id'] = __( 'ID' );
		$new_columns['title'] = _x( 'Ads Title', 'column name', 'ujinter' );
		$new_columns['valability'] = _x( 'Valability', 'column name', 'ujinter' );
		$new_columns['impress'] = _x( 'Impressions', 'column name', 'ujinter' );

		if ( isset( $defaults['date'] ) ) {
			$new_columns['date'] = $defaults['date'];
		}

		return $new_columns;
	} // End add_column_headings()

	/**
	 * Add data for our newly-added custom columns.
	 * @access public
	 * @since  1.0.0
	 */
	public function add_column_data ( $column_name, $id ) {
		global $wpdb, $post;

		switch ( $column_name ) {
			case 'id':
				echo $id;
			break;

			case 'valability':
				$value = __( 'Unlimited', 'ujizodiac' );
				$val_from = get_post_meta( $post->ID, '_datapick1', true );
				$val_to = get_post_meta( $post->ID, '_datapick2', true );

				if ( $val_from || $val_to ) {
					echo ( $val_from ) ? $val_from : '';
					echo ( $val_to) ? (($val_from) ? ' - ' . $val_to : $val_to) : '';
				}else{
					echo $value;
				}
			break;

			case 'impress':
				$num = get_post_meta( $id, 'ads_impressions', true );
				echo ( !empty($num) ) ? $num : 0;
			break;

			default:
			break;
		}
	} // End add_column_data()

	/**
	 * Labels for post type
	 * @since  1.0.0
	 * @return void
	 */
	private function create_post_type_labels ( $token, $singular, $plural, $menu ) {
		$labels = array(
		    'name' => sprintf( _x( '%s', 'post type general name', 'ujinter' ), $plural ),
		    'singular_name' => sprintf( _x( '%s', 'post type singular name', 'ujinter' ), $singular ),
		    'add_new' => sprintf( _x( 'Add New %s', $token, 'ujinter' ), $singular ),
		    'add_new_item' => sprintf( __( 'Add New %s', 'ujinter' ), $singular ),
		    'edit_item' => sprintf( __( 'Edit %s', 'ujinter' ), $singular ),
		    'new_item' => sprintf( __( 'New %s', 'ujinter' ), $singular ),
		    'all_items' => sprintf( __( 'All %s', 'ujinter' ), $plural ),
		    'view_item' => sprintf( __( 'View %s', 'ujinter' ), $singular ),
		    'search_items' => sprintf( __( 'Search %s', 'ujinter' ), $plural ),
		    'not_found' =>  sprintf( __( 'No %s found', 'ujinter' ), strtolower( $plural ) ),
		    'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'ujinter' ), strtolower( $plural ) ),
		    'parent_item_colon' => '',
		    'menu_name' => $menu
		  );

		return $labels;
	} // End create_post_type_labels()


	/**
	 * Load the global admin styles for the menu icon and the relevant page icon.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_styles_interads () {
		$screen = get_current_screen();

		if (in_array( $screen->id, array( 'interads', 'interads_page_ujiinter-api' ))) :

		wp_register_style( 'admin-interads', self::$plugin_url . 'css/admin.css', '', '1.0', 'screen' );
		wp_register_style( 'bootstrap', self::$plugin_url . 'assets/bootsrap/css/bootstrap.css', '', '2.0', 'screen' );
		wp_register_style( 'colorpicker', self::$plugin_url . 'assets/colorpicker/css/colorpicker.css', '', '1.0', 'screen' );
		wp_register_style( 'datapicker', self::$plugin_url . 'assets/datepicker/css/datepicker.css', '', '1.0', 'screen' );
		wp_enqueue_style(  'admin-interads' );
		wp_enqueue_style(  'bootstrap' );
		wp_enqueue_style(  'colorpicker' );
		wp_enqueue_style(  'datapicker' );

		endif;
	} // End admin_styles_global()

		/**
	 * enqueue_scripts function.
	 *
	 * @description Load in JavaScripts where necessary.
	 */
	public function admin_enqueue_scripts () {

		$screen = get_current_screen();


		if (in_array( $screen->id, array( 'interads', 'interads_page_ujiinter-api' ))) :

		wp_enqueue_script( 'bootstrap', self::$plugin_url . 'assets/bootsrap/js/bootstrap.min.js', array( 'jquery' ), '2.0' );
		wp_enqueue_script( 'bootstrap-color', self::$plugin_url . 'assets/colorpicker/js/bootstrap-colorpicker.js', array( 'jquery' ), '1.0' );
		wp_enqueue_script( 'interads', self::$plugin_url . 'js/admin-interads.js', array( 'jquery' ), '1.0' );

		endif;

	} // End enqueue_scripts()

	/**
	 * Change Title
	 * @since  1.0
	 */
	public function change_default_title( $title ){
		 $screen = get_current_screen();
		 if  ( 'interads' == $screen->post_type ) {
			  $title = 'Enter Ad Title Here';
		 }

		 return $title;
	}

	/**
	 * Remove it if already exist
	 * @since  1.0
	 */
	public function remove_add( ){
		// $screen = get_current_screen();
		// if  ( 'interads' == $screen->post_type ) {
			$published_posts = wp_count_posts( 'interads' );

			if( (int) $published_posts->publish > 0 ){
				remove_submenu_page( 'edit.php?post_type=interads', 'post-new.php?post_type=interads' );
				add_action('admin_footer', array( &$this, 'add_footer_css' ) );
			}
		 //}
	}

	/**
	 * Add footer CSS
	 * @since  1.0
	 */
	public function add_footer_css( ){
		echo '<style type="text/css">
					#favorite-actions {display:none;}
					.add-new-h2{display:none;}
					.tablenav{display:none;}
					#wp-admin-bar-new-interads {display:none;}
					</style>';
	}


	/**
	 * Add menu
	 * @since  1.0
	 */
	public function ujinter_menu() {
		$hook = add_submenu_page( 'edit.php?post_type=interads', __( 'Interstitial Ads', 'ujinter' ), __('Ad Options', 'ujinter'), 'manage_options', $this->page_slug, array( &$this, 'settings_screen' ) );
		if ( isset( $_GET['page'] ) && ( $_GET['page'] == $this->page_slug ) ) {
			add_action( 'admin_notices', array( &$this, 'settings_errors' ) );
			/*add_action( 'admin_print_scripts', array( &$this, 'enqueue_scripts' ) );
			add_action( 'admin_print_styles', array( &$this, 'enqueue_styles' ) );*/
		}
		// Ad remove.
  		/*add_action('load-edit.php', array( &$this, 'remove_add' ) );
		add_action('load-post.php', array( &$this, 'remove_add' ) );
		add_action('load-post-new.php', array( &$this, 'remove_add' ) );
		add_action('load-'.$hook, array( &$this, 'remove_add' ) );*/
	}

	/**
	 * Add metaboxes
	 * @since  1.0
	 */
	public function interads_meta_boxes() {
	global $post;


		// Excerpt
		if ( function_exists('wp_editor') ) {
			remove_meta_box( 'postexcerpt', 'product', 'normal' );
			add_meta_box( 'postexcerpt', __('Ads Content', 'ujinter'), array( &$this, 'interads_html' ), 'interads', 'normal' );
		}

		add_meta_box( 'postwhere', __('Where to show', 'ujinter'), array( &$this, 'interads_where' ), 'interads', 'normal' );
		add_meta_box( 'getpro', __('Interstitial Ads Premium', 'ujinter'), array( &$this, 'interads_prover' ), 'interads', 'side' );


	}

	/**
	 * Add HTML metaboxes
	 * @since  1.0
	 */
	public function interads_html( $post ) {
		?>
		<ul class="nav nav-tabs" id="cont_tab">
				<li><a href="#int-tab-1" data-toggle="tab"><?php _e("Text/Html", 'ujinter') ?></a></li>
				<li><a href="#int-tab-4" data-toggle="tab"><?php _e("Settings", 'ujinter') ?></a></li>
			  </ul>
	   	 <div class="tab-content">
		 <?php
		//TAB1: add editor
		$include = get_post_meta( $post->ID, 'include_html', true );
		?>
		<div class="tab-pane" id="int-tab-1">
				<div class="options_group" style="margin-bottom:8px;">
				<p class="form-field">
					<label for="include_html"><?php _e("Included as Ad", 'ujinter') ?></label>
					<input id="include_html" class="checkbox" type="checkbox" value="yes" name="include_html" <?php checked( $include, 'yes' ) ?>>
				</p>
		</div>
		<?php
		$settings = array(
				'quicktags' 	=> array( 'buttons' => 'em,strong,link' ),
				'textarea_name'	=> 'excerpt',
				'quicktags' 	=> true,
				'tinymce' 		=> true,
				'editor_css'	=> '<style>#wp-excerpt-editor-container .wp-editor-area{height:275px; width:100%;}</style>'
				);

		wp_editor( htmlspecialchars_decode( $post->post_excerpt ), 'excerpt', $settings );

		echo '</div>';

		//TAB2: add links
		$include = get_post_meta( $post->ID, 'include_url', true );
		?>
		<div class="tab-pane" id="int-tab-2">
			   <div class="options_group">
				<p class="form-field">
					<label for="include_url"><?php _e("Included as Ads", 'ujinter') ?></label>
					<input id="include_url" class="checkbox" type="checkbox" value="yes" name="include_url" <?php checked( $include, 'yes' ) ?>>
				</p>
			   </div>
			<div class="options_group">
		<?php
		for($x=1; $x<=5; $x++){
			$include = get_post_meta( $post->ID, 'ads_link'.$x, true );
			echo'<p class="form-field">
					<label for="ads_link">'. __("Add Link URL", 'ujinter') .$x.'</label>
					<input type="text" name="ads_link'.$x.'" class="medium" id="ads_link'.$x.'" value="'.$include.'" />
				</p>';

			}

		echo  '</div>
			  </div>';
		//TAB3: post/pages
		?>
		<div class="tab-pane" id="int-tab-3">
					<div class="options_group">
					<p class="form-field">
						<label for="ads_link"><?php _e("Selected Posts/Pages", 'ujinter') ?></label>
						<input type="text" name="add_posts" class="short" id="add_posts" value="<?php echo get_post_meta( $post->ID, 'add_posts', true ); ?>" />
						<span class="description"><?php _e("Add any pages or posts id separated by commas. ex: 312, 16, 27", 'ujinter') ?></span>
					</p>
				   </div>
			  </div>
		<?php
		//TAB4: settings
		$include = get_post_meta( $post->ID, 'show_title', true );
		$include_one = get_post_meta( $post->ID, 'post_random', true );
		$include_two = get_post_meta( $post->ID, 'post_once', true );
		?>
		<div class="tab-pane" id="int-tab-4">
					<div class="options_group">
					<p class="form-field">
						<label for="show_title"><?php _e("Show title", 'ujinter') ?></label>
						<input id="show_title" class="checkbox" type="checkbox" value="yes" name="show_title" <?php checked( $include, 'yes' ) ?>>
						<span class="description"><?php _e("Select to show Ad Title in top/left corner.", 'ujinter') ?></span>
					</p>
				   </div>
                   <div class="options_group">
					<p class="form-field">
						<label for="post_once"><?php _e("Show just once only per user", 'ujinter') ?></label>
						<input id="post_once" class="checkbox" type="checkbox" value="yes" name="post_once" <?php checked( $include_two, 'yes' ) ?>>
						<span class="description"><?php _e("Show only one time based on cookie.", 'ujinter') ?></span>
					</p>
				   </div>
			  </div>
	<?php

		echo '</div>';
	}

	/**
	 * Where to see
	 * @since  1.0
	 */
	public function interads_where( $post ) {
		$include = get_post_meta( $post->ID, 'where_show', true );
	?>
	   <div class="tab-content">

	   <!-- checkbox Home Page -->
			  <div class="options_group">
				<p class="form-field">
					<label for="_see_show_home"><?php _e("Enable on Home Page", 'ujinter') ?></label>
					<input id="_see_show_home" class="radio" type="radio" value="show_home" name="where_show" <?php checked( $include, 'show_home' ) ?>>
					<span class="description"><?php _e("Show Ad on Home Page", 'ujinter') ?></span>
				</p>
			   </div>

	   <!-- checkbox All Pages -->
	   		 <div class="options_group">
				<p class="form-field">
					<label for="_see_show_all"><?php _e("Enable on All Pages", 'ujinter') ?></label>
					<input id="_see_show_all" class="radio" type="radio" value="show_all" name="where_show" <?php checked( $include, 'show_all' ) ?>>
					<span class="description"><?php _e("Show Ad on entire site", 'ujinter') ?></span>
				</p>
			   </div>

		<!-- checkbox Custom Pages -->
	  		 <div class="options_group">
				<p class="form-field">
					<label for="_see_show_cust"><?php _e("Enable on Custom Pages", 'ujinter') ?></label>
					<input id="_see_show_cust" class="radio" type="radio" value="show_cust" name="where_show" <?php checked( $include, 'show_cust' ) ?>>
					<span class="description"><?php _e("Show Ad on selected Pages/Posts", 'ujinter') ?></span>
				</p>
			   </div>

		<!-- Select Posts/Pages -->
	    	<div id="show_custom" class="options_group" <?php echo ($include!='show_cust') ? ' style="display:none"' : '' ?>>
				<p class="form-field">
					<label for="ads_link"><?php _e("Selected Posts/Pages", 'ujinter') ?></label>
					<input type="text" name="ads_posts" class="short" id="ads_posts" value="<?php echo get_post_meta( $post->ID, 'ads_posts', true ); ?>" />
					<span class="description"><?php _e("Add any pages or posts id separated by commas. ex: 312, 16, 27", 'ujinter') ?></span>
				</p>
			   </div>

		<!-- checkbox Custom Categories -->
	  		 <div class="options_group">
				<p class="form-field">
					<label for="_see_show_cats"><?php _e("Enable Based on Category", 'ujinter') ?></label>
					<input id="_see_show_cats" class="radio" type="radio" value="show_cats" name="where_show" <?php checked( $include, 'show_cats' ) ?>>
					<span class="description"><?php _e("Show Ad on Posts With a Certain Category", 'ujinter') ?></span>
				</p>
			   </div>

		<!-- Select Categories -->
	    	<div id="show_custom" class="options_group" <?php echo ($include!='show_cats') ? ' style="display:none"' : '' ?>>
				<p class="form-field">
					<label for="ads_cats"><?php _e("Selected Categories", 'ujinter') ?></label>
					<input type="text" name="ads_cats" class="short" id="ads_cats" value="<?php echo get_post_meta( $post->ID, 'ads_cats', true ); ?>" />
					<span class="description"><?php _e("Category slugs separated by commas. ex: uncategorized, videos, etc.", 'ujinter') ?></span>
				</p>
			   </div>

		</div>
	<?php
	}


	/**
	 * Add HTML metaboxes
	 * @since  1.0
	 */
	public function interads_prover( $post ) {
		echo '<a href="http://www.wpmanage.com/interstitial-ads/" target="_blank"><img src="'.plugins_url() . '/interstitial-ads/images/ads-premium.png" style="padding-left:2px" /></a>';
	}


	/**
	 * Save post
	 * @since  1.0
	 */
	public function interads_save( $post_id ) {



		if ( !$_POST ) return $post_id;
		if ( is_int( wp_is_post_revision( $post_id ) ) ) return;
		if ( is_int( wp_is_post_autosave( $post_id ) ) ) return;
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
		if ( !current_user_can( 'edit_post', $post_id )) return $post_id;

		if ( 'interads' == $_POST['post_type'] ){
		// Save fields
			if( isset($_POST['include_html'] ) ) update_post_meta($post_id, 'include_html', esc_html(stripslashes($_POST['include_html']))); else update_post_meta($post_id, 'include_html', '');
			if( isset($_POST['include_url'] ) ) update_post_meta($post_id, 'include_url', esc_html(stripslashes($_POST['include_url']))); else update_post_meta($post_id, 'include_url', '');
			if( isset($_POST['show_title'] ) ) update_post_meta($post_id, 'show_title', esc_html(stripslashes($_POST['show_title']))); else update_post_meta($post_id, 'show_title', '');
			if( isset($_POST['post_random'] ) ) update_post_meta($post_id, 'post_random', esc_html(stripslashes($_POST['post_random']))); else update_post_meta($post_id, 'post_random', '');
			if( isset($_POST['post_once'] ) ) update_post_meta($post_id, 'post_once', esc_html(stripslashes($_POST['post_once']))); else update_post_meta($post_id, 'post_once', '');
			if( isset($_POST['add_posts']) ) update_post_meta($post_id, 'add_posts', esc_html(stripslashes($_POST['add_posts']))); else update_post_meta($post_id, 'add_posts', '');
			if( isset($_POST['where_show'] ) ) update_post_meta($post_id, 'where_show', esc_html(stripslashes($_POST['where_show'])));  else update_post_meta($post_id, 'where_show', '');
			for($x=1; $x<=5; $x++){
				if(isset($_POST['ads_link'.$x])) update_post_meta($post_id, 'ads_link'.$x, esc_html(stripslashes($_POST['ads_link'.$x])));  else update_post_meta($post_id, 'ads_link'.$x, '');
			}
			if( isset($_POST['ads_posts'] ) ) update_post_meta($post_id, 'ads_posts', esc_html(stripslashes($_POST['ads_posts']))); else update_post_meta($post_id, 'ads_posts', '');
			if( isset($_POST['ads_cats'] ) ) update_post_meta($post_id, 'ads_cats', esc_html(stripslashes($_POST['ads_cats']))); else update_post_meta($post_id, 'ads_cats', '');
			if( isset($_POST['datapick1'] ) ) update_post_meta($post_id, '_datapick1', esc_html(stripslashes($_POST['datapick1']))); else update_post_meta($post_id, '_datapick1', '');
			if( isset($_POST['datapick2'] ) ) update_post_meta($post_id, '_datapick2', esc_html(stripslashes($_POST['datapick2']))); else update_post_meta($post_id, '_datapick2', '');
		}

	}

	/**
	 * settings_errors function.
	 * @since 1.0.0
	 */
	public function settings_errors () {
		echo settings_errors( $this->token . '-errors' );
	} // End settings_errors()

	/**
	 * settings_screen function.
	 * @since 1.0.0
	 */
	public function settings_screen () {

	?>
    <div id="ujinter" class="wrap">
        <?php screen_icon( 'interads' ); ?>
        <h2><?php echo esc_html( $this->opt_name ); ?></h2>

        <form action="options.php" method="post">
            <?php settings_fields( $this->page_slug ); ?>
            <?php do_settings_sections( $this->page_slug ); ?>
            <?php //$this->print_fields(); ?>
            <?php submit_button(); ?>
        </form>
    </div>

        <?php
        }



} // End Class
?>
