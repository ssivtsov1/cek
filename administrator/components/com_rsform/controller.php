<?php
/**
* @package RSForm! Pro
* @copyright (C) 2007-2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class RSFormController extends JControllerLegacy
{	
	public function __construct()
	{
		parent::__construct();
		
		JHtml::_('behavior.framework');
		
		$version 	= new RSFormProVersion();
		$v 			= $version->revision;
		$doc 		= JFactory::getDocument();
		
		if (RSFormProHelper::isJ('3.0')) {
			JHtml::_('jquery.framework');
		} else {
			$doc->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/js/jquery.js?v='.$v);
		}
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/js/script.js?v='.$v);
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/js/tablednd.js?v='.$v);
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsform/assets/js/jquery.scrollto.js?v='.$v);
		
		$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsform/assets/css/style.css?v='.$v);
		if (RSFormProHelper::isJ('3.0')) {
			$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsform/assets/css/style30.css?v='.$v);
		} else {
			$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsform/assets/css/style25.css?v='.$v);
		}
		$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsform/assets/css/rsdesign.css?v='.$v);
	}
	
	function mappings()
	{
		JRequest::setVar('view', 'forms');
		JRequest::setVar('layout', 'edit_mappings');
		JRequest::setVar('tmpl', 'component');
		
		parent::display();
	}
	
	function changeLanguage()
	{
		$formId  	 = JRequest::getInt('formId');
		$tabposition = JRequest::getInt('tabposition');
		$tab		 = JRequest::getInt('tab',0);
		$tab 		 = $tabposition ? '&tab='.$tab : '';
		$session 	 = JFactory::getSession();
		$session->set('com_rsform.form.'.$formId.'.lang', JRequest::getVar('Language'));
		
		$this->setRedirect('index.php?option=com_rsform&task=forms.edit&formId='.$formId.'&tabposition='.$tabposition.$tab);
	}
	
	function changeEmailLanguage()
	{
		$formId  = JRequest::getInt('formId');
		$cid	 = JRequest::getInt('id');
		$session = JFactory::getSession();
		$session->set('com_rsform.emails.'.$cid.'.lang', JRequest::getVar('ELanguage'));
		
		$this->setRedirect('index.php?option=com_rsform&task=forms.emails&tmpl=component&formId='.$formId.'&cid='.$cid);
	}

	function layoutsGenerate()
	{
		$model = $this->getModel('forms');
		$model->getForm();
		$model->_form->FormLayoutName = JRequest::getCmd('layoutName');
		$model->autoGenerateLayout();
		
		echo $model->_form->FormLayout;
		exit();
	}

	function layoutsSaveName()
	{
		$formId = JRequest::getInt('formId');
		$name = JRequest::getVar('formLayoutName');
		
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__rsform_forms SET FormLayoutName='".$db->escape($name)."' WHERE FormId='".$formId."'");
		$db->execute();
		
		exit();
	}
	
	function submissionExportPDF()
	{		
		$cid = JRequest::getInt('cid');
		$this->setRedirect('index.php?option=com_rsform&view=submissions&layout=edit&cid='.$cid.'&format=pdf');
	}

	/**
	 * Backup / Restore Screen
	 */
	function backupRestore()
	{
		JRequest::setVar('view', 'backuprestore');
		JRequest::setVar('layout', 'default');
		
		parent::display();
	}

	function updatesManage()
	{
		JRequest::setVar('view', 'updates');
		JRequest::setVar('layout', 'default');
		
		parent::display();
	}
	
	function goToPlugins()
	{
		$mainframe = JFactory::getApplication();
		$mainframe->redirect('http://www.rsjoomla.com/support/documentation/view-knowledgebase/26-plugins-and-modules.html');
	}
	
	function goToSupport()
	{
		$mainframe = JFactory::getApplication();
		$mainframe->redirect('http://www.rsjoomla.com/support/documentation/view-knowledgebase/21-rsform-pro-user-guide.html');
	}
	
	function plugin()
	{
		$mainframe = JFactory::getApplication();
		$mainframe->triggerEvent('rsfp_bk_onSwitchTasks');
	}
	
	function setMenu()
	{
		$app   = JFactory::getApplication();
		
		$type  = json_decode('{"id":0,"title":"COM_RSFORM_MENU_FORM","request":{"option":"com_rsform","view":"rsform"}}');
		$title = 'component';
		
		$app->setUserState('com_menus.edit.item.type',	$title);
		
		$component = JComponentHelper::getComponent($type->request->option);
		$data['component_id'] = $component->id;
		
		$params['option'] = 'com_rsform';
		$params['view']   = 'rsform';
		$params['formId'] = JRequest::getInt('formId');
		
		$app->setUserState('com_menus.edit.item.link', 'index.php?'.JURI::buildQuery($params));
		
		$data['type'] = $title;
		$data['formId'] = JRequest::getInt('formId');
		$app->setUserState('com_menus.edit.item.data', $data);
		
		$this->setRedirect(JRoute::_('index.php?option=com_menus&view=item&layout=edit', false));
	}
	
	function captcha()
	{
		require_once JPATH_SITE.'/components/com_rsform/helpers/captcha.php';
		
		$componentId = JRequest::getInt('componentId');
		$captcha = new RSFormProCaptcha($componentId);

		$session = JFactory::getSession();
		$session->set('com_rsform.captcha.'.$componentId, $captcha->getCaptcha());
		exit();
	}
}