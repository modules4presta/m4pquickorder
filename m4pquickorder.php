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

if (!defined('_PS_VERSION_')) {
    exit;
}

class M4pQuickOrder extends Module
{
    public function __construct()
    {
        $this->name = 'm4pquickorder';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Modules4Presta.io';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Quick Order');
        $this->description = $this->l('Provides a quick order form for selected products.');
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayHome') &&
            Configuration::updateValue('QUICKORDER_PRODUCTS', '') &&
            Configuration::updateValue('QUICKORDER_ENABLED', 1);
    }

    public function uninstall()
    {
        Configuration::deleteByName('QUICKORDER_PRODUCTS');
        Configuration::deleteByName('QUICKORDER_ENABLED');

        return parent::uninstall();
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit($this->name . '_submit')) {
            Configuration::updateValue('QUICKORDER_ENABLED', (bool) Tools::getValue('QUICKORDER_ENABLED'));
            Configuration::updateValue('QUICKORDER_PRODUCTS', implode(',', Tools::getValue('QUICKORDER_PRODUCTS')));

            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        $fields_form = [
            'form' => [
                'legend' => ['title' => $this->l('Quick Order Settings')],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Quick Order'),
                        'name' => 'QUICKORDER_ENABLED',
                        'values' => [
                            ['id' => 'on','value' => 1,'label' => $this->l('Enabled')],
                            ['id' => 'off','value' => 0,'label' => $this->l('Disabled')]
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Select Products'),
                        'name' => 'QUICKORDER_PRODUCTS[]',
                        'multiple' => true,
                        'options' => [
                            'query' => $this->getProductOptions(),
                            'id' => 'id_product',
                            'name' => 'name',
                        ],
                        'class' => 'select2',
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->fields_value['QUICKORDER_ENABLED'] = Configuration::get('QUICKORDER_ENABLED');
        $helper->fields_value['QUICKORDER_PRODUCTS[]'] = explode(',', Configuration::get('QUICKORDER_PRODUCTS'));
        $helper->submit_action = $this->name . '_submit';

        $this->context->controller->addJquery();
        $this->context->controller->addJqueryPlugin('select2');
        $this->context->controller->addJS($this->_path . 'views/js/quickorder-admin.js');

        return $output . $helper->generateForm([$fields_form]);
    }

    protected function getProductOptions()
    {
        $products = Product::getProducts($this->context->language->id, 0, 0, 'name', 'ASC');
        $options = [];
        foreach ($products as $p) {
            $options[] = [
                'id_product' => $p['id_product'],
                'name' => $p['name']
            ];
        }

        return $options;
    }

    public function hookDisplayHome($params)
    {
        if (!Configuration::get('QUICKORDER_ENABLED')) {
            return;
        }

        $ids = array_filter(explode(',', Configuration::get('QUICKORDER_PRODUCTS')));
        if (empty($ids)) {
            return;
        }

        $products = [];
        foreach ($ids as $id) {
            $product = new Product((int)$id, true, $this->context->language->id);
            // skip if total quantity <=1
            if ((int)$product->getQuantity($product->id) <= 1) {
                continue;
            }

            // Get combinations with stock >1
            $combinations = [];
            $attrs = $product->getAttributeCombinations($this->context->language->id);
            foreach ($attrs as $attr) {
                $idAttr = $attr['id_product_attribute'];
                $stock = (int)StockAvailable::getQuantityAvailableByProduct($product->id, $idAttr);
                if ($stock <= 1) {
                    continue;
                }
                $label  = $attr['group_name'] . ' - ' . $attr['attribute_name'];
                $priceOld = (float)$product->getPrice(false, $idAttr);
                $priceNew = (float)$product->getPrice(true, $idAttr);
                $combinations[$idAttr] = [
                    'label'     => $label,
                    'price_old' => $priceOld,
                    'price_new' => $priceNew,
                    'stock'     => $stock,
                ];
            }

            // Cover image
            $cover = Product::getCover((int)$id);
            $coverUrl = $cover ? $this->context->link->getImageLink($product->link_rewrite, $cover['id_image'], 'home_default') : '';

            // Default product stock
            $stockDef = (int)StockAvailable::getQuantityAvailableByProduct($product->id, null);
            if ($stockDef <= 1) {
                continue;
            }

            // Default prices
            $priceOldDef = (float)$product->getPrice(false, null);
            $priceNewDef = (float)$product->getPrice(true, null);

            $products[] = [
                'product' => $product,
                'combinations' => $combinations,
                'cover' => $coverUrl,
                'price_old' => $priceOldDef,
                'price_new' => $priceNewDef,
                'stock' => $stockDef,
            ];
        }

        if (empty($products)) {
            return;
        }

        $moduleForLoginAccess = Module::getInstanceByName('m4ploginaccess');
        $hidePosibleToOrderForGuests = false;
        if (
            !empty($moduleForLoginAccess)
            && !empty($moduleForLoginAccess->active)
            && (bool) Configuration::get('M4PLOGINACCESS_HIDE_PRICES')
        ) {
            $hidePosibleToOrderForGuests = true;
        }

        $this->context->smarty->assign([
            'quickorder_error' => !empty(Tools::getValue('quickorder_error')) ? true : false,
            'hide_posible_to_order_for_guests' => $hidePosibleToOrderForGuests,
            'customer_logged' => !empty($this->context->customer->isLogged()) ? true : false,
            'quickorder_products' => $products,
            'form_action' => $this->context->link->getModuleLink(
                $this->name,
                'form',
                [
                    'token' => md5(Tools::getHttpHost(true) . $this->name)
                ]
            ),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/quickorder_home.tpl');
    }
}
