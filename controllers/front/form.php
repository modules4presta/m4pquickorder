<?php
/**
 * LICENCE
 *
 * ALL RIGHTS RESERVED.
 * YOU ARE NOT ALLOWED TO COPY/EDIT/SHARE/WHATEVER.
 *
 * IN CASE OF ANY PROBLEM CONTACT AUTHOR.
 *
 *  @author    Jan Kołodziej (contact@modules4presta.io)
 *  @copyright Modules4Presta.io
 *  @license   ALL RIGHTS RESERVED
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
            || Tools::getValue('token') != md5(Tools::getHttpHost(true) . $this->name)
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
