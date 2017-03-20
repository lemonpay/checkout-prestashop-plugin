<?php
/**
 * LEMONPAY
 */

/* SSL Management */
$useSSL = true;

include dirname(__FILE__) . '/../../config/config.inc.php';
include dirname(__FILE__) . '/../../init.php';
include dirname(__FILE__) . '/lemonpay.php';

include dirname(__FILE__) . '/backward_compatibility/backward.php';

if (!Context::getContext()->customer) {
    Tools::redirect('index.php?controller=authentication&back=order.php');
}

$lemonpay = new LEMONPAY();
$lemonpay->payment();
