<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
exit;
//rss törlése
global $wp_rewrite;
		$wp_rewrite->flush_rules();
?>