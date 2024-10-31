<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit('Không có quyền truy cập');
}

include (plugin_dir_path(__FILE__) ."/PayonEncrypto.php");
class PayonHelper
{
    public $ref_code;
    public function __construct()
    {
        $this->ref_code = 'MCAPI-WPV1-'.sanitize_text_field( home_url());
    }

    /**
     * @param $param
     * @return mixed
     */
    function CreateOrderPaynow($param, $secret_key, $app_id, $url, $mc_auth, $mc_pass)
    {
        $data = $param;
        $data = json_encode($data);
        $crypto = new PayonEncrypto($secret_key);
        $data = $crypto->Encrypt($data);
        $checksum = md5($app_id . $data . $secret_key);
        $bodyPost = array(
            'app_id' => $app_id,
            'data' => $data,
            'checksum' => $checksum,
            'ref_code' => $this->ref_code
        );
        $result = $this->call($bodyPost, "createOrderPaynow", $url, $mc_auth, $mc_pass);
        return $result;
    }

    /**
     * @param $input
     * @return mixed
     */
    function CheckPayment($input, $secret_key, $app_id, $url, $mc_auth, $mc_pass)
    {
        $data = array(
            'merchant_request_id' => $input,
        );
        $data = json_encode($data);
        $crypto = new PayonEncrypto($secret_key);
        $data = $crypto->Encrypt($data);
        $checksum = md5($app_id . $data . $secret_key);
        $bodyPost = array(
            'app_id' => $app_id,
            'data' => $data,
            'checksum' => $checksum,
            'ref_code' => $this->ref_code
        );
        $result = $this->call($bodyPost, "checkPayment", $url, $mc_auth, $mc_pass);
        return $result;
    }

    /**
     * @param string $param
     * @return mixed
     */
    function GetBankInstallment($param = "", $secret_key, $app_id, $url, $mc_auth, $mc_pass)
    {
        $data = array();
        $data = json_encode($data);
        $crypto = new PayonEncrypto($secret_key);
        $data = $crypto->Encrypt($data);
        $checksum = md5($app_id . $data . $secret_key);
        $bodyPost = array(
            'app_id' => $app_id,
            'data' => $data,
            'checksum' => $checksum,
            'ref_code' => $this->ref_code
        );
        $result = $this->call($bodyPost, "getBankInstallmentV2", $url, $mc_auth, $mc_pass);
        return $result;
    }

    /**
     * @param $data
     * @return mixed
     */
    function getFee($data, $secret_key, $app_id, $url, $mc_auth, $mc_pass)
    {
        $data = json_encode($data);
        $crypto = new PayonEncrypto($secret_key);
        $data = $crypto->Encrypt($data);
        $checksum = md5($app_id . $data . $secret_key);
        $bodyPost = array(
            'app_id' => $app_id,
            'data' => $data,
            'checksum' => $checksum,
            'ref_code' => $this->ref_code
        );
        $result = $this->call($bodyPost, "getFeeInstallmentv2", $url, $mc_auth, $mc_pass);
        return $result;
    }

    /**
     * @param $data
     * @return mixed
     */
    function createOrderInstallment($data, $secret_key, $app_id, $url, $mc_auth, $mc_pass)
    {
        $data = json_encode($data);
        $crypto = new PayonEncrypto($secret_key);
        $data = $crypto->Encrypt($data);
        $checksum = md5($app_id . $data . $secret_key);
        $bodyPost = array(
            'app_id' => $app_id,
            'data' => $data,
            'checksum' => $checksum,
            'ref_code' => $this->ref_code
        );
        $result = $this->call($bodyPost, "createOrderInstallment", $url, $mc_auth, $mc_pass);
        return $result;
    }

    /**\
     * @param $params
     * @param $fnc
     * @return mixed
     */
    function Call($params, $fnc, $url, $mc_auth, $mc_pass)
    {
        if(substr( $url,-1) != '/'){
            $url = $url.'/';
        }
        $url = $url.$fnc;
        // $response = curl_exec($curl);
        $agent = sanitize_text_field($_SERVER["HTTP_USER_AGENT"]);
        if(empty($agent))
        {
            $agent = 'not user agent';
        }
        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $mc_auth  . ':' . $mc_pass ),
                'Content-Length' => strlen( json_encode($params) ),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ),
            'sslverify' => false,
            'user-agent'=> $agent,
            'method' => 'POST',
            'body'=>json_encode($params)
        );
        $response = wp_remote_request($url,$args);
        if(isset($response))
        {
            $data = $response['body'];
            $data = json_decode($data, true);
            if ($data['error_code'] == "00") {
                return $data;
            } else {
                echo 'Call Failed ';
            }
        } else{
            echo 'Hệ thống đang bận';
        }
    }
}