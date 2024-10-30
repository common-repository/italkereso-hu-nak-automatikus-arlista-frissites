<?php
	/*
		Plugin Name: Italkereső.hu -nak automatikus árlista frissítés
		Plugin URI: https://italkereso.hu
		Description: Az italkereso.hu rendszere által használt árlista automatikus elkészítése és frissítése, ami alapján frissítjük az árakat az italkereso.hu oldalon.
		Version: 1.2
		Author:      italkereso
		Author URI: https://italkereso.hu
		License:     GPL2
		License URI: https://www.gnu.org/licenses/gpl-2.0.html
		Text Domain: woo-italkereso
		WC requires at least: 2.6.0
		WC tested up to: 3.9.1
	*/
													
	include("functions.php");
	add_filter('init', 'witalk_customRSS');
	add_action( 'admin_menu', 'witalk_add_menu_page');
?>