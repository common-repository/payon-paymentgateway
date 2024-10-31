<?php

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter('woocommerce_payment_gateways', 'payon_add_gateway_ins_class');
function payon_add_gateway_ins_class($gateways)
{
    $gateways[] = 'WC_Payon_Ins_Gateway'; // your class name is here
    return $gateways;
}
/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action('plugins_loaded', 'payon_init_gateway_ins_class');

// Register new status
function register_wait_call_order_status()
{
    register_post_status('wc-payed', array(
        'label' => 'Đã thanh toán',
        'public' => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list' => true,
        'exclude_from_search' => false,
        'label_count' => _n_noop('Đã thanh toán (%s)', 'Đã thanh toán (%s)')
    ));
}

// Add custom status to order status list
function add_wait_call_to_order_statuses($order_statuses)
{
    $new_order_statuses = array();
    foreach ($order_statuses as $key => $status) {
        $new_order_statuses[$key] = $status;
        if ('wc-pending' === $key) {
            $new_order_statuses['wc-payed'] = 'Đã thanh toán';
        }
    }
    return $new_order_statuses;
}
add_action('init', 'register_wait_call_order_status');
add_filter('wc_order_statuses', 'add_wait_call_to_order_statuses');

function payon_init_gateway_ins_class()
{

    class WC_Payon_Ins_Gateway extends WC_Payment_Gateway
    {
        /**
         * Class constructor, more about it in Step 3
         */
        public function __construct()
        {
            $this->id = 'installment'; // payment gateway plugin ID
            // $this->icon = 'https://payon.vn/payon-assets/img/logo-payon.svg'; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true; // in case you need a custom credit card form
            $this->method_title = 'Trả góp 0% qua PayOn';
            $this->method_description = 'Sử dụng thẻ tín dụng để mua hàng trả góp';
            $this->supports = array(
                'products'
            );

            // Method with all the options fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled_ins = $this->get_option('enabled_ins');
            $this->installment_amount = $this->get_option('installment_amount');
            $this->user_fee = $this->get_option('user_fee');
            // This action hook saves the settings
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

            // We need custom JavaScript to obtain a token
            add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
            //Tạo page checkout trả góp
            $this->create_checkout();
        }

        /**
         * Tạo page checkout trả góp
         */
        public function create_checkout()
        {
            global $wpdb;
            $post = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE `post_name` = %s", 'thanh-toan-tra-gop'));
            if ($post) {
            } else {
                $data = array(
                    'post_author' => 1,
                    'post_date' => date('Y-m-d H:i:s'),
                    'post_date_gmt' => gmdate('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'))),
                    'post_content' => '<p>[checkout_tragop]</p>',
                    'post_title' => 'Thanh toán trả góp PayOn',
                    'post_excerpt' => '',
                    'post_status' => 'publish',
                    'ping_status' => 'closed',
                    'post_password' => '',
                    'post_name' => 'thanh-toan-tra-gop',
                    'to_ping' => '',
                    'pinged' => '',
                    'post_modified' => date('Y-m-d H:i:s'),
                    'post_modified_gmt' => gmdate('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s'))),
                    'post_content_filtered' => '',
                    'post_parent' => 0,
                    'guid' => home_url() . '',
                    'menu_order' => 0,
                    'post_type' => 'page',
                    'post_mime_type' => '',
                    'comment_count' => 0,
                    'comment_status' => 'closed'
                );
                $wpdb->insert($wpdb->posts, $data);
                $my_id = $wpdb->insert_id;
                if ($my_id) {
                    $wpdb->update($wpdb->posts, array('guid' => home_url() . '/?page_id=' . $my_id), array('id' => $my_id));
                }
            }
        }

        /**
         * Tạo form nhập các thông số
         */
        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled_ins' => array(
                    'title' => 'Bật/Tắt',
                    'label' => 'Bật thanh toán trả góp PayOn',
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'yes'
                ),
                'user_fee' => array(
                    'title' => 'Người chịu phí',
                    'label' => 'Tích chọn người mua trả phí thanh toán',
                    'type' => 'checkbox',
                    'description' => 'Mặc định người mua sẽ chịu phí thanh toán, Nếu không tích chọn thì người bán chịu phí thanh toán',
                    'default' => 'yes',
                    'desc_tip' => true
                ),
                'installment_amount' => array(
                    'title' => 'Số tiền tối thiểu trả góp',
                    'description' => 'Số tiền tối thiểu của đơn hàng có thể trả góp',
                    'type' => 'number',
                    'default' => '3000000',
                    'desc_tip' => true
                ),
                'title' => array(
                    'title' => 'Tiêu đề',
                    'type' => 'text',
                    'description' => 'Tiêu đề phương thức thanh toán',
                    'default' => 'Trả góp 0% qua PayOn',
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => 'Mô tả',
                    'type' => 'textarea',
                    'description' => 'Mô tả về cổng',
                    'default' => 'Thanh toán trả góp 0% khi mua hàng qua PayOn',
                    'desc_tip' => true,
                ),
                'show_button' => array(
                    'title' => 'Hiển thị nút thanh toán',
                    'label' => 'Hiển thị nút thanh toán trả góp tại xem chi tiết sản phẩm',
                    'type' => 'checkbox',
                    'description' => 'Mặc định nút thanh toán trả góp sẽ không hiển thị',
                    'default' => 'no',
                    'desc_tip' => true
                ),
                'show_prepay' => array(
                    'title' => 'Cho phép thanh toán trước 1 phần',
                    'label' => 'Cho phép khách hàng chọn thanh toán trả góp 1 phần của giá trị sảm phẩm. ( Từ 10 đến 70% )',
                    'type' => 'checkbox',
                    'description' => 'Mặc định Khách Hàng có thể thanh toán trả góp 1 phần của giá trị sảm phẩm',
                    'default' => 'yes',
                    'desc_tip' => true
                ),
            );

            if (get_option('payon_option') == 'installment' || !get_option('payon_option')) {
                $this->form_fields['url_api_key'] = array(
                    'title' => 'URL_API',
                    'description' => 'Đường dẫn API',
                    'type' => 'text',
                    'default' => get_option('url_api_key'),
                    'desc_tip' => true
                );
                $this->form_fields['mc_auth_user'] = array(
                    'title' => 'MC_AUTH_USER',
                    'description' => 'Tên Auth basic',
                    'type' => 'text',
                    'default' => get_option('mc_auth_user'),
                    'desc_tip' => true
                );
                $this->form_fields['mc_auth_pass'] = array(
                    'title' => 'MC_AUTH_PASS',
                    'description' => 'Mật khẩu Http Auth basic',
                    'type' => 'text',
                    'default' => get_option('mc_auth_pass'),
                    'desc_tip' => true
                );
                $this->form_fields['mc_id'] = array(
                    'title' => 'MC_ID',
                    'description' => 'ID Merchant để định danh khách hàng trên PayOn',
                    'type' => 'text',
                    'default' => get_option('mc_id'),
                    'desc_tip' => true
                );
                $this->form_fields['app_id'] = array(
                    'title' => 'APP_ID',
                    'description' => 'ID ứng dụng để định danh ứng dụng tích hợp',
                    'type' => 'text',
                    'default' => get_option('app_id'),
                    'desc_tip' => true
                );
                $this->form_fields['api_app_secret_key'] = array(
                    'title' => 'MC_SECRET_KEY',
                    'description' => 'Khóa để thực hiện mã hóa tham số data trong các hàm nghiệp vụ',
                    'type' => 'text',
                    'default' => get_option('api_app_secret_key'),
                    'desc_tip' => true
                );
            }
            if (!empty($this->get_option('api_app_secret_key'))) {
                (!get_option('api_app_secret_key')) ?
                    add_option('api_app_secret_key', $this->get_option('api_app_secret_key'), '', 'yes')
                    : update_option('api_app_secret_key', $this->get_option('api_app_secret_key'), 'yes');
            }
            if (!empty($this->get_option('url_api_key'))) {
                (!get_option('url_api_key')) ?
                    add_option('url_api_key', $this->get_option('url_api_key'), '', 'yes')
                    : update_option('url_api_key', $this->get_option('url_api_key'), 'yes');
            }
            if (!empty($this->get_option('mc_auth_pass'))) {
                (!get_option('mc_auth_pass')) ?
                    add_option('mc_auth_pass', $this->get_option('mc_auth_pass'), '', 'yes')
                    : update_option('mc_auth_pass', $this->get_option('mc_auth_pass'), 'yes');
            }
            if (!empty($this->get_option('mc_auth_user'))) {
                (!get_option('mc_auth_user')) ?
                    add_option('mc_auth_user', $this->get_option('mc_auth_user'), '', 'yes')
                    : update_option('mc_auth_user', $this->get_option('mc_auth_user'), 'yes');
            }
            if (!empty($this->get_option('mc_id'))) {
                (!get_option('mc_id')) ?
                    add_option('mc_id', $this->get_option('mc_id'), '', 'yes')
                    : update_option('mc_id', $this->get_option('mc_id'), 'yes');
            }
            if (!empty($this->get_option('app_id'))) {
                (!get_option('app_id')) ?
                    add_option('app_id', $this->get_option('app_id'), '', 'yes')
                    : update_option('app_id', $this->get_option('app_id'), 'yes');
            }
            if (!empty($this->get_option('installment_amount'))) {
                (!get_option('installment_amount')) ?
                    add_option('installment_amount', $this->get_option('installment_amount'), '', 'yes')
                    : update_option('installment_amount', $this->get_option('installment_amount'), 'yes');
            }
            if (!empty($this->get_option('enabled_ins'))) {
                (!get_option('enabled_ins')) ?
                    add_option('enabled_ins', $this->get_option('enabled_ins'), '', 'yes')
                    : update_option('enabled_ins', $this->get_option('enabled_ins'), 'yes');
            }
            if (!empty($this->get_option('user_fee'))) {
                (!get_option('user_fee')) ?
                    add_option('user_fee', $this->get_option('user_fee'), '', 'yes')
                    : update_option('user_fee', $this->get_option('user_fee'), 'yes');
            }
            if (!empty($this->get_option('show_button'))) {
                (!get_option('show_button')) ?
                    add_option('show_button', $this->get_option('show_button'), '', 'yes')
                    : update_option('show_button', $this->get_option('show_button'), 'yes');
            }
            if (!empty($this->get_option('show_prepay'))) {
                (!get_option('show_prepay')) ?
                    add_option('show_prepay', $this->get_option('show_prepay'), '', 'yes')
                    : update_option('show_prepay', $this->get_option('show_prepay'), 'yes');
            }

            if ($this->get_option('api_app_secret_key') && $this->get_option('app_id') && $this->get_option('url_api_key') && $this->get_option('mc_auth_user') && $this->get_option('mc_auth_pass')) {
                (!get_option('payon_option')) ?
                    add_option('payon_option', 'installment', '', 'yes')
                    : update_option('payon_option', 'installment', 'yes');
            } else {
                if (get_option('payon_option') && get_option('payon_option') == 'installment') {
                    delete_option('app_id');
                    delete_option('mc_id');
                    delete_option('mc_auth_user');
                    delete_option('mc_auth_pass');
                    delete_option('url_api_key');
                    delete_option('api_app_secret_key');
                    (!get_option('payon_option')) ?
                    add_option('payon_option', '', '', 'yes')
                        : update_option('payon_option', '', 'yes');
                }
            }
        }

        /**
         * Xử lý thêm giao diện ở page checkout
         */
        public function payment_fields()
        {
            $total = WC()->cart->get_cart_contents_total();
            if ($this->description) {
                // display the description with <p> tags etc.
                if ($total >= (int) get_option('installment_amount')) {
                    echo wpautop(wp_kses_post($this->description));
                } else {
                    echo wpautop(wp_kses_post($this->description . ' Số tiền thanh toán phải lớn hơn ' . number_format(get_option('installment_amount'), 0, ",", ".") . '₫'));
                }
            }
            ;

            if ($this->get_option('enabled_ins') == 'yes') {
                if (get_option('api_app_secret_key') && get_option('app_id') && get_option('url_api_key') && get_option('mc_auth_user') && get_option('mc_auth_pass')) {
                    include_once(plugin_dir_path(__FILE__) . "PayonHelper.php");
                    $payonhelper = new PayonHelper();
                    // get bank installment
                    $listBankInstallment = $payonhelper->GetBankInstallment("", get_option('api_app_secret_key'), get_option('app_id'), get_option('url_api_key'), get_option('mc_auth_user'), get_option('mc_auth_pass'));
                    $listBanks = $listBankInstallment['data'];
                    // print_r($listBanks); die();
                    do_action('woocommerce_credit_payment_payon_start', $this->id);
                    //load views
                    require_once plugin_dir_path(__FILE__) . 'view-installment.php';
                    do_action('woocommerce_credit_payment_payon_end', $this->id);
                }
            } else {
                echo '<script>
                document.getElementById("payment_method_' . $this->id . '").checked = false;
                document.getElementById("payment_method_' . $this->id . '").disabled = true;
                document.getElementsByClassName("payment_method_' . $this->id . '")[0].style.display = "none";
                </script>';
            }
        }

        /*
         * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
         */
        public function payment_scripts()
        {
            // we need JavaScript to process a token only on cart/checkout pages, right?
            if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
                return;
            }

            // and this is our custom JS in your plugin directory that works with token.js
            wp_enqueue_script('jquery');
            // we need JavaScript to process a token only on cart/checkout pages, right?
            if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
                return;
            }

            if ('no' === $this->enabled) {
                return;
            }
            wp_register_style('ins_css', plugins_url('assets/css/installment.css', plugin_dir_path(__FILE__)));
            wp_enqueue_style('ins_css');
        }

        /*
         * Fields validation, more in Step 5
         */
        public function validate_fields()
        {
        }

        /*
         * Xử lý dữ liệu sau khi submit thông tin tạo đơn hàng
         */
        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);
            $order_data = $order->get_data();

            if (get_option('api_app_secret_key') && get_option('app_id') && get_option('url_api_key') && get_option('mc_auth_user') && get_option('mc_auth_pass')) {
                include_once(plugin_dir_path(__FILE__) . "PayonHelper.php");
                $payonhelper = new PayonHelper();

                $bank_code = get_post_meta($order_id, 'bank_code', true);;
                $merchant_request_id = get_post_meta($order_id, 'merchant_request_id', true);;
                $cycle = get_post_meta($order_id, 'cycle', true);;
                $card_type = get_post_meta($order_id, 'card_type', true);;
                foreach ($order_data['meta_data'] as $meta) {
                    if ($meta->key == 'merchant_request_id') {
                        $merchant_request_id = $meta->value;
                    }
                    if ($meta->key == 'bank_code') {
                        $bank_code = $meta->value;
                    }

                    if ($meta->key == 'cycle') {
                        $cycle = $meta->value;
                    }

                    if ($meta->key == 'card_type') {
                        $card_type = $meta->value;
                    }
                }

                if (empty($merchant_request_id)) {
                    $merchant_request_id = 'PAYON_' . $order_id . '_' . date('YmdHis');
                    update_post_meta($order_id, 'merchant_request_id', sanitize_text_field($merchant_request_id));
                }
                if ($order_data['payment_method'] = 'installment') {

                    $data = [
                        "merchant_id" => (int) get_option('mc_id'),
                        "merchant_request_id" => $merchant_request_id,
                        "amount" => (int) $order->get_total(),
                        'bank_code' => $bank_code,
                        'cycle' => (int) $cycle,
                        'card_type' => $card_type,
                        'userfee' => (int) (($this->get_option('user_fee') == 'yes') ? 1 : 2),
                        "description" => 'Thanh toán đơn hàng ' . $order_id . ' KH ' . $order_data['billing']['first_name'] . " " . $order_data['billing']['last_name'],
                        "url_redirect" => get_rest_url(null, "/payon/checkout?order_id=" . $order_id),
                        "url_notify" => get_rest_url(null, "/payon/notification"),
                        "url_cancel" => $order->get_cancel_order_url_raw(),
                        "customer_fullname" => $order_data['billing']['first_name'] . " " . $order_data['billing']['last_name'],
                        "customer_email" => $order_data['billing']['email'],
                        "customer_mobile" => $order_data['billing']['phone'],
                    ];
                    $resultCreateOrder = $payonhelper->createOrderInstallment($data, get_option('api_app_secret_key'), get_option('app_id'), get_option('url_api_key'), get_option('mc_auth_user'), get_option('mc_auth_pass'));

                    $data['payment_token'] = $resultCreateOrder['data']['payment_token'];
                    update_post_meta($order_id, 'payment_token', sanitize_text_field($data['payment_token']));
                    $data['payment_id'] = $resultCreateOrder['data']['payment_id'];
                    update_post_meta($order_id, 'payment_id', sanitize_text_field($data['payment_id']));

                    if ($resultCreateOrder['error_code'] == "00") {
                        $urlCheckout = $resultCreateOrder['data']['url_checkout'];
                        return array(
                            'result' => 'success',
                            'redirect' => $urlCheckout
                        );
                    } else {
                        echo 'Thanh toán đã xảy ra lỗi, Vui lòng thử lại sau.';
                    }
                }
            } else {
                echo 'Vui lòng cài đặt cấu hình thanh toán';
            }
        }
    }
}

add_action('woocommerce_after_add_to_cart_button', 'cmk_additional_button');
function cmk_additional_button()
{
    $productID = get_the_ID();
    $product = wc_get_product(get_the_ID());

    $enabled_ins = get_option('enabled_ins') ? get_option('enabled_ins') : 'no';
    $show_button = get_option('show_button') ? get_option('show_button') : 'no';
    $installment_amount = (get_option('installment_amount') && get_option('installment_amount') >= 3000000) ? get_option('installment_amount') : 3000000;
    if (isset($productID) && $product->get_price() > (int) $installment_amount && $enabled_ins == 'yes' && $show_button == 'yes') {
        wp_register_style('product_css', plugins_url('assets/css/installment.css', plugin_dir_path(__FILE__)));
        wp_enqueue_style('product_css');
        echo '<a href=" ' . esc_url(home_url("/thanh-toan-tra-gop?product-id=" . $productID)) . ' " class="button-tragop button"> TRẢ GÓP 0% QUA THẺ </a>';
    }
}


//Kiểm tra Thanh toán
function checkout_tragop()
{
    if (sanitize_text_field($_GET['product-id']) > 0) {
        $product = wc_get_product(sanitize_text_field($_GET['product-id']));
        $enabled_ins = get_option('enabled_ins') ? get_option('enabled_ins') : 'no';
        $installment_amount = (get_option('installment_amount') && get_option('installment_amount') >= 3000000) ? get_option('installment_amount') : 3000000;
        if (isset($product) && !empty($product) && $product->get_price() > (int) $installment_amount && $enabled_ins == 'yes') {
            $api_app_secret_key = get_option('api_app_secret_key');
            $app_id = get_option('app_id');
            $url_api_key = get_option('url_api_key');
            $mc_auth_user = get_option('mc_auth_user');
            $mc_auth_pass = get_option('mc_auth_pass');
            include_once(plugin_dir_path(__FILE__) . "PayonHelper.php");
            $payonhelper = new PayonHelper();
            // get bank installment
            $listBankInstallment = $payonhelper->GetBankInstallment("", $api_app_secret_key, $app_id, $url_api_key, $mc_auth_user, $mc_auth_pass);
            $listBanks = $listBankInstallment['data'];

            wp_register_style('ins_css', plugins_url('assets/css/installment.css', plugin_dir_path(__FILE__)));
            wp_enqueue_style('ins_css');

            $dv = get_woocommerce_currency_symbol();
            $pri_all = $product->get_price();
            $pri_10 = ($product->get_price()) - $product->get_price() * 10 / 100;
            $pri_20 = ($product->get_price()) - $product->get_price() * 20 / 100;
            $pri_30 = ($product->get_price()) - $product->get_price() * 30 / 100;
            $pri_40 = ($product->get_price()) - $product->get_price() * 40 / 100;
            $pri_50 = ($product->get_price()) - $product->get_price() * 50 / 100;
            $pri_60 = ($product->get_price()) - $product->get_price() * 60 / 100;
            $pri_70 = ($product->get_price()) - $product->get_price() * 70 / 100;
            require_once plugin_dir_path(__FILE__) . 'checkout.php';
        } else {
            echo 'Vui lòng quay lại <a href="' . esc_url(home_url()) . '">Trang chủ</a>';
        }
    } else {
        echo 'Vui lòng quay lại <a href="' . esc_url(home_url()) . '">Trang chủ</a>';
    }
}

add_shortcode('checkout_tragop', 'checkout_tragop');

add_action('rest_api_init', function () {
    register_rest_route('payon', '/notification', array(
        'methods' => 'POST',
        'callback' => 'cuspost',
        'permission_callback' => '__return_true'
    ));
});

/**
 * Nhận notify do PAYON trả về
 * 
 */
function cuspost(WP_REST_Request $req)
{
    global $wpdb;
    $api_app_secret_key = get_option('api_app_secret_key');
    $app_id = get_option('app_id');
    $url_api_key = get_option('url_api_key');
    $mc_auth_user = get_option('mc_auth_user');
    $mc_auth_pass = get_option('mc_auth_pass');

    $parameters = json_decode($req->get_body());
    $merchant_request_id = $parameters->data->merchant_request_id;
    $checksum = md5($app_id . json_encode($parameters->data) . $api_app_secret_key);
    if ($checksum == $parameters->checksum) {
        $post_meta = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE `meta_value` = %s", $merchant_request_id));
        if ($post_meta) {
            include_once(plugin_dir_path(__FILE__) . "PayonHelper.php");
            $payonhelper = new PayonHelper();

            $checkpayment = $payonhelper->CheckPayment($merchant_request_id, $api_app_secret_key, $app_id, $url_api_key, $mc_auth_user, $mc_auth_pass);

            $order = wc_get_order($post_meta->post_id);
            if ($checkpayment['error_code'] == "00" && $checkpayment['data']['status'] == 2) {
                //Thanh toán thành công
                $order->set_status('payed');
                $order->save();
            } else {
                //Thanh toán thất bại
                $order->set_status('failded');
                $order->save();
            }
            update_post_meta($post_meta->post_id, 'notify', sanitize_text_field($req->get_body()));
        }
        $output = [
            'code' => 200,
            'message' => 'Thanh toán thành công!',
            'data' => null
        ];
    } else {

        update_post_meta($post_meta->post_id, 'notify', sanitize_text_field($checksum . $parameters->data));
        $output = [
            'code' => 400,
            'message' => 'Dữ liệu không chính xác',
            'data' => $checksum,
            'checksum' => $parameters->data
        ];
    }
    wp_send_json($output);
}

add_filter('woocommerce_order_button_text', 'wc_custom_order_button_text');
/**
 * Đổi tên nút thanh toán
 * 
 */
function wc_custom_order_button_text()
{
    return __('Thanh toán', 'woocommerce');
}

/**
 * Lưu thêm data khi thực hiện tạo đơn hàng
 * 
 */
add_action('woocommerce_checkout_update_order_meta', 'saving_checkout_cf_data', 10, 1);
function saving_checkout_cf_data($order_id)
{
    $payment_method = sanitize_text_field($_POST['payment_method']);
    if (!empty($payment_method)) {
        $merchant_request_id = sanitize_text_field('PAYON_' . $order_id . '_' . date('YmdHis'));
        update_meta_test($order_id, 'merchant_request_id', $merchant_request_id);
        
        if ($payment_method == 'payon_paynow_v2') {
            $service_code = sanitize_text_field($_POST['service_code']);
            update_meta_test($order_id, 'service_code', $service_code);
            
            $method_code = sanitize_text_field($_POST['method_code']);
            update_meta_test($order_id, 'method_code', $method_code);
            $bank_code = sanitize_text_field($_POST['paynow_bank']);
            update_meta_test($order_id, 'bank_code', $bank_code);
        }
        if ($payment_method == 'installment') {

            $cycle = sanitize_text_field($_POST['payon_cycle']);
            update_meta_test($order_id, 'cycle', $cycle);
            $card_type = sanitize_text_field(strtoupper($_POST['payon_card']));
            update_meta_test($order_id, 'card_type', $card_type);
            
            $bank_code = sanitize_text_field((($_POST['paynow_bank']) ? $_POST['paynow_bank'] : $_POST['payon_bank']));
            update_meta_test($order_id, 'bank_code', $bank_code);
            
            $fee_by_month = sanitize_text_field($_POST['fee-by-month']);
            update_meta_test($order_id, 'fee_by_month', $fee_by_month);
            
            $total_installment = sanitize_text_field($_POST['total-installment']);
            update_meta_test($order_id, 'total_installment', $total_installment);
            
            $money_different = sanitize_text_field($_POST['money-different']);
            update_meta_test($order_id, 'money_different', $money_different);
            
        }
    }
}
function update_meta_test($order_id, $key, $value)
{
    $existing_merchant_request_id = get_post_meta($order_id, $key, true);
    if ($existing_merchant_request_id === '') {
        // If the 'money_different' meta key doesn't exist, add it
        return add_post_meta($order_id, $key, $value, true);
        
    } else {
        global $wpdb;
        return  $wpdb->update(
            $wpdb->postmeta,
            array('meta_value' => sanitize_text_field($value)),
            array('post_id' => $order_id, 'meta_key' => $key)
        );
    }
}

add_action('rest_api_init', function () {
    register_rest_route('payon', '/checkout', array(
        'methods' => 'GET',
        'callback' => 'checkoutpayon',
        'permission_callback' => '__return_true'
    ));
});
/**
 * Nhận Redirect từ PAYON điều hướng đến trang order_received_url
 */
function checkoutpayon()
{
    global $wpdb;
    $get = sanitize_text_field($_GET['order_id']);
    $param = explode("/", $get);
    $post_meta = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE `meta_value` = %s", $param[1]));
    // Empty cart
    if (WC()->cart) {
        WC()->cart->empty_cart();
    }
    if ((int) $post_meta->post_id == (int) $param[0]) {
        $order = wc_get_order($post_meta->post_id);
        include(plugin_dir_path(__FILE__) . "PayonHelper.php");
        $payonhelper = new PayonHelper();
        $merchant = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE `meta_key` = %s and `post_id` = %d", ['merchant_request_id', $post_meta->post_id]));
        $api_app_secret_key = get_option('api_app_secret_key');
        $app_id = get_option('app_id');
        $url_api_key = get_option('url_api_key');
        $mc_auth_user = get_option('mc_auth_user');
        $mc_auth_pass = get_option('mc_auth_pass');

        $checkpayment = $payonhelper->CheckPayment($merchant->meta_value, $api_app_secret_key, $app_id, $url_api_key, $mc_auth_user, $mc_auth_pass);
        if ($checkpayment['error_code'] == "00" && $checkpayment['data']['status'] == 2) {
            //Thanh toán thành công
            $order->set_status('payed');
            $order->save();
        } else {
            //Thanh toán thất bại
            $order->set_status('failded');
            $order->save();
        }
        wp_redirect($order->get_checkout_order_received_url());
        exit;
    } else {
        $order = wc_get_order($param[0]);
        if ($order) {
            wp_redirect($order->get_checkout_order_received_url());
        } else {
            wp_redirect(home_url());
        }
        exit;
    }
}

add_action('rest_api_init', function () {
    register_rest_route('payon', '/fee', array(
        'methods' => 'POST',
        'callback' => 'fee',
        'permission_callback' => '__return_true'
    ));
});

/**
 * Lấy thông tin trả góp
 * 
 */
function fee(WP_REST_Request $req)
{
    $payonCycle = sanitize_text_field($_POST['payon_cycle']);
    $data = [
        'merchant_id' => (int) get_option('mc_id'),
        'amount' => (int) sanitize_text_field($_POST['amount']),
        'bank_code' => sanitize_text_field($_POST['payon_bank']),
        'cycles' => json_decode($payonCycle, true),
        'card_type' => sanitize_text_field(strtoupper($_POST['payon_card']))
    ];
    include_once(plugin_dir_path(__FILE__) . "PayonHelper.php");
    $payonhelper = new PayonHelper();

    $api_app_secret_key = get_option('api_app_secret_key');
    $app_id = get_option('app_id');
    $url_api_key = get_option('url_api_key');
    $mc_auth_user = get_option('mc_auth_user');
    $mc_auth_pass = get_option('mc_auth_pass');

    $fee = $payonhelper->getFee($data, $api_app_secret_key, $app_id, $url_api_key, $mc_auth_user, $mc_auth_pass);
    // return $fee;
    $month = [];
    $feeByMonth = [];
    $totalInstallment = [];
    $different = [];
    $dv = get_woocommerce_currency_symbol();
    foreach ($fee['data'] as $key => $val) {
        $totalInstallmentAmount = $val['amount_payment'] + ((get_option('user_fee') == 'yes') ? $val['fee'] : 0);
        $differentAmount = $totalInstallmentAmount - $data['amount'];
        $month[] = $key;
        $totalInstallment['m' . $key] = number_format($totalInstallmentAmount, 0, ",", ".") . $dv;
        $different['m' . $key] = number_format($differentAmount, 0, ",", ".") . $dv;
        $feeByMonth['m' . $key] = number_format(round($totalInstallmentAmount / $key, 0), 0, ",", ".") . $dv;
    }
    $output = [
        'code' => 200,
        'message' => 'Thanh toán thành công!',
        'month' => $month,
        'feeByMonth' => $feeByMonth,
        'totalInstallment' => $totalInstallment,
        'different' => $different,
        'dv' => get_woocommerce_currency_symbol()
    ];
    wp_send_json($output);
}

add_action('rest_api_init', function () {
    register_rest_route('payon', '/create-order', array(
        'methods' => 'POST',
        'callback' => 'createOrder',
        'permission_callback' => '__return_true'
    ));
});

/**
 * Lấy thông tin trả góp
 * 
 */
function createOrder(WP_REST_Request $req)
{
    $order = wc_create_order();

    // add products
    $order->add_product(wc_get_product(sanitize_text_field($_POST['product_id'])), 1);

    // add shipping
    $shipping = new WC_Order_Item_Shipping();
    $shipping->set_method_title('Free shipping');
    $shipping->set_method_id('free_shipping:1'); // set an existing Shipping method ID
    $shipping->set_total(0); // optional
    $order->add_item($shipping);

    // add billing and shipping addresses
    $address = array(
        'first_name' => sanitize_text_field($_POST['fullname']),
        'last_name' => '',
        'company' => '',
        'email' => sanitize_text_field($_POST['email']),
        'phone' => sanitize_text_field($_POST['phone']),
        'address_1' => sanitize_text_field($_POST['address']),
        'address_2' => '',
        'city' => '',
        'state' => '',
        'postcode' => '',
        'country' => 'VN'
    );

    $order->set_address($address, 'billing');
    $order->set_address($address, 'shipping');

    // add payment method
    $order->set_payment_method('installment');
    $order->set_payment_method_title('Trả góp 0% qua PayOn');

    // order status
    $order->set_status('wc-pending', 'Order is created programmatically');

    // calculate and save
    $order->calculate_totals();
    $order->save();
    $dv = get_woocommerce_currency_symbol();
    $order_id = $order->get_id();
    $amount_tra_truoc = (int) $order->get_total() * (int) sanitize_text_field($_POST['pripaid']) / 100;
    $amount_tra_truoc = number_format($amount_tra_truoc, 0, ",", ".") . $dv;
    $cycle = sanitize_text_field($_POST['payon_cycle']);
    $card_type = sanitize_text_field(strtoupper($_POST['payon_card']));
    $bank_code = sanitize_text_field($_POST['payon_bank']);
    $fee_by_month = sanitize_text_field($_POST['fee_by_month']);
    $total_installment = sanitize_text_field($_POST['total_installment']);
    $money_different = sanitize_text_field($_POST['money_different']);
    update_post_meta($order_id, 'cycle', sanitize_text_field($cycle));
    update_post_meta($order_id, 'card_type', sanitize_text_field($card_type));
    update_post_meta($order_id, 'bank_code', sanitize_text_field($bank_code));
    update_post_meta($order_id, 'fee_by_month', sanitize_text_field($fee_by_month));
    update_post_meta($order_id, 'total_installment', sanitize_text_field($total_installment));
    update_post_meta($order_id, 'so_tien_tra_truoc', sanitize_text_field($amount_tra_truoc));
    update_post_meta($order_id, 'money_different', sanitize_text_field($money_different));
    $merchant_request_id = 'PAYON_' . $order_id . '_' . date('YmdHis');
    update_post_meta($order_id, 'merchant_request_id', sanitize_text_field($merchant_request_id));

    if (get_option('api_app_secret_key') && get_option('app_id') && get_option('url_api_key') && get_option('mc_auth_user') && get_option('mc_auth_pass')) {
        include_once(plugin_dir_path(__FILE__) . "PayonHelper.php");
        $payonhelper = new PayonHelper();
        $amount = (int) $order->get_total() - (int) $order->get_total() * (int) sanitize_text_field($_POST['pripaid']) / 100;
        $data = [
            "merchant_id" => (int) get_option('mc_id'),
            "merchant_request_id" => $merchant_request_id,
            "amount" => (int) $amount,
            'bank_code' => $bank_code,
            'cycle' => (int) $cycle,
            'card_type' => $card_type,
            'userfee' => (int) ((get_option('user_fee') == 'yes') ? 1 : 2),
            "description" => 'Thanh toán đơn hàng ' . $order_id . ' KH ' . $order_data['billing']['first_name'] . " " . $order_data['billing']['last_name'],
            "url_redirect" => get_rest_url(null, "/payon/checkout?order_id=" . $order_id),
            "url_notify" => get_rest_url(null, "/payon/notification"),
            "url_cancel" => sanitize_url(home_url() . "/thanh-toan-tra-gop?product-id=" . $_POST['product_id']),
            "customer_fullname" => sanitize_text_field($_POST['fullname']),
            "customer_email" => sanitize_text_field($_POST['email']),
            "customer_mobile" => sanitize_text_field($_POST['phone']),
        ];
        $resultCreateOrder = $payonhelper->createOrderInstallment($data, get_option('api_app_secret_key'), get_option('app_id'), get_option('url_api_key'), get_option('mc_auth_user'), get_option('mc_auth_pass'));

        $data['payment_token'] = $resultCreateOrder['data']['payment_token'];
        update_post_meta($order_id, 'payment_token', sanitize_text_field($data['payment_token']));
        $data['payment_id'] = $resultCreateOrder['data']['payment_id'];
        update_post_meta($order_id, 'payment_id', sanitize_text_field($data['payment_id']));

        if ($resultCreateOrder['error_code'] == "00") {
            $urlCheckout = $resultCreateOrder['data']['url_checkout'];

            $output = [
                'code' => 200,
                'message' => 'Tạo link thành công',
                'data' => $urlCheckout
            ];
        } else {
            $output = [
                'code' => 400,
                'message' => 'Tạo link thất bại',
                'data' => null
            ];
        }
    } else {
        $output = [
            'code' => 400,
            'message' => 'Chưa cấu hình phương thức thanh toán',
            'data' => null
        ];
    }
    wp_send_json($output);
}

add_action('woocommerce_admin_order_data_after_billing_address', 'payon_editable_order_meta_billing');

function payon_editable_order_meta_billing($order)
{

    $contactmethod = $order->get_payment_method();
    if ($contactmethod == 'installment'):
        ?>
        <div>
            <h3>Thông tin thanh toán :</h3>
            <?php
            $bank_code = $order->get_meta('bank_code');
            $card_type = $order->get_meta('card_type');
            $cycle = $order->get_meta('cycle');
            $so_tien_tra_truoc = $order->get_meta('so_tien_tra_truoc');
            $total_installment = $order->get_meta('total_installment');
            ?>
            <p><strong>Hình thức thanh toán:</strong> Trả góp</p>
            <p><strong>Ngân hàng:</strong>
                <?php echo esc_html($bank_code) ?>
            </p>
            <p><strong>Loại thẻ:</strong>
                <?php echo esc_html($card_type) ?>
            </p>
            <p><strong>Kỳ trả góp:</strong>
                <?php echo esc_html($cycle) ?> Tháng
            </p>
            <p><strong>Số tiền đã thanh toán:</strong>
                <?php echo esc_html($total_installment) ?>
            </p>
            <p><strong>Số tiền thanh toán khi nhận hàng:</strong>
                <?php echo esc_html($so_tien_tra_truoc) ?>
            </p>
        </div>
    <?php else: ?>
        <!-- <p><strong>Hình thức thanh toán:</strong> Checkout qua PayOn</p> -->
    <?php endif; ?>
<?php
}
