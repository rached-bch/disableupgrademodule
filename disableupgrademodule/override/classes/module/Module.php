<?php
abstract class Module extends ModuleCore
{
	public static function getModulesOnDisk($useConfig = false, $loggedOnAddons = false, $id_employee = false)
	{ 
		$module_list = parent::getModulesOnDisk($useConfig, $loggedOnAddons, $id_employee);
		if(is_array($module_list) && Module::isEnabled("disableupgrademodule")) {
			// get list module to disable upgrade <<
			$list_modules_disabled_upgrade = Configuration::get('DUM_LIST_MODULES');
			if($list_modules_disabled_upgrade) 
				$list_modules_disabled_upgrade = explode(",", $list_modules_disabled_upgrade);
			else
				$list_modules_disabled_upgrade = array();
			// get list module to disable upgrade >>	
			foreach($module_list as $key => &$object) {
				if(isset($object->version_addons)) {
					if(is_object($object->version_addons)) {
						if(isset($object->name)) {
							if(in_array($object->name, $list_modules_disabled_upgrade))	
								unset($object->version_addons) ;
						}
					}
				}
			}
		}
		return $module_list ;
	}
}