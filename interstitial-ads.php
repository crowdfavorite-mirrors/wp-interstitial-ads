<?php
/*
Plugin Name: Interstitial Ads (Supports Categories)
Plugin URI: http://wpmanage.com/interstitial-ads
Description: Allows you to show Interstitial Ads on any WordPress site.  This has been modified by Crowd Favorite to support categories more reliably than the PRO version of the plugin.
Version: 1.2-categories
Author: Raul Ujog
Author URI: http://wpmanage.com/
*/

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	 if ( ! class_exists( 'Uji_Interst' ) ) {
		    //Functions
			require_once( 'classes/class-interstitial-functions.php' );
			//Interstate Ads Front
		 	require_once( 'classes/class-interstitial.php' );
	 }


	 global $ujinter;
	 $ujinter = new Uji_Interst( __FILE__ );
	 $ujinter->version = '1.2';

?>
