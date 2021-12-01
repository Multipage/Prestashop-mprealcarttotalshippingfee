<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Mprealcarttotalshippingfee extends Module
{
    /**
     * @var bool
     */
    protected $config_form = false;

    /**
     * @var array
     */
    public $_errors = array();


    public function __construct()
    {
        $this->name = 'mprealcarttotalshippingfee';
        $this->tab = 'shipping_logistics';
        $this->version = '0.0.1';
        $this->author = 'Vincent van Santen';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Real cart total for correct shipping fee');
        $this->description = $this->l('Gives the right shipping fee based on the real cart total ( real price with discounts) ');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * @return false
     */
    public function install()
    {
        //unlink old override on install
        @unlink(_PS_MODULE_DIR_ . $this->name . '/override/classes/Cart.php');

        //create overrides
        $cartsearch = array('if ($carrier->range_behavior) {', '$shipping_cost += $carrier->getDeliveryPriceByPrice($order_total');
        $cartreplace = array('$orderTotalwithDiscountsNoVirtual = $this->getOrderTotal(true, Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING);' . "\n" . 'if ($carrier->range_behavior) {', '$shipping_cost += $carrier->getDeliveryPriceByPrice($orderTotalwithDiscountsNoVirtual');
        if (!$this->patchClass("Cart", 'classes', null, array('getPackageShippingCost'), $cartsearch, $cartreplace)) {
            $this->_errors[] = $this->l('Patching of Core files failed');
            return false;
        }

        sleep(2);

        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        //Module is not configurable
        $output = "";
        return $output;
    }

    protected function renderForm()
    {
        //TODO
        return;
    }

    protected function getConfigForm()
    {
        //TODO
        return;
    }

    protected function getConfigFormValues()
    {
        //TODO
        return;
    }

    protected function postProcess()
    {
        //no form needed
        return;
    }


    //Patch and create class override on the fly!
    protected function patchClass($classname, $type, $classpath = null, $functions = array(), $search_for = array(), $replace_with = array())
    {
        if (empty($functions) || empty($search_for) || empty($replace_with)) {
            $this->_errors[] = "something is empty";
            return false;
        }

        //check if class exists and patch it !
        $filename = _PS_ROOT_DIR_ . "/classes/" . $classname . ".php";

        if (file_exists($filename)) {
            $lines = file($filename);
            $totallines = count($lines);
            $overridelines = "<?php \n /** auto patched */\n\nclass " . $classname . " extends " . $classname . "Core {\n\n";

            for ($ln = 0; $ln < $totallines; $ln++) {
                foreach ($functions as $funct) {
                    if (strpos($lines[$ln], 'function ' . $funct)) {
                        $overridelines .= "/** auto patched */\r\n";

                        //$lines[$ln] = str_replace('\r\n',$lines[$ln]);
                        $startags = 0;
                        $endtags = 0;
                        for ($lp = $ln; $lp < $totallines; $lp++) {
                            $foundreplacement = false;
                            if (strpos($lines[$lp], '{')) {
                                $startags++;
                            }
                            if (strpos($lines[$lp], '}')) {
                                $endtags++;
                            }
                            //preg_replace won't work - DON'T TRY IT 
                            foreach ($search_for as $key => $search) {
                                if (strpos($lines[$lp], $search) !== false) {
                                    $foundreplacement = true;
                                    $lines[$lp] = str_replace($search_for[$key], $replace_with[$key], $lines[$lp]);
                                }
                            }

                            $overridelines .= $lines[$lp];
                            if ($startags != 0 && $startags == $endtags) {
                                $ln = $lp;
                                $overridelines .= "\r\n\r\n";
                                break;
                            }
                        }
                    }
                }
            }
            $overridelines .= "}";

            if (!empty($type)) {

                if (!file_exists(__DIR__ . '/override/' . $type)) {
                    print "Create dir";
                    mkdir(__DIR__ . '/override/' . $type, 0755);
                }
            }

            if (!empty($classpath)) {
                if (!file_exists(__DIR__ . '/override/' . $type . '/' . $classpath)) {
                    mkdir(__DIR__ . '/override/' . $type . '/' . $classpath, 0755);
                }
            }

            if (!$fhandle = fopen(__DIR__ . '/override/' . $type . '/' . (!empty($classpath) ? $classpath . '/' : '') . $classname . '.php', 'w+')) {
                print "cannot open file";
            }
            fwrite($fhandle, $overridelines);
            fclose($fhandle);
        }
        return true;
    }
}
