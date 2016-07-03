<?php
if (!defined('_PS_VERSION_'))
  exit;
 
class DisableUpgradeModule extends Module
{
	public function __construct()
	{
		$this->name = 'disableupgrademodule';
		$this->tab = 'others';
		$this->version = '2.0';
		$this->author = "Pixels";
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6.0');
		$this->bootstrap = true;
		parent::__construct();
		$this->displayName = $this->l('Disable Upgrade Module');
		$this->description = $this->l('Select from the list of modules that should not be updated');
		
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	}
  
	public function install()
	{
		$result =  parent::install() && Configuration::updateValue('DUM_LIST_MODULES', "") && Configuration::updateValue('DUM_NOTIF_PRESTA_VERSION', 0) ;
		@unlink(_PS_ROOT_DIR_."/".Autoload::INDEX_FILE);
		
		return $result ;
	}
	
	public function uninstall()
	{
		@unlink(_PS_ROOT_DIR_."/".Autoload::INDEX_FILE);
		Autoload::getInstance()->generateIndex();
		
		$result = parent::uninstall() && Configuration::deleteByName('DUM_LIST_MODULES') && Configuration::deleteByName('DUM_NOTIF_PRESTA_VERSION') ;
		@unlink(_PS_ROOT_DIR_."/".Autoload::INDEX_FILE);
		Autoload::getInstance()->generateIndex();
		
		return $result ;
	}
	
	public function getContent()
	{
		$output = '' ;
		if (Tools::isSubmit('savedisableupgrademodule'))
		{
			$dont_show_notif_prestashop_version = (int) Tools::getValue('dont_show_notif_prestashop_version') ;
			$list_modules = Tools::getValue('selection_modules') ;
			if(empty($list_modules))
				$list_modules = array();
			
			Configuration::updateValue('DUM_LIST_MODULES', implode(",", $list_modules)) ;
			Configuration::updateValue('DUM_NOTIF_PRESTA_VERSION', $dont_show_notif_prestashop_version) ;
			
			$output .= $this->displayConfirmation($this->l('Saved settings.'));
		}
		$helper = $this->initForm();
		$output .= $this->generateJavascript().$helper->generateForm($this->fields_form);
		
		return $output ;
	}
	
	protected function generateJavascript()
	{
		return '<script type="text/javascript">
			//<![CDATA[
			if (window.jQuery) {
				var checked_selection_modules = true ;
				jQuery(document).ready( function() {
					jQuery("#check_all_modules_id").click(function() {
						jQuery(".selection_modules").attr("checked", checked_selection_modules);
						if(!checked_selection_modules) checked_selection_modules = true ; else checked_selection_modules = false ;
					});				
				});	
			}
			// ]]>
		</script>' ;
	}
	
	protected function initForm()
	{
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
		
		$modules = Module::getModulesOnDisk(true);
		
		$list_modules_inputs = array();
		if(is_array($modules)) {
			foreach($modules as $one_module) {
				$list_modules_inputs[] = array(
												'val' => $one_module->name,
												'name' => $one_module->displayName,
												'id' => $one_module->name,
												) ;
			}
		}
		
		$this->fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
			),
			'input' => array(
				array(
					'type'	 => 'radio',
					'label'  => $this->l('Dont show Upgrade notification for Prestashop Version'),
					'name' 	 => 'dont_show_notif_prestashop_version',
					'class'  => 't',
					'is_bool'=> true,
					'values' => array(
						array(
							'id' 	=> 'dont_show_notif_prestashop_version_yes',
							'value'	=> 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' 	=> 'dont_show_notif_prestashop_version_no',
							'value'	=> 0,
							'label' => $this->l('No')
						)
					)
				),
				array(
					'type' => 'checkbox',
					'label' => $this->l('Check / Uncheck all modules'),
					'name' => 'check_all_modules',
					'lang' => true,
					'values' => array(
						'query' => array(array(
												'val' => 1,
												'name' => '',
												'id' => 'id',
											   )),
						'id' => 'id',
						'name' => 'name'
					)
				),
				array(
					'type' => 'checkbox',
					'label' => $this->l('Modules with the update blocked:'),
					'name' => 'selection_modules[]',
					'class' => 'selection_modules',
					'values' => array(
						'query' => $list_modules_inputs,
						'id' => 'id',
						'name' => 'name'
					)
				),
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button'
			)
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->module = $this;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		
		
		
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;

		$helper->submit_action = 'savedisableupgrademodule';
		
		$helper->fields_value['dont_show_notif_prestashop_version'] = 0 ;
		$dont_show_notif_prestashop_version = (int) Configuration::get('DUM_NOTIF_PRESTA_VERSION');
		if($dont_show_notif_prestashop_version == 1)
			$helper->fields_value['dont_show_notif_prestashop_version'] = 1 ;
		
		$list_modules = Configuration::get('DUM_LIST_MODULES');
		if($list_modules) {
			$list_modules = explode(",", $list_modules);
			foreach($list_modules as $id_module)
				$helper->fields_value['selection_modules[]_'.$id_module] = true;
		}
		
		return $helper;
	}
}