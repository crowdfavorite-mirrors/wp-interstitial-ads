<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Uji_Interst extends Uji_Interst_Functions{
	var $version;
	private $file;
	public $keep;
	
	/**
	 * __construct function.
	 * 
	 */
	public function __construct ( $file ) {
		$this->token = 'ujinter';
		$this->plugin_url = trailingslashit( plugins_url( '', $plugin = $file ) );
			
		$this->load_plugin_textdomain();
		add_action( 'init', array( &$this, 'load_localisation' ), 0 );
		
		// Setup post types.
		require_once( 'class-interstitial-admin-settings.php' );
		require_once( 'class-interstitial-admin.php' );
		$this->admin_inter = new Uji_Interst_Admin($file);
		
		//Add style
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_styles' ) );
		//Add scripts
		add_action( 'wp_footer', array( &$this, 'enqueue_scripts' ) );
		
		//Set Cookies
		add_action( 'get_header', array( &$this, 'set_cookie' ) );
		
		//Add Ads Ajax
		if( $this->is_cached( ) ){
		add_action( 'wp_ajax_inter_ads_action', array( &$this, 'inter_ajax_ads' ) );
		add_action( 'wp_ajax_nopriv_inter_ads_action', array( &$this, 'inter_ajax_ads' ) );
		}
		
		//Add Ads
		add_action( 'wp_footer', array( &$this, 'inter_ads' ) );
		

		
	}// End__construct()
	
	/**
	 * Load the plugin's localisation file.
	 * @since 1.0
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'interstitial-ads', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation()
	
	/**
	 * Load the plugin textdomain from the main WordPress "languages" folder.
	 * @since  1.0
	 */
	public function load_plugin_textdomain () {
	    $domain = 'interstitial-ads';
	    // The "plugin_locale" filter is also used in load_plugin_textdomain()
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain()
	
	/**
	 * Register frontend CSS files.
	 * @since  1.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->token . '-interads', esc_url( $this->plugin_url . 'css/interads.css' ), '', '1.0', 'all' );
	} // End enqueue_styles()
	
	
	/**
	 * Register frontend JS files.
	 * @since  1.0
	 * @return void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->token . '-count', esc_url( $this->plugin_url . 'js/kkcountdown.min.js' ), array( 'jquery' ), '1.0', true );
		wp_register_script( $this->token . '-interads', esc_url( $this->plugin_url . 'js/interads.js' ), array( 'jquery' ), '1.0', true );
	} // End register_script()
	
	
	/**
	 * Set Cookie.
	 * @since  1.0
	 * @return void
	 */
	public function set_cookie () {
		if ( !is_admin() ){
			global $post;
			$ad_id = $this->is_interads( $post->ID );		
	
			if( $ad_id ) 
				$this->let_cookie( $ad_id );
		}
	} // End set_cookie()
	
	/**
	 * Check trigger it
	 * @since  1.0
	 */
	public function inter_ads ($id = NULL) {
		if($id){
			$id_post = $id;
		} else {
			global $post;
			$id_post = $post->ID;
		}
		//AD id
		$ad_id = $this->is_interads( $id_post ); 	
		
		$settings = array(
						'bar_color'  	 => 'bar_color', 
						'title_color' 	 => 'title_color', 
						'back_color'  	 => 'back_color', 
						'cont_width'  	 => 'cont_width',
						'close_name'  	 => 'but_close', 
						'show_timer'  	 => 'show_timer', 
						'countdown_time' => 'countdown_time', 
						'wait_time' 	 => 'wait_time', 
						'tra_close'	 	 => 'tra_close', 
						'tra_wait' 		 => 'tra_wait',
						'tra_seconds' 	 => 'tra_seconds',
						'tra_minutes' 	 => 'tra_minutes',
						'tra_until' 	 => 'tra_until'
						);
						
			foreach($settings as $set => $name){
				${$name} = $this->int_option( $set );
			}
		
		// ADD val if cached
		if( $this->is_cached( ) ){
	
			//Timing
			$JSinterAds = array( 'is_cached' => 'true', 'id_post' => $id_post, 'ajaxurl' => admin_url( 'admin-ajax.php' ) );
			
			if ( !empty( $wait_time ) && ( int ) $wait_time > 0 ){
				$JSinterAds = array_merge( $JSinterAds, array( 'is_wait' => $wait_time ) );	
			}
			if ( !empty( $countdown_time ) && ( int ) $countdown_time > 0 && $show_timer == 'yes' ){
				wp_enqueue_script( $this->token . '-count' );
				$JSinterAds = array_merge($JSinterAds, array( 'is_count' => $countdown_time ));
				$add_wait = ( !empty( $wait_time ) && ( int ) $wait_time > 0 ) ? ( int ) $wait_time : 0;
				$countdown = time() + $add_wait + ( int ) $countdown_time;
				
			}
			if ( !empty( $tra_seconds ) && $show_timer == 'yes' ){
				$JSinterAds = array_merge( $JSinterAds, array( 'seconds' => $tra_seconds ));
			}
			if ( !empty( $tra_minutes ) && $show_timer == 'yes' ){
				$JSinterAds = array_merge( $JSinterAds, array( 'minutes' => $tra_minutes ));
			}
			wp_enqueue_style( $this->token . '-interads' );			
			wp_enqueue_script ( $this->token . '-interads' );
			wp_localize_script( $this->token . '-interads', 'interAds', $JSinterAds );
			
		} 
		
		
		if( $ad_id ){
		//add impression
		$this->impression( $id_post );
			

			$html_ad = $style_main = $style_cont = '';
					
			$cont_width = !empty( $cont_width ) ? 'width:'. $cont_width .'px;' : '';
			$back_color = !empty( $back_color ) ? 'background:'. $back_color .';' : '';
			$bar_color  = !empty( $bar_color ) ? 'background:'. $bar_color .';' : '';
			$tit_color  = !empty( $title_color ) ? 'color:'. $title_color .';' : '';
			$is_wait    = !empty( $wait_time ) && ( int ) $wait_time > 0  ? 'display: none;' : '';
			
			$col_style  = !empty( $tit_color ) ? 'style="'. $tit_color .'"' : '';
			$bor_style  = !empty( $title_color ) ? 'style="border-color:'. $title_color .';"' : '';
			$bor_style  = ( !empty( $but_close ) && $but_close == 'no' ) ? 'style="border:none;"' : '';	
			
			if( !empty( $back_color ) || !empty( $is_wait ) ){
				$style_main = 'style="'. $back_color . $is_wait .'"';	
			}
			
			if( !empty( $cont_width ) ){
				$style_cont = 'style="'. $cont_width .'"';	
			}
			
			if( !empty( $bar_color ) || !empty( $tit_color ) ){
				$style_bar = 'style="'. $bar_color . $tit_color .'"';	
			}
			//Not caching
			if( !$this->is_cached( ) ){		
				//Timing
				$JSinterAds = array();
				if ( !empty( $wait_time ) && ( int ) $wait_time > 0 ){
					$JSinterAds = array( 'is_wait' => $wait_time );	
				}
				if ( !empty( $countdown_time ) && ( int ) $countdown_time > 0 && $show_timer == 'yes' ){
					wp_enqueue_script( $this->token . '-count' );
					$JSinterAds = array_merge($JSinterAds, array( 'is_count' => $countdown_time ));
					$add_wait = ( !empty( $wait_time ) && ( int ) $wait_time > 0 ) ? ( int ) $wait_time : 0;
					$countdown = time() + $add_wait + ( int ) $countdown_time;
					
				}
				if ( !empty( $tra_seconds ) && $show_timer == 'yes' ){
					$JSinterAds = array_merge( $JSinterAds, array( 'seconds' => $tra_seconds ));
				}
				if ( !empty( $tra_minutes ) && $show_timer == 'yes' ){
					$JSinterAds = array_merge( $JSinterAds, array( 'minutes' => $tra_minutes ));
				}
				if ( !$this->is_cached( ) ){
					$JSinterAds = array_merge( $JSinterAds, array( 'id_post' => $id_post, 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
				}
				//add JS var for timing
				if ( !empty( $JSinterAds ) ){
					wp_localize_script( $this->token . '-interads', 'interAds', $JSinterAds );
				}
				
				wp_enqueue_style( $this->token . '-interads' );
				wp_enqueue_script( $this->token . '-interads' );
			}
			
			$html_ad .= '<!-- ©wpmanage.com-->
						<div id="interads" '. $style_main .'>
						<div id="interads-bar" '. $style_bar .'>
							<div id="interads-tit" class="interads">'. $this->get_interad( $ad_id, 'title' ) .'</div>
							<div id="interads-close">';
						
			if( !empty( $show_timer ) && $show_timer == 'yes' )
					  $html_ad .= '<div id="inter-mess" '. $bor_style .'>
										<span> '. $tra_wait .' </span>
										<span time="'. $countdown .'" class="interads-kkcount-down"></span>
										<span> '. $tra_until .' </span>
								  </div>';
							
			if( !empty( $but_close ) && $but_close == 'yes' )
			$html_ad .=  '<a href="javascript:return false" onclick="interads_close();" '. $col_style .'> '. $tra_close .' <span> x </span></a>';
			$html_ad .=  '</div>
					</div>
					<div id="interads-cnt" '. $style_cont .'>
						'. $this->get_interad( $ad_id ) .'
					</div>
				  </div>';
		
		   //Post AD content
		    if( !empty( $html_ad ) && !$this->is_cached( ) ){
				echo $html_ad;
			} else if( !empty( $html_ad ) && $this->is_cached( ) ) {
				return $html_ad;
			}
		
		} 
		
	}
		
} // End Class
?>
