<?php

namespace BillDesk\APIs;

use BillDesk\BaseClass;

class Order extends BaseClass
{
    const POPUP_VIEW_PATH = __DIR__ . "/../../views/popup.php";
    const FULL_PAGE_VIEW_PATH = __DIR__ . "/../../views/fullpage.php";

    public function createOrder($order_id, $amount_in_inr, $order_date = "", $currency = "356", $additional_info = [], $itemcode = "DIRECT", $init_channel = "internet", $user_ip = "", $accept_header = "text/html", $useragent = "")
    {

        if ($order_date == "") {
            $order_date = date("Y-m-d\TH:i:sP");
        }

        if (count($additional_info) == 0) {
            $additional_info = [
                "additional_info1" => $order_id,
            ];
        }
        
        if ($user_ip == "") {
            $user_ip = $this->get_client_ip();
        }

        if ($useragent == "") {
            $useragent = $_SERVER['HTTP_USER_AGENT'];
        }

        $payload = [
            "mercid" => $this->config["MERCHANT_ID"],
            "orderid" => $order_id,  // A unique string for every request
            "amount" => $amount_in_inr,
            "order_date" => $order_date,
            "currency" => $currency,  // for INR
            "ru" => $this->config["RETURN_URL"],
            "additional_info" => $additional_info,
            "itemcode" => $itemcode,
            "device" => [
                "init_channel" => $init_channel,
                "ip" => $user_ip,  // Replace with your own IP logic if needed
                "accept_header" => $accept_header,
                "user_agent"=> $useragent
            ]
        ];

        return $this->executeRequest($payload);
    }

    public function getPopupScripts($order, $params = [], $ui_params = []): string
    {
        // Prepare variables for inclusion in the popup view
        $sdk_url = $this->config["BILLDESK_JSSDK_URL"];

        // Start output buffering
        ob_start();

        // Extract variables into the current symbol table
        // This makes $sdk_url available within the included PHP file
        extract(get_defined_vars());

        // Include the view file
        require self::POPUP_VIEW_PATH;

        // Get the contents of the output buffer and then clear it
        $popupHtml = ob_get_clean();

        return $popupHtml;
    }

    public function getFullPageHTML($order, $params = [], $ui_params = []): string
    {
        $scripts = $this->getPopupScripts($order, $params, $ui_params);

        // Start output buffering
        ob_start();

        // Extract variables into the current symbol table
        // This makes $sdk_url available within the included PHP file
        extract(get_defined_vars());

        // Include the view file
        require self::FULL_PAGE_VIEW_PATH;

        // Get the contents of the output buffer and then clear it
        $fullPageHtml = ob_get_clean();

        return $fullPageHtml;
    }

    public function validateOrder(){

        try{

            if(!isset($_POST["transaction_response"])){
                throw new \Exception("Transaction Response not found.");
            }

            $transaction_response = $this->decodePayload($_POST["transaction_response"]);

            if($transaction_response['transaction_error_type'] != "success"){
                throw new \Exception("Transaction Failed with error code '".$transaction_response['transaction_error_type']."': ". $transaction_response['transaction_error_desc'] .".");
            }

            return $transaction_response;
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

}