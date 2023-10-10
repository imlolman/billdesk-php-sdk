<script type="module" src="<?= $sdk_url ?>/billdesksdk/billdesksdk.esm.js"></script>
<script nomodule="" src="<?= $sdk_url ?>/billdesksdk.js"></script>
<link href="<?= $sdk_url ?>/billdesksdk/billdesksdk.css" rel="stylesheet">
<script>
    var flow_config = {
        merchantId: "<?php echo $order['links'][1]['parameters']['mercid'] ?>",
        bdOrderId: "<?php echo $order['links'][1]['parameters']['bdorderid'] ?>",
        authToken: "<?php echo $order['links'][1]['headers']['authorization'] ?>",
        returnUrl: "<?php echo $order['ru']; ?>",
        crossButtonHandling: 'Y',
        childWindow: false,
    };

    var params = <?php echo json_encode($params); ?>;
    flow_config = Object.assign(flow_config, params);

    var responseHandler = function (txn) {
        if (txn.response) {
            // Do Something..
        }
    };

    var config = {
        flowConfig: flow_config,
        flowType: "payments",
    };
    var ui_params = <?php echo json_encode($ui_params); ?>;
    config = Object.assign(config, ui_params);

    window.onload = function () {
        window.loadBillDeskSdk(config);
    };
</script>