<?php
/*
Plugin Name: Hosting Panel
Description: Shows your bandwidth and hosting quota in dashboard from cPanel/WHM
Author: Ian Anderson Gray
Version: 0.2
Author URI: http://iag.me/
*/

/*
Thanks to Paul Or, for much of the code:
 http://www.paulor.net/tag/cpanel-2/
*/


function SP_wp_dashboard()
   {
      $server_name =    "<-- Enter Server Name Here -->";
      $user =           "<-- Enter cPanel Username here -->";
      $current_plan =   "<!-- Enter your package name here -->";
      $cached_time =    14400; // Time in Seconds
      include("/home/$user/incs/bwMonitor.php");
   }

function SP_wp_dashboard_setup()
   {
      wp_add_dashboard_widget( 'SP_wp_dashboard', __( 'Hosting Panel' ),'SP_wp_dashboard');
   }

add_action('wp_dashboard_setup', 'SP_wp_dashboard_setup');

?>