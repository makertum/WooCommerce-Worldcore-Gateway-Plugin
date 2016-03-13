<?php
/*
Plugin Name: Worldcore.eu - WooCommerce Gateway
Plugin URI: http://www.makertum.com/
Description: Extends WooCommerce by Adding the Worldcore.eu Gateway.
Version: 0.1
Author: Moritz Walter
Author URI: http://www.makertum.com
*/

// Include our Gateway Class and Register Payment Gateway with WooCommerce
add_action( 'plugins_loaded', 'mkt_worldcore_init', 0 );
function mkt_worldcore_init() {

	// Checking if WooCommerce is installed
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

	// Including Gateway Class
	include_once( 'woocommerce-worldcore.php' );

	// Adding gateway to WooCommerce
	add_filter( 'woocommerce_payment_gateways', 'mkt_add_worldcore_gateway' );
	function mkt_add_worldcore_gateway( $methods ) {
		$methods[] = 'MKT_WORLDCORE';
		return $methods;
	}
}

// Add action link to settings panel
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'mkt_worldcore_action_links' );
function mkt_worldcore_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'mkt-worldcore' ) . '</a>',
	);
	return array_merge( $plugin_links, $links );
}

?>
