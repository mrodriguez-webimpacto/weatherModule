<?php
/**
* 2007-2021 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class WeatherModule extends Module implements WidgetInterface
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'weatherModule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Manu rt';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('My weather module');
        $this->description = $this->l('A great module to display weather information.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        return parent::install() &&
        $this->registerHook('header') &&
        $this->registerHook('displayNav');
    }
    
    public function uninstall()
    {
        return parent::uninstall();
    }
    
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

//función para realizar llamadas a una API
    public function getAPIResponse($url)
        {
            $curlsesion = curl_init();
            curl_setopt($curlsesion, CURLOPT_URL, $url);
            curl_setopt($curlsesion, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curlsesion);
            curl_close($curlsesion);
            $APIresult = json_decode($response);
            return $APIresult;
        }
        //Añadimos la configuración al widget
    public function getWidgetVariables($hookName, array $configuration)
        {
            //Posición del usuario usando la clase Country
            $location = $this->context->country->name[Configuration::get('PS_LANG_DEFAULT')];
            //Llamado a la API de openweather añadiendo la variable de la localización
            $data = $this->getAPIResponse('https://openweathermap.org/data/2.5/weather?q='.$location.'&appid=72482ed3bb82ab72915e48fc103e59a2');
            if(data->cod == 200) {
                return array(
                    'temp' => (int)$res->main->temp,
                    'hum' => $res->main->humidity,
                    'pres' => $res->main->pressure,
                    'tempicon' => $this->_path.'views/img/tempicon.png',
                    'humedadicon' => $this->_path.'views/img/humedadicon.png',
                    'presionicon' => $this->_path.'views/img/presionicon.png',
                );
            } else {
                return false;
            }
        }
        
    //Generamos el widget
    public function renderWidget($hookName, array $configuration)
    {
        if($this->getWidgetVariables($hookName, $configuration)) {
            $this->context->smarty->assign($this->getWidgetVariables($hookName, $configuration));
            $template = '/views/templates/front/weatherModuleTemplate.tpl';
            return $this->fetch('module:weatherModule'.$template);
        }
    }
}