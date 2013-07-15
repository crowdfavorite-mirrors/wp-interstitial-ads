<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Uji_Interst_Functions {

	/**
	 * Check if trigger it
	 * @since  1.0
	 */
	protected function is_interads ( $id ) {
		$args = array(
			 'post_type'     	 => 'interads',
			 'post_status' 		 => 'publish',
			 'order' 			 => 'DESC',
			 'orderby'			 => 'date',
			 'posts_per_page'	 => 1

		);

		$queryin = new WP_Query( $args );
		$cicle = true;

		while ( $queryin->have_posts() && $cicle ):
				$queryin->the_post();
				$valid = true;

				//Cookie check
				$is_cookie = ( isset( $_COOKIE["inter_ads"] ) && !empty( $_COOKIE["inter_ads"] ) ) ? maybe_unserialize( stripslashes($_COOKIE["inter_ads"]) ) : '';
				$want = get_post_meta( get_the_ID(), 'post_once', true );

				if( $valid && ( !empty( $is_cookie ) && in_array( get_the_ID(), $is_cookie ) && !empty( $want ) && $want == 'yes' ) ){
					$valid = false;
				}


				//Selected
				$is_as_html =  get_post_meta( get_the_ID(), 'include_html', true );
				if( $valid && empty( $is_as_html ) ){
					$valid = false;

				}

				//Where
				$where = get_post_meta( get_the_ID(), 'where_show', true );

				if( $valid && $where == 'show_home' ){
					if( !is_home() && !is_front_page() ) {
						$valid = false;
					}
				}

				//CUSTOM PAGE
				if( $valid && $where == 'show_cust' && !is_home() && !is_front_page() ){
					$ads_posts = get_post_meta( get_the_ID(), 'ads_posts', true );
					if(!empty($ads_posts)){
						$ids = explode( ",", $ads_posts );
						if( !in_array( $id, $ids ) ){
							$valid = false;
						}
					}
				}
				//CUSTOM PAGE NOT HOME
				if( $valid && $where == 'show_cust' && ( is_home() || is_front_page() ) ){
					$valid = false;
				}


				//END RETURN
				if( $valid ){
					$cicle = false;
					return get_the_ID();
				}

		endwhile;
		wp_reset_query();
	}

	/**
	 * Add impression
	 * @since  1.0
	 */
	protected function impression ( $id ) {
		$num = get_post_meta( $id, 'ads_impressions', true );
		$num = (!empty($num)) ? (int) $num + 1 : 1;
		update_post_meta($id, 'ads_impressions', $num );
	}

	/**
	 * Get Option
	 * @since  1.0
	 */
	protected function int_option ( $name, $default = NULL ) {
		$val = get_option( $this->token );

		if( !empty( $val[$name] ) )
			return $val[$name];
		elseif( $default && !empty( $val[$name] ) )
			return $default;
		else
			return '';
	}

	/**
	 * Check Cookie
	 * @since  1.0
	 */
	protected function is_cookie ( $id ) {
		$is_cookie = ( isset( $_COOKIE["inter_ads"] ) && !empty( $_COOKIE["inter_ads"] ) ) ? maybe_unserialize( stripslashes($_COOKIE["inter_ads"]) ) : '';
		if( !empty( $is_cookie ) && !empty( $is_cookie[0] ) && !in_array( $id, $is_cookie ) ){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add/Edit Ad Cookie
	 * @since  1.0
	 */
	protected function let_cookie ( $id ) {
		$want = get_post_meta( $id, 'post_once', true );
		$add_cook = array();

		if( !empty( $want ) && $want == 'yes' ){
			$is_cookie = ( isset( $_COOKIE["inter_ads"] ) && !empty( $_COOKIE["inter_ads"] ) ) ? maybe_unserialize( stripslashes($_COOKIE["inter_ads"]) ) : '';

			if( !empty( $is_cookie ) && !empty( $is_cookie[0] ) && !in_array( $id, $is_cookie ) ){

				$add_cook = array_merge( $is_cookie, array( $id ) );
			}
			else if( !empty( $is_cookie ) && in_array( $id, $is_cookie ) ){

				$add_cook = '';

			}else{

				$add_cook = array( $id );
			}

			if( !empty( $add_cook ) ){
				setcookie( "inter_ads", "", time() - 3600, '/' );
				setcookie( "inter_ads", maybe_serialize(  array_unique( $add_cook ) ), time() + 3600*24, '/' );
			}
		}

	}

	/**
	 * Is Cache Plugin
	 * @since  1.0
	 */
	public function is_cached ( ) {
		$is = $this->int_option ( 'cache_in', 'no' );
		$chached = ($is == 'yes') ? true : false;
		return $chached;
	}

	/**
	 * Ad content with Cache Plugin
	 * @since  1.0
	 */
	public function inter_ajax_ads ( ) {
		$id = $_POST['id_post'];
		$ad_id = $this->is_interads( $id );
		$mess =  $this->inter_ads( $id );
		if( !empty( $mess ) && !$this->is_cookie ( $ad_id ) && $ad_id ){
			$this->let_cookie ( $ad_id );
			echo $mess;
		} else if( empty( $mess ) || $this->is_cookie ( $ad_id ) || !$ad_id ){
			echo 'none_interads';
		}

		die();
	}

	/**
	 * Get Ad Contents
	 * @since  1.0
	 */
	protected function get_interad ( $id, $return = 'content' ) {

		if( $return == 'title' ){
			$show_it =  get_post_meta( $id, 'show_title', true );
			if( $show_it == 'yes' ){
				$get_ad = get_post( $id );
				return $get_ad->post_title;
			}

		}else{

			return $this->get_content ( $id );

		}

	}

	/**
	 * Get Ad Content
	 * @since  1.0
	 */
	private function get_content ( $id ) {

		$cnt_html =  get_post_meta( $id, 'include_html', true );

			//is HTML
			if( $cnt_html ){
				$get_ad = get_post( $id );
				return do_shortcode( $get_ad->post_excerpt );
			}
	}


} // End Class
?>
