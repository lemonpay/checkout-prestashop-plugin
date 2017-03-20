<?php
/**
 * LEMONPAY
 *
 */
include dirname(__FILE__) . '/../../config/config.inc.php';
include dirname(__FILE__) . '/../../init.php';
include dirname(__FILE__) . '/lemonpay.php';

$lemonpay = new Lemonpay();

if ($_GET['a'] == 'success') {
    $lemonpay->returnsuccess();
} else if ($_GET['a'] == 'failure') {
    $lemonpay->returnfailure();
} else {
    $lemonpay->validation();
}
