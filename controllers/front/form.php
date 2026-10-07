<?php

/**
 * m4pquickorder
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
 */

class M4pQuickOrderFormModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        $this->name = 'm4pquickorder';

        parent::__construct();
    }

    public function initContent()
    {
        if (
            empty(Tools::getValue('qty'))
            || empty(Tools::getValue('token'))
            || !Tools::getValue('token')
            || !hash_equals(Tools::getToken(false), (string) Tools::getValue('token'))
        ) {
            Tools::redirect('/');
        }

        $quantities = Tools::getValue('qty');
        $attrs = Tools::getValue('attr');
        foreach ($quantities as $id => $qty) {
            $qty = (int)$qty;
            if ($qty > 0) {
                $id_attr = isset($attrs[$id]) ? (int) $attrs[$id] : null;
                if (!$this->context->cart->updateQty($qty, (int) $id, $id_attr)) {
                    Tools::redirect('?quickorder_error=true#quickorder-block');
                }
            }
        }

        Tools::redirect('index.php?controller=order');
    }
}
