## Unofficial Billdesk PHP SDK
This is an unofficial PHP SDK for the BillDesk Payment Gateway. This is the newer implimentation of v2 that the billdesk is promoting. It is a work in progress and is not yet ready for production use.

## Contributing
Pull requests are more than welcome. Since I am using Orders, I have created the Order Class. You are more then welcome to create other classes for other things including Refunds, etc.

## Installation
```bash
composer require imlolman/billdesk-php-sdk
```

## Usage

### Create an Order
- Setup .env file before proceeding from the example.env file
```php
<?php

require 'vendor/autoload.php';

use BillDesk\BillDesk;
use BillDesk\APIs\Orders;
use Dotenv\Dotenv;

$mode = "DEV"; // DEV or PROD

# Load the env
if ($mode === 'PROD') {
    $dotenv = Dotenv::createImmutable(__DIR__, '.env_prod');
} else {
    $dotenv = Dotenv::createImmutable(__DIR__);
}

$dotenv->load();

$billDesk = BillDesk::init([
    "MERCHANT_ID" => $_ENV['MERCHANT_ID'],
    "CLIENT_ID" => $_ENV['CLIENT_ID'],
    "SECRET_KEY" => $_ENV['SECRET_KEY'],
    "RETURN_URL" => $_ENV['RETURN_URL'],
    "MODE" => $_ENV['MODE'],
]);

$orders = new Orders();
$order_id = uniqid(); // Your Order ID Here
$order = $orders->createOrder($order_id, "1.00");

# If you want to use full page HTML, else use the below code
$fullpage_html = $orders->getFullPageHTML($order, [
    "prefs" => [
        "payment_categories" => ["qr", "upi", "card", "nb"]
    ],
],[
    "merchantLogo" => "data:image/png;base64," // Base64 Encoded Image
]);
echo $fullpage_html;

# You can also use the below code to get the popup scripts and inject it into your page Manually 
$popup_script = $orders->getPopupScripts($order, [
    "prefs" => [
        "payment_categories" => ["qr", "upi", "card", "nb"]
    ],
],[
    "merchantLogo" => "data:image/png;base64," // Base64 Encoded Image
]);
echo $popup_script;
```

- Validating a Transaction from webhook callback
```php
<?php

require 'vendor/autoload.php';

use BillDesk\BillDesk;
use BillDesk\APIs\Orders;
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$billDesk = BillDesk::init([
    "MERCHANT_ID" => $_ENV['MERCHANT_ID'],
    "CLIENT_ID" => $_ENV['CLIENT_ID'],
    "SECRET_KEY" => $_ENV['SECRET_KEY'],
    "RETURN_URL" => $_ENV['RETURN_URL'],
    "MODE" => $_ENV['MODE'], // DEV or PROD
]);

try{
    $orders = new Orders();
    $order = $orders->validateOrder();

    // Do something with the order, since it is validated.
    // Below is the order structure.
    //  array:22 [▼
    //     "mercid" => "YOUR MERCHENT ID HERE"
    //     "transaction_date" => "2023-10-10T16:31:08+05:30"
    //     "surcharge" => "0.00"
    //     "payment_method_type" => "card"
    //     "amount" => "1.00"
    //     "ru" => "https://example.com/return.php"
    //     "orderid" => "65252ec886812" // Your Order ID Here
    //     "transaction_error_type" => "success"
    //     "discount" => "0.00"
    //     "payment_category" => "02"
    //     "bank_ref_no" => "2CZF75L15L"
    //     "transactionid" => "UHMP0010711945"
    //     "txn_process_type" => "3ds"
    //     "bankid" => "HMP"
    //     "additional_info" => array:10 [▼
    //       "additional_info7" => "NA"
    //       "additional_info6" => "NA"
    //       "additional_info9" => "NA"
    //       "additional_info8" => "NA"
    //       "additional_info10" => "NA"
    //       "additional_info1" => "65252ec886812" // Your Order ID Here
    //       "additional_info3" => "NA"
    //       "additional_info2" => "NA"
    //       "additional_info5" => "NA"
    //       "additional_info4" => "NA"
    //     ]
    //     "itemcode" => "DIRECT"
    //     "transaction_error_code" => "TRS0000"
    //     "currency" => "356" // FOR INR
    //     "auth_status" => "0300"
    //     "transaction_error_desc" => "Transaction Successful"
    //     "objectid" => "transaction"
    //     "charge_amount" => "1.00"
    // ]
}catch(\Exception $e){
    echo $e->getMessage();
    die();
}
```