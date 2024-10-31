<?php
/*
 * Plugin Name: PayOn PaymentGateway
 * Version: 1.0.8
 * Plugin URI: https://payon.vn
 * Description: Cổng thanh toán PayOn, giải pháp thanh toán từ xa, môt sản phẩm của CÔNG TY CỔ PHẦN CÔNG NGHỆ VI MÔ.
 * Author: devteampayon - 06/09/2022
 * Author URI: 
 * Requires PHP: 5.6
 */

define( 'PAYON_DIR', plugin_dir_path( __FILE__ ) );
define( 'PAYON_URL', plugins_url( '/', __FILE__ ) );
 
require_once plugin_dir_path(__FILE__) . 'includes/payment-now.php';
require_once plugin_dir_path(__FILE__) . 'includes/installment.php';

function payon_payment_activate(){
	register_uninstall_hook( __FILE__, 'payon_payment_uninstall' );
}
register_activation_hook( __FILE__, 'payon_payment_activate' );
register_deactivation_hook( __FILE__, 'payon_payment_deactivation' );

// Gỡ các option của plugin sau khi xóa plugin
function payon_payment_uninstall(){
    
    delete_option('mc_id');
    delete_option('mc_auth_user');
    delete_option('mc_auth_pass');
    delete_option('url_api_key');
    delete_option('api_app_secret_key');
    delete_option('app_id');
	//trả góp
    if(!empty(get_option('woocommerce_installment_settings')))
    {
        delete_option('woocommerce_installment_settings');
        delete_option('user_fee');
        delete_option('enabled_ins');
        delete_option('installment_amount');
    }

    //thanh toán checkout
    if(!empty(get_option('woocommerce_payon_paynow_settings')))
    {
        delete_option('woocommerce_payon_paynow_settings');
    }
}

// Gỡ các option của plugin sau khi hủy kích hoạt plugin
function payon_payment_deactivation(){
    if(!empty(get_option('payon_option')))
    {
        delete_option('payon_option');
    }
}
