<?php
/**
 * LEMONPAY
 */
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Lemonpay extends PaymentModule
{
    public function __construct()
    {
        $this->name          = 'lemonpay';
        $this->tab           = 'payments_gateways';
        $this->version       = '1.7';
        $this->author        = 'LemonPay';
        $this->need_instance = 1;
        $this->bootstrap     = true;
        $this->module_key    = 'df879a424922284764d87e842f8a23s';

        $this->ps_versions_compliancy = array(
            'min' => '1.7',
            'max' => _PS_VERSION_,
        );

        parent::__construct();

        $this->displayName = 'LemonPay';
        $this->description = $this->l('Secure payement with LemonPay.');

    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook('paymentOptions') || !$this->registerHook('adminOrder') || !$this->registerHook('orderConfirmation')
        ) {
            return false;
        }

        return true;

    }

    public function hookPaymentOptions($params)
    {
        return $this->lemonpayPaymentOptions($params);
    }

    public function hookPaymentReturn($params)
    {
        $this->lemonpayPaymentReturnNew($params);
        return $this->display(dirname(__FILE__), '/tpl/order-confirmation.tpl');
    }

    public static function setOrderStatus($oid, $status)
    {
        $order_history           = new OrderHistory();
        $order_history->id_order = (int) $oid;
        $order_history->changeIdOrderState((int) $status, (int) $oid, true);
        $order_history->addWithemail(true);
    }

    public function hookOrderConfirmation($params)
    {
        if ($params['objOrder']->module != $this->name) {
            return false;
        }

        if ($params['objOrder'] && Validate::isLoadedObject($params['objOrder']) && isset($params['objOrder']->valid)) {

            if (version_compare(_PS_VERSION_, '1.5', '>=') && isset($params['objOrder']->reference)) {
                $this->smarty->assign('lemonpay_order', array('id' => $params['objOrder']->id, 'reference' => $params['objOrder']->reference, 'valid' => $params['objOrder']->valid));
            } else {
                $this->smarty->assign('lemonpay_order', array('id' => $params['objOrder']->id, 'valid' => $params['objOrder']->valid));
            }

            return $this->display(__FILE__, '/tpl/order-confirmation.tpl');
        }
    }

    public function returnsuccess()
    {
        $oid = $_GET['oid'];

        $payment_status             = '';
        Context::getContext()->cart = new Cart((int) $oid);
        $cart                       = new Cart((int) $oid);
        $total                      = ($cart->getOrderTotal());
        $url                        = $this->getUrl() . '/hash/' . $_COOKIE['lphash'];
        $LemonPay_Params            = array();
        $resArray                   = $this->api_call($url, $LemonPay_Params);
        if (($resArray['status'] == 'Processed')) {

            $this->validateOrder((int) $oid, (int) Configuration::get('LEMONPAY_OSID'), (float) ($total), $this->displayName, null, array(), null, false, $cart->secure_key);

        }

        if (_PS_VERSION_ < 1.5) {
            $redirect = __PS_BASE_URI__ . 'order-confirmation.php?id_cart=' . (int) $oid . '&id_module=' . (int) $this->id . '&id_order=' . (int) $this->currentOrder . '&key=' . $this->context->customer->secure_key;
        } else {
            $redirect = $this->context->shop->getBaseURL() . 'index.php?controller=order-confirmation&id_cart=' . (int) $oid . '&id_module=' . (int) $this->id . '&id_order=' . (int) $this->currentOrder . '&key=' . $this->context->customer->secure_key;
        }

        header('Location: ' . $redirect);
    }

    public function returnfailure()
    {
        $oid = $_GET['oid'];
        if (_PS_VERSION_ < 1.5) {
            $redirect = __PS_BASE_URI__ . 'order-confirmation.php?id_cart=' . (int) $this->context->cart->id . '&id_module=' . (int) $this->id . '&id_order=' . (int) $this->currentOrder . '&key=' . $this->context->customer->secure_key;
        } else {
            $redirect = $this->context->shop->getBaseURL() . 'index.php?controller=order-confirmation&id_cart=' . (int) $oid . '&id_module=' . (int) $this->id . '&id_order=' . (int) $this->currentOrder . '&key=' . $this->context->customer->secure_key;
        }

        header('Location: ' . $redirect);
    }

    protected function getfailureURL()
    {
        $url = $this->context->shop->getBaseURL() . 'modules/lemonpay/validation.php?a=failure&oid=' . $this->context->cart->id;
        return $url;
    }

    protected function getsuccessURL()
    {
        $url = $this->context->shop->getBaseURL() . 'modules/lemonpay/validation.php?a=success&oid=' . $this->context->cart->id;
        return $url;
    }

    public function validation()
    {

        if (!$this->active) {
            return;
        }

        $cart          = $this->context->cart;
        $customer      = new Customer($cart->id_customer);
        $daddress      = new Address($cart->id_address_delivery);
        $iaddress      = new Address($cart->id_address_invoice);
        $dcountry_code = Country::getIsoById($daddress->id_country);
        $icountry_code = Country::getIsoById($iaddress->id_country);
        $dstate        = new State((int) $daddress->id_state);
        $istate        = new State((int) $iaddress->id_state);
        $currency      = new Currency((int) ($cart->id_currency));
        $currency_code = $currency->iso_code;
        $total         = ($cart->getOrderTotal());
//        $currency_code = 'EUR';
        list($prefix, $phone_no) = explode(' ', $iaddress->phone);
        if (empty($prefix)) {
            list($prefix, $phone_no) = explode('-', $iaddress->phone);
        }

        if (empty($prefix)) {
            $prefix = Configuration::get('LEMONPAY_PREFIX');
        }

        if (empty($phone_no)) {
            $phone_no = $iaddress->phone;
        }

        if (substr($prefix, '0', 1) !== '+') {
            $prefix = '+' . $prefix;
        }

        $LemonPay_Params = array(
            'trustee_prefix' => $prefix,
            'trustee_phone'  => $phone_no,
            'name'           => 'Order ID-' . $cart->id . '-' . time(),
            'amount'         => $total,
            'currency'       => $currency_code,
            'source'         => Configuration::get('LEMONPAY_SLUG'),
            'settlor_prefix' => Configuration::get('LEMONPAY_PREFIX'),
            'settlor_phone'  => Configuration::get('LEMONPAY_PHONE'),
            'confirmed_url'  => $this->getsuccessURL(),
            'denied_url'     => $this->getfailureURL(),
        );

        $url      = $this->getUrl();
        $resArray = $this->api_call($url, $LemonPay_Params);

        $ack = 0;
        if (is_array($resArray) && array_key_exists('shortlink', $resArray)) {
            $redirect_url = $resArray['shortlink']['short_url'];
            $lphash       = $resArray['shortlink']['hash'];
            setcookie('lphash', $lphash);
        } else {
            $err_msg = '';
            foreach ($resArray['errors'] as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $errorMsg) {
                        $err_msg = $errorMsg;
                    }
                } else {
                    $err_msg = $val;
                }

            }
            Tools::redirect('index.php?controller=order&step=4&lemonpayerror=' . $err_msg);

        }

        Tools::redirect(Tools::safeOutput($redirect_url, ''));
        exit;

        return true;
    }

    public function api_call($rest_url, $LemonPay_Params)
    {
        $LemonPay_Qr = http_build_query($LemonPay_Params);

        $curl = curl_init(trim($rest_url));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (!empty($LemonPay_Qr)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $LemonPay_Qr);
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    /**
     * Uninstall and clean the module settings
     *
     * @return    bool
     */
    public function uninstall()
    {
        parent::uninstall();

        Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'module_country` WHERE `id_module` = ' . (int) $this->id);

        return (true);
    }

    public function getUrl()
    {
        $returl = (Configuration::get('LEMONPAY_MODE') == 'Y') ? 'https://pre-api.lemonpay.me/1.0/express' : 'https://api.lemonpay.me/1.0/express';
        return $returl;
    }

    public function getContent()
    {
        if (Tools::isSubmit('submit' . $this->name)) {

            $lemonpay_name = Tools::getValue('lemonpay_name');
            $saveOpt       = true;
            if (!empty($lemonpay_name)) {

                $LemonPay_Params = array(
                    'name'            => Tools::getValue('lemonpay_name'),
                    'slug'            => Tools::getValue('lemonpay_slug'),
                    'image'           => Tools::getValue('lemonpay_img'),
                    'primary_color'   => Tools::getValue('lemonpay_pcolor'),
                    'secondary_color' => Tools::getValue('lemonpay_scolor'),
                );

                $url      = $this->getUrl() . '/source';
                $resArray = $this->api_call($url, $LemonPay_Params);

                if (array_key_exists('errors', $resArray)) {
                    $saveOpt = false;
                    $err_msg = '';
                    foreach ($resArray['errors'] as $key => $val) {
                        if (is_array($val)) {
                            foreach ($val as $errorMsg) {
                                $err_msg = $errorMsg;
                            }
                        } else {
                            $err_msg = $val;
                        }
                    }

                }
            }

            if ($saveOpt) {
                Configuration::updateValue('LEMONPAY_PREFIX', pSQL(Tools::getValue('lemonpay_prefix')));
                Configuration::updateValue('LEMONPAY_PHONE', pSQL(Tools::getValue('lemonpay_phone')));
                Configuration::updateValue('LEMONPAY_SLUG', pSQL(Tools::getValue('lemonpay_slug')));
                Configuration::updateValue('LEMONPAY_MODE', pSQL(Tools::getValue('lemonpay_mode')));
                Configuration::updateValue('LEMONPAY_OSID', pSQL(Tools::getValue('lemonpay_order_status')));

                $html = '<div class="alert alert-success">' . $this->l('Configuration updated successfully') . '</div>';
            } else {
                $html = '<div class="alert alert-warning">' . $this->l($err_msg) . '</div>';
            }
        }

        $states = OrderState::getOrderStates((int) Configuration::get('PS_LANG_DEFAULT'));
        foreach ($states as $state) {
            $OrderStates[$state['id_order_state']] = $state['name'];
        }
        $lemonpay_slug = Configuration::get('LEMONPAY_SLUG');
        if (empty($lemonpay_slug)) {
            $lemonpay_slug = parse_url($this->context->shop->getBaseURL(), PHP_URL_HOST);
        }

        $data = array(
            'base_url'              => _PS_BASE_URL_ . __PS_BASE_URI__,
            'module_name'           => $this->name,
            'lemonpay_prefix'       => Configuration::get('LEMONPAY_PREFIX'),
            'lemonpay_phone'        => Configuration::get('LEMONPAY_PHONE'),
            'lemonpay_slug'         => Configuration::get('LEMONPAY_SLUG'),
            'lemonpay_slugval'      => $lemonpay_slug,
            'lemonpay_mode'         => Configuration::get('LEMONPAY_MODE'),
            'lemonpay_order_status' => Configuration::get('LEMONPAY_OSID'),
            'lemonpay_confirmation' => $html,
            'orderstates'           => $OrderStates,
        );

        $this->context->smarty->assign($data);
        $output = $this->display(__FILE__, 'tpl/admin.tpl');

        return $output;
    }

    //1.7

    public function lemonpayPaymentOptions($params)
    {

        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }
        $payment_options = [
            $this->lemonpayExternalPaymentOption(),
        ];
        return $payment_options;
    }

    public function checkCurrency($cart)
    {
        $currency_order    = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function lemonpayExternalPaymentOption()
    {
        $lang = Tools::strtolower($this->context->language->iso_code);
        if (isset($_GET['lemonpayerror'])) {
            $errmsg = $_GET['lemonpayerror'];
        }

        $this->context->smarty->assign(array(
            'module_dir' => $this->_path,
            'errmsg'     => $errmsg,
        ));
        $url       = $this->context->shop->getBaseURL() . 'modules/lemonpay/validation.php';
        $newOption = new PaymentOption();
        $newOption->setCallToActionText($this->l('Pay with LemonPay'))
            ->setAction($url)
            ->setAdditionalInformation($this->context->smarty->fetch('module:lemonpay/tpl/payment_infos.tpl'));

        return $newOption;
    }

    public function lemonpayPaymentReturnNew($params)
    {
        // Payement return for PS 1.7
        if ($this->active == false) {
            return;
        }
        $order = $params['order'];
        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }
        $this->smarty->assign(array(
            'id_order'     => $order->id,
            'reference'    => $order->reference,
            'params'       => $params,
            'total_to_pay' => Tools::displayPrice($order->total_paid, null, false),
            'shop_name'    => $this->context->shop->name,
        ));
        return $this->fetch('module:' . $this->name . '/tpl/order-confirmation.tpl');
    }

}
