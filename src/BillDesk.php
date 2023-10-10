<?php

namespace BillDesk;

class BillDesk
{
    private static $instance;
    private $config;

    private function __construct($config)
    {
        $this->config = $config;
    }

    public static function init($config = null)
    {
        if (null === static::$instance) {
            if($config["MODE"] == "DEV") {
                $BillDeskURL = "https://uat1.billdesk.com/u2/payments/ve1_2/orders/create";
                $BillDeskJSSDKURL = "https://uat1.billdesk.com/merchant-uat/sdk/dist";
            }else{
                $BillDeskURL = "https://api.billdesk.com/payments/ve1_2/orders/create";
                $BillDeskJSSDKURL = "https://pay.billdesk.com/jssdk/v1/dist";
            }

            static::$instance = new static([
                "MERCHANT_ID" => $config["MERCHANT_ID"],
                "CLIENT_ID" => $config["CLIENT_ID"],
                "SECRET_KEY" => $config["SECRET_KEY"],
                "RETURN_URL" => $config["RETURN_URL"],
                "MODE" => $config["MODE"], // DEV or PROD
                "BILLDESK_URL" => $BillDeskURL,
                "BILLDESK_JSSDK_URL" => $BillDeskJSSDKURL
            ]);
        }

        return static::$instance;
    }

    public function getConfig()
    {
        return $this->config;
    }
}
