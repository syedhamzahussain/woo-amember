<?php
/*
 * Plugin Name: Amember Proxy Payment Gateway
 * Description: Provide you Amember Proxy Payment Gateway Integration.
 * Author: Syed Hamza Hussain
 * Author URI: https://www.upwork.com/fl/syedhamzahussain
 * Version: 1.1.1.1
 *
 */


/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter('woocommerce_payment_gateways', 'wcproxypay_add_gateway',10,1 );

function wcproxypay_add_gateway($methods) {
        if(!in_array('WC_Gateway_Proxypay', $methods)) {
                $methods[] = 'WC_Gateway_Proxypay';
        }
        return $methods;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action('plugins_loaded', 'tpg_init_gateway_class');

function tpg_init_gateway_class() {
    require_once 'class-proxypay-wc.php';
}

 add_action('wp', 'proxypay_mark_payment_complete', 20);
function proxypay_mark_payment_complete() {
        global $wp;
        if (isset( $wp->query_vars['order-received'] ) && isset($_GET['proxypay_payment_complete']) ) {
            $order_id = absint($wp->query_vars['order-received']);
            $order = wc_get_order($order_id);

            if ($order_id === $order->get_id() && $order->needs_payment()) {
                if ('custom-proxypay' === $order->get_payment_method() ) {
                    $note = 'Payment completed on ProxyPay';
                    $order->add_order_note($note);
                    $order->payment_complete();
                }
            }
        }
    }