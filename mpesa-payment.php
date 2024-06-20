<?php

/*
 * Plugin Name:       Mpesa Payment Gateway
 * Description:       This plugin allows for WooCommerce payments.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Erick Mutua
 * Text Domain:       mpesa-payments-woo
 * Class WC_Gateway_Mpesa file.
 *
 * @package WooCommerce\Gateways
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

add_action( 'plugins_loaded', 'mpesa_payment_init', 11 );

function mpesa_payment_init() {
	if ( class_exists( 'WC_Payment_Gateway' ) ) {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class_wc_payment-gateway-mpesa.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/mpesa_order_statuses.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/mpesa_checkout_description_fields.php';
	}
}

add_filter( 'woocommerce_currencies', 'emsoftwares_add_currencies' );
add_filter( 'woocommerce_currency_symbol', 'emsoftwares_add_currencies_symbol', 10, 2 );
add_filter( 'woocommerce_payment_gateways', 'add_to_woo_mpesa_payment_gateway' );
 
function add_to_woo_mpesa_payment_gateway( $gateways ) {
	$gateways[] = 'WC_Gateway_Mpesa';
	return $gateways;
}

function emsoftwares_add_currencies( $currencies ) {
	$currencies['KSH'] = __( 'Kenyan Shillings', 'mpesa-payments-woo' );
	return $currencies;
}

function emsoftwares_add_currencies_symbol( $currency_symbol, $currency ) {
	switch ( $currency ) {
		case 'KSH':
			$currency_symbol = 'KSH';
			break;
	}
	return $currency_symbol;
}
