<?php
if(!defined('ABSPATH')) exit;

if(!class_exists('WC_Payment_Gateway')) { return; }

class WC_Gateway_Proxypay extends WC_Payment_Gateway {

	var $ipn_url;

	public function __construct() {
		global $woocommerce;

		$this->id = "custom-proxypay";
		$this->method_title = __("Custom ProxyPay", 'woo-proxypay-hosted-payment-gateway');
		$this->method_description = __("aMember Pro ProxyPay Payment Gateway Plug-in for WooCommerce", 'woo-proxypay-hosted-payment-gateway');
		$this->title = __("Custom ProxyPay", 'woo-proxypay-hosted-payment-gateway');
		$this->icon = null;
		$this->has_fields = true;
		$this->init_form_fields();
		$this->init_settings();
		$this->ipn_url = add_query_arg('wc-api', 'WC_Gateway_Proxypay', home_url('/'));

		foreach($this->settings as $setting_key => $value) {
			$this->$setting_key = $value;
		}

		if(is_admin()) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		}		
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'		=> __( 'Enable / Disable', 'woo-proxypay-hosted-payment-gateway' ),
				'label'		=> __( 'Enable this payment gateway', 'woo-proxypay-hosted-payment-gateway' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
			'title' => array(
				'title'		=> __( 'Title', 'woo-proxypay-hosted-payment-gateway' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Payment title of checkout process.', 'woo-proxypay-hosted-payment-gateway' ),
				'default'	=> __( 'Credit card', 'woo-proxypay-hosted-payment-gateway' ),
			),
			'description' => array(
				'title'		=> __( 'Description', 'woo-proxypay-hosted-payment-gateway' ),
				'type'		=> 'textarea',
				'desc_tip'	=> __( 'Payment description of checkout process.', 'woo-proxypay-hosted-payment-gateway' ),
				'default'	=> __( 'Successfully payment through credit card.', 'woo-proxypay-hosted-payment-gateway' ),
				'css'		=> 'max-width:450px;'
			),
			'merchant_id' => array(
				'title'		=> __( 'ProxyPay Merchant ID', 'woo-proxypay-hosted-payment-gateway' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Please enter Merchant ID provided by ProxyPay here.', 'woo-proxypay-hosted-payment-gateway' ),
			),
			'merchant_secret' => array(
				'title'		=> __( 'ProxyPay Merchant Secret', 'woo-proxypay-hosted-payment-gateway' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Please enter Merchant Secret provided by ProxyPay here.', 'woo-proxypay-hosted-payment-gateway' ),
			),
		);		
	}

	/*
     * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
     */

    public function payment_scripts() {
        
    }

    /*
     * Fields validation, more in Step 5
     */

    public function validate_fields() {
        
    }

	public function process_payment($order_id) {
		global $woocommerce;

			$customer_order = new WC_Order($order_id);
			$proxypay_options = get_option('woocommerce_custom-proxypay_settings');
			$currency_code = get_woocommerce_currency();
			$order_number = $customer_order->get_order_number();
			$cart_total = $customer_order->get_total();
			$IPN_Response = $this->ipn_url;
			
			// Variables
			$post = [
				'currency' => $currency_code,
				'first_total' => $cart_total,
				'first_period' => '1m',
				'second_total' => $cart_total,
				'rebill_times' => 0,
				'public_id' => $order_number,
				'merchant_id' => $proxypay_options['merchant_id'],
				'ipn_url' => $IPN_Response,
				'thanks_url' => $this->get_return_url($customer_order),
				'cancel_url' => $customer_order->get_cancel_order_url(),
				'name_f' => $customer_order->get_billing_first_name(),
				'name_l' => $customer_order->get_billing_last_name(),
				'email' => $customer_order->get_billing_email(),
				'country' => $customer_order->get_billing_country(),
				'state' => $customer_order->get_billing_state(),
				'city' => $customer_order->get_billing_city(),
				'zip' => $customer_order->get_billing_postcode(),
				'street' => $customer_order->get_billing_address_1(),
				'phone' => $customer_order->get_billing_phone(),
				'description' => $order_id,
				'hash' => hash('sha256', $proxypay_options['merchant_secret']),
			];
            
            $url = add_query_arg($post, 'https://mania-hosts.co.uk/portal/proxypay/signup' );
            return array(
                    'result'   => 'success',
                    'redirect' => $url,
            );
	}
    
    function log( $message ){
        $log = new WC_Logger();
        $log_entry = print_r( $message, true );
        $log->log( 'proxypay-log', $log_entry );
    }
}