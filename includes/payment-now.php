<?php 

/*
 * Thanh toán nhanh checkout qua Payon
 */
add_filter( 'woocommerce_payment_gateways', 'payon_add_gateway_paynow_class' );
function payon_add_gateway_paynow_class( $gateways ) {
	$gateways[] = 'WC_Payon_Paynow_Gateway'; // your class name is here
	return $gateways;
}
/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'payon_init_gateway_paynow_class' );

function payon_init_gateway_paynow_class() {

	class WC_Payon_Paynow_Gateway extends WC_Payment_Gateway {

 		/**
 		 * Class constructor, more about it in Step 3
 		 */
 		public function __construct() {

            $this->id = 'payon_paynow'; // payment gateway plugin ID
            // $this->icon = 'https://payon.vn/payon-assets/img/logo-payon.svg'; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true; // in case you need a custom credit card form
            $this->method_title = 'Thanh toán nhanh qua PayOn ';
            $this->method_description = 'Khách hàng thanh toán đơn hàng thông qua Cổng thanh toán PayOn'; // will be displayed on the options page

            // but in this tutorial we begin with simple payments
            $this->supports = array(
                'products'
            );
        
            // Method with all the options fields
            $this->init_form_fields();
        
            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option( 'title' );
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option( 'enabled' );
            // This action hook saves the settings
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            // We need custom JavaScript to obtain a token
            add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

 		}

		/**
 		 * Tạo form nhập các thông số
 		 */
 		public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Bật/Tắt',
                    'label'       => 'Bật thanh toán nhanh qua PayOn',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'yes'
                ),
                'title' => array(
                    'title'       => 'Tiêu đề',
                    'type'        => 'text',
                    'description' => 'Thanh toán',
                    'default'     => 'Thanh toán nhanh qua PAYON',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Mô tả',
                    'type'        => 'textarea',
                    'description' => 'Mô tả về cổng thanh toán',
                    'default'     => 'Thanh toán qua cổng PayOn. Bạn sẽ được chuyển tới PayOn để tiến hành thanh toán bằng (Thẻ Quốc tế, ATM, QR code .. ).',
                    'desc_tip'    => true,
                )
            );
            
            if (get_option('payon_option') == 'payon_paynow' || !get_option('payon_option')) {
                $this->form_fields['url_api_key'] = array(
                    'title'       => 'URL_API',
                    'description' => 'Đường dẫn API',
                    'type'        => 'text',
                    'default'     => get_option('url_api_key'),
                    'desc_tip'    => true
                );
                $this->form_fields['mc_auth_user'] = array(
                    'title'       => 'MC_AUTH_USER',
                    'description' => 'Tên Auth basic',
                    'type'        => 'text',
                    'default'     => get_option('mc_auth_user'),
                    'desc_tip'    => true
                );
                $this->form_fields['mc_auth_pass'] = array(
                    'title'       => 'MC_AUTH_PASS',
                    'description' => 'Mật khẩu Http Auth basic',
                    'type'        => 'text',
                    'default'     => get_option('mc_auth_pass'),
                    'desc_tip'    => true
                );
                $this->form_fields['mc_id'] = array(
                    'title'       => 'MC_ID',
                    'description' => 'ID Merchant để định danh khách hàng trên PayOn',
                    'type'        => 'text',
                    'default'     => get_option('mc_id'),
                    'desc_tip'    => true
                );
                $this->form_fields['app_id'] = array(
                    'title'       => 'APP_ID',
                    'description' => 'ID ứng dụng để định danh ứng dụng tích hợp',
                    'type'        => 'text',
                    'default'     => get_option('app_id'),
                    'desc_tip'    => true
                );
                $this->form_fields['api_app_secret_key'] = array(
                    'title'       => 'MC_SECRET_KEY',
                    'description' => 'Khóa để thực hiện mã hóa tham số data trong các hàm nghiệp vụ',
                    'type'        => 'text',
                    'default'     => get_option('api_app_secret_key'),
                    'desc_tip'    => true
                );
            }

            if (!empty($this->get_option('api_app_secret_key'))) {
                (!get_option('api_app_secret_key')) ?
                    add_option('api_app_secret_key', $this->get_option('api_app_secret_key'), '', 'yes')
                    :  update_option('api_app_secret_key', $this->get_option('api_app_secret_key'), 'yes');
            }

            if (!empty($this->get_option('url_api_key'))) {
                (!get_option('url_api_key')) ?
                    add_option('url_api_key', $this->get_option('url_api_key'), '', 'yes')
                    :  update_option('url_api_key', $this->get_option('url_api_key'), 'yes');
            }

            if (!empty($this->get_option('mc_auth_pass'))) {
                (!get_option('mc_auth_pass')) ?
                    add_option('mc_auth_pass', $this->get_option('mc_auth_pass'), '', 'yes')
                    :  update_option('mc_auth_pass', $this->get_option('mc_auth_pass'), 'yes');
            }

            if (!empty($this->get_option('mc_auth_user'))) {
                (!get_option('mc_auth_user')) ?
                    add_option('mc_auth_user', $this->get_option('mc_auth_user'), '', 'yes')
                    :  update_option('mc_auth_user', $this->get_option('mc_auth_user'), 'yes');
            }

            if (!empty($this->get_option('mc_id'))) {
                (!get_option('mc_id')) ?
                    add_option('mc_id', $this->get_option('mc_id'), '', 'yes')
                    :  update_option('mc_id', $this->get_option('mc_id'), 'yes');
            }

            if (!empty($this->get_option('app_id'))) {
                (!get_option('app_id')) ?
                    add_option('app_id', $this->get_option('app_id'), '', 'yes')
                    :  update_option('app_id', $this->get_option('app_id'), 'yes');
            }

            if (!empty($this->get_option('installment_amount'))) {
                (!get_option('installment_amount')) ?
                    add_option('installment_amount', $this->get_option('installment_amount'), '', 'yes')
                    :  update_option('installment_amount', $this->get_option('installment_amount'), 'yes');
            }

            if (!empty($this->get_option('user_fee'))) {
                (!get_option('user_fee')) ?
                    add_option('user_fee', $this->get_option('user_fee'), '', 'yes')
                    :  update_option('user_fee', $this->get_option('user_fee'), 'yes');
            }
            if ($this->get_option('api_app_secret_key') && $this->get_option('app_id') && $this->get_option('url_api_key') && $this->get_option('mc_auth_user') && $this->get_option('mc_auth_pass') && $this->get_option('mc_id')) {
                (!get_option('payon_option')) ?
                add_option('payon_option', 'payon_paynow', '', 'yes')
                :  update_option('payon_option', 'payon_paynow', 'yes');
            } else {
                if(get_option('payon_option') && get_option('payon_option') == 'payon_paynow')
                {
                    delete_option('app_id');
                    delete_option('mc_id');
                    delete_option('mc_auth_user');
                    delete_option('mc_auth_pass');
                    delete_option('url_api_key');
                    delete_option('api_app_secret_key');
                    (!get_option('payon_option')) ?
                    add_option('payon_option', '', '', 'yes')
                    :  update_option('payon_option', '', 'yes');
                }
            }
	 	}

		/**
		 * Xử lý thêm giao diện ở page checkout
		 */
		public function payment_fields() 
        {
            if ( $this->description ) {
                echo wpautop( wp_kses_post( $this->description ) );
            };
            if($this->get_option( 'enabled' ) == 'no')
            {
                echo '<script>
                document.getElementById("payment_method_'.$this->id.'").checked = false;
                document.getElementById("payment_method_'.$this->id.'").disabled = true;
                document.getElementsByClassName("payment_method_'.$this->id.'")[0].style.display = "none";
                </script>';
            }
		}

		/*
		 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
		 */
	 	public function payment_scripts()
        {
            // we need JavaScript to process a token only on cart/checkout pages, right?
            if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
                return;
            }
            if ( 'no' === $this->enabled ) {
                return;
            }
	 	}

		/*
 		 * Fields validation, more in Step 5
		 */
		public function validate_fields() {}

		/*
		 * Xử lý dữ liệu sau khi submit thông tin tạo đơn hàng
		 */
		public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );
            $order_data = $order->get_data();
            
            if(get_option( 'api_app_secret_key') && get_option( 'app_id' ) && get_option( 'url_api_key' ) && get_option( 'mc_auth_user' ) && get_option( 'mc_auth_pass' )){
                include_once (plugin_dir_path(__FILE__)."PayonHelper.php");
                $payonhelper = new PayonHelper();
                
                $merchant_request_id = '';
                foreach($order_data['meta_data'] as $meta){
                    if($meta->key == 'merchant_request_id'){
                        $merchant_request_id = $meta->value;
                    }
                }
                if (empty($merchant_request_id)){
                    $merchant_request_id = 'PAYON_' . $order_id . '_' . date('YmdHis');
                    update_post_meta($order_id, 'merchant_request_id', sanitize_text_field($merchant_request_id));
                }
                if($order_data['payment_method'] == 'payon_paynow'){
                    $data = [
                        "merchant_request_id" => $merchant_request_id,
                        "merchant_id" => (int)get_option( 'mc_id' ),
                        "amount" => (int)$order->get_total(),
                        "description" => 'Thanh toán đơn hàng '.$order_id.' KH '. $order_data['billing']['first_name'] ." " . $order_data['billing']['last_name'],
                        "url_redirect" => get_rest_url( null, "/payon/checkout?order_id=" . $order_id ),
                        "url_notify" => get_rest_url( null, "/payon/notification" ),
                        "url_cancel" => $order->get_cancel_order_url_raw(),
                        "customer_fullname" => $order_data['billing']['first_name'] ." " . $order_data['billing']['last_name'],
                        "customer_email" => $order_data['billing']['email'],
                        "customer_mobile" => $order_data['billing']['phone'],
                    ];
                    $resultCreateOrder = $payonhelper->CreateOrderPaynow($data, get_option( 'api_app_secret_key' ), get_option( 'app_id' ), get_option( 'url_api_key' ), get_option( 'mc_auth_user' ),get_option( 'mc_auth_pass' ));
                    
                    $data['payment_token'] = $resultCreateOrder['data']['payment_token'];
                    update_post_meta($order_id, 'payment_token', sanitize_text_field( $data['payment_token'] ) );
                    $data['payment_id'] = $resultCreateOrder['data']['payment_id'];
                    update_post_meta($order_id, 'payment_id', sanitize_text_field( $data['payment_id'] ) );
                    
                    if ($resultCreateOrder['error_code'] == "00") {
                        $urlCheckout = $resultCreateOrder['data']['url_checkout'];
                            
                        return array(
                            'result'   => 'success',
                            'redirect' => $urlCheckout
                        );
                    } else {
                        echo 'Bạn vui lòng chọn lại thông tin thanh toán.';
                    }
                }
            } else {
                echo 'Vui lòng cài đặt cấu hình thanh toán';
            }
	 	}
 	}
}
