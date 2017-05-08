<?php

/**
 * @file plugins/generic/tsvVertu/TsvVertuPlugin.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TsvVertuPlugin
 * @ingroup plugins_generic_tsvVertu
 *
 * @brief TsvVertu plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');


class TsvVertuPlugin extends GenericPlugin {

	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True if plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		if ($success && $this->getEnabled()) {
			
			// Handle metadata forms
			HookRegistry::register('TemplateManager::display', array($this, 'metadataFieldEdit'));
			#HookRegistry::register('issueentrypublicationmetadataform::initdata', array($this, 'metadataInitData'));
			HookRegistry::register('issueentrypublicationmetadataform::readuservars', array($this, 'metadataReadUserVars'));
			HookRegistry::register('issueentrypublicationmetadataform::execute', array($this, 'metadataExecute'));
			HookRegistry::register('articledao::getAdditionalFieldNames', array($this, 'articleSubmitGetFieldNames'));

			// Handle bulk edit
			#HookRegistry::register('Templates::Management::Settings::website', array($this, 'callbackShowWebsiteSettingsTabs'));
			#HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));
			
			// Handle display in frontend
			HookRegistry::register('Templates::Article::Main', array($this, 'insertArticleLabel'));
			HookRegistry::register('Templates::Issue::Issue::Article', array($this, 'insertTocLabel'));
			
			// Add stylesheet
			HookRegistry::register('TemplateManager::display',array($this, 'insertStylesheet'));			
			
			// Handle OAI

		}
		return $success;
	}
	
	/**
	 * Extend the website settings tabs to include vertu page
	 */
	function callbackShowWebsiteSettingsTabs($hookName, $args) {
		$output =& $args[2];
		$request =& Registry::get('request');
		$dispatcher = $request->getDispatcher();
		$output .= '<li><a name="tsvVertu" href="' . $dispatcher->url($request, ROUTE_COMPONENT, null, 'plugins.generic.tsvVertu.controllers.grid.TsvVertuGridHandler', 'index') . '">' . __('plugins.generic.tsvVertu.displayName') . '</a></li>';
		return false;
	}
	

	/**
	 * Permit requests to the grid handler
	 */
	function setupGridHandler($hookName, $params) {
		$component =& $params[0];
		if ($component == 'plugins.generic.tsvVertu.controllers.grid.TsvVertuGridHandler') {
			import($component);
			TsvVertuGridHandler::setPlugin($this);
			return true;
		}
		return false;
	}	
	

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.tsvVertu.displayName');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.tsvVertu.description');
	}


	/*
	 * Metadata
	 */

	/**
	 * Insert vertuLabel field into schedule publication form
	 */
	function metadataFieldEdit($hookName, $params) {
		$template =& $params[1];
		
		if ($template != "controllers/tab/issueEntry/form/publicationMetadataFormFields.tpl") return false;
		$templateMgr =& $params[0];		
		$templateMgr->register_outputfilter(array($this, 'formFilter'));
		
		return false;
		
	}


	/**
	 * Output filter adds form field
	 */
	function formFilter($output, &$templateMgr) {
		
		if (preg_match('/<div class=\"section formButtons/', $output, $matches, PREG_OFFSET_CAPTURE) AND !strpos($output, 'id="vertuLabel"')) {
			$match = $matches[0][0];
			$offset = $matches[0][1];				
			
			$fbv = $templateMgr->getFBV();
			$form = $fbv->getForm();
			$article = $form->getSubmission();		
			$articleVertuLabel = $article->getData('vertuLabel');
						
			$templateMgr->assign(array(
				'vertuLabel' => $articleVertuLabel,
			));
			
			$newOutput = substr($output, 0, $offset);	
			$newOutput .= $templateMgr->fetch($this->getTemplatePath() . 'vertuEdit.tpl');
			$newOutput .= substr($output, $offset);
			$output = $newOutput;
			
		}
		$templateMgr->unregister_outputfilter('formFilter');
		return $output;
	}	
	
	
	/**
	 * Add vertuLabel element to the article
	 */
	function articleSubmitGetFieldNames($hookName, $params) {
		$fields =& $params[1];
		$fields[] = 'vertuLabel';
		return false;
	}

	/**
	 * Set article vertuLabel
	 */
	function metadataExecute($hookName, $params) {
		$form =& $params[0];
		$article = $form->getSubmission();

		$formVertuLabel = $form->getData('vertuLabel');
		$article->setData('vertuLabel', $formVertuLabel);
		return false;
	}

	/**
	 * Init article vertuLabel
	 */
	function metadataInitData($hookName, $params) {

		$form =& $params[0];
		$article = $form->getSubmission();		
		$articleVertuLabel = $article->getData('vertuLabel');
		$form->setData('vertuLabel', $articleVertuLabel);
		return false;
	}

	/**
	 * Concern vertuLabel field in the form
	 */
	function metadataReadUserVars($hookName, $params) {

		
		$userVars =& $params[1];
		$userVars[] = 'vertuLabel';
		return false;
	}

	
	/**
	 * Insert label to public TOC
	 */
	function insertTocLabel($hookName, $params) {
		if ($this->getEnabled()) {
			$templateMgr =& $params[1];
			$output =& $params[2];
			
			$article = $templateMgr->get_template_vars('article');
			
			if ($article->getData('vertuLabel')){
				$output .= '<div class="prLabelSmall"><img src="' . Request::getBaseUrl() . '/' . $this->getPluginPath() . '/images/prlabel-small.jpg" /></div>';
			}
			
		}
		return false;
	}	
	
	/**
	 * Insert label to Article landing page
	 */
	function insertArticleLabel($hookName, $params) {
		if ($this->getEnabled()) {
			$templateMgr =& $params[1];
			$output =& $params[2];
			$article = $templateMgr->get_template_vars('article');			

			if ($article->getData('vertuLabel')){
				$output .= '<a href="http://www.tsv.fi/tunnus"><img src="' . Request::getBaseUrl() . '/' . $this->getPluginPath() . '/images/prlabel-large.jpg" class="prLabelLarge" /></a>';
			}
		}
		return false;
	}	
	

	/**
	 * Insert stylesheet
	 */
	function insertStylesheet($hookName, $params) {

		$template = $params[1];
			
		if ($template != 'frontend/pages/issue.tpl' && $template != 'frontend/pages/article.tpl' && $template != 'frontend/pages/indexJournal.tpl') return false;
		
		$templateMgr = $params[0];
		$templateMgr->addStylesheet('tsvVertu', Request::getBaseUrl() . '/' . $this->getPluginPath() . '/tsvVertu.css');		
		
		return false;
	}	
	
	

}
?>
