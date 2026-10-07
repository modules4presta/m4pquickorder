<?php

/**
 * m4pquickorder
 *
 * @author    Modules4Presta <contact@modules4presta.io>
 * @copyright 2026 Nice Code sp. z o.o. (Modules4Presta)
 * @license   https://opensource.org/licenses/MIT MIT License
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
        $this->author = 'Modules4Presta';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Quick Order', [], 'Modules.M4pquickorder.Admin');
        $this->description = $this->trans('Provides a quick order form for selected products.', [], 'Modules.M4pquickorder.Admin');
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => _PS_VERSION_];
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

            $output .= $this->displayConfirmation($this->trans('Settings updated', [], 'Modules.M4pquickorder.Admin'));
        }

        $fields_form = [
            'form' => [
                'legend' => ['title' => $this->trans('Quick Order Settings', [], 'Modules.M4pquickorder.Admin')],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Enable Quick Order', [], 'Modules.M4pquickorder.Admin'),
                        'name' => 'QUICKORDER_ENABLED',
                        'values' => [
                            ['id' => 'on','value' => 1,'label' => $this->trans('Enabled', [], 'Modules.M4pquickorder.Admin')],
                            ['id' => 'off','value' => 0,'label' => $this->trans('Disabled', [], 'Modules.M4pquickorder.Admin')]
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->trans('Select Products', [], 'Modules.M4pquickorder.Admin'),
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
                'submit' => ['title' => $this->trans('Save', [], 'Modules.M4pquickorder.Admin')],
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

        $controller = $this->context->controller;
        if (method_exists($controller, 'addJqueryPlugin')) {
            $controller->addJqueryPlugin('select2');
        }
        if (method_exists($controller, 'addJS')) {
            $controller->addJS($this->_path . 'views/js/quickorder-admin.js');
        }

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

    protected function formatPrice($price)
    {
        return $this->context->currentLocale->formatPrice(
            (float) $price,
            $this->context->currency->iso_code
        );
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
                    'price_old' => $this->formatPrice($priceOld),
                    'price_new' => $this->formatPrice($priceNew),
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
                'price_old' => $this->formatPrice($priceOldDef),
                'price_new' => $this->formatPrice($priceNewDef),
                'has_discount' => $priceOldDef > $priceNewDef,
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
                    'token' => Tools::getToken(false),
                ]
            ),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/quickorder_home.tpl');
    }
}
