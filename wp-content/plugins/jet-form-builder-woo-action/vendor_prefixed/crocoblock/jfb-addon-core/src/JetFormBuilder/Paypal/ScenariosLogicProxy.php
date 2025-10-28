<?php

namespace JFB\WooComm\Vendor\JFBCore\JetFormBuilder\Paypal;

use JFB\WooComm\Vendor\JFBCore\JetFormBuilder\WithInit;
abstract class ScenariosLogicProxy
{
    use WithInit;
    public abstract function scenarios() : array;
    public function plugin_version_compare() : string
    {
        return '2.0.0';
    }
    public function on_plugin_init()
    {
        add_filter('jet-form-builder/gateways/paypal/scenarios-logic', array($this, 'register_scenarios'));
    }
    public function register_scenarios($logic)
    {
        $logic = \array_merge($logic, $this->scenarios());
        return $logic;
    }
}
