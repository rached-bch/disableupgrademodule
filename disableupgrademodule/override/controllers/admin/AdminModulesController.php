<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminModulesController extends AdminModulesControllerCore
{
	public function postProcessCallback()
	{
		$modules = Tools::getValue("update") ;
		if(!empty($modules) && Module::isEnabled("disableupgrademodule")) {
			// get list module to disable upgrade <<
			$list_modules_disabled_upgrade = Configuration::get('DUM_LIST_MODULES');
			if($list_modules_disabled_upgrade) 
				$list_modules_disabled_upgrade = explode(",", $list_modules_disabled_upgrade);
			else
				$list_modules_disabled_upgrade = array();
			// get list module to disable upgrade >>
			
			if (strpos($modules, '|')) {
				$modules_list_save = $modules;
				$modules = explode('|', $modules);
				$modules_filtereds = array();
				foreach($modules as $un_module) {
					if(!in_array($un_module, $list_modules_disabled_upgrade)) 
						$modules_filtereds[] = $un_module ;
				}	
				if(count($modules_filtereds) != count($modules) && count($modules_filtereds) > 0){
					if(isset($_POST['update'])) $_POST['update'] = implode("|", $modules_filtereds) ; 
					else $_GET['update'] = implode("|", $modules_filtereds) ;  
				} elseif(count($modules_filtereds) == 0) {
					if(isset($_POST['update'])) $_POST['update'] = "" ; 
					else $_GET['update'] = "" ;  
					
					Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
					exit();
				}
			} else {
				if(in_array($modules, $list_modules_disabled_upgrade)) {
					if(isset($_POST['update'])) $_POST['update'] = "" ; 
					else $_GET['update'] = "" ;  
					
					Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
					exit();
				}
			}
		}
			
		return parent::postProcessCallback();
	}
}
