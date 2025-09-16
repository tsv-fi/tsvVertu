<?php

/**
 * @file plugins/generic/tsvVertu/TsvVertuPlugin.php
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TsvVertuPlugin
 * @ingroup plugins_generic_tsvVertu
 *
 * @brief TsvVertu plugin class
 */

namespace APP\plugins\generic\tsvVertu;

use APP\core\Application;
use APP\template\TemplateManager;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class TsvVertuPlugin extends GenericPlugin {

	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True if plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path);
		if ($success && $this->getEnabled()) {

			// Handle display in frontend
			Hook::add('Templates::Article::Main', array($this, 'insertArticleLabel'));
			Hook::add('Templates::Issue::Issue::Article', array($this, 'insertTocLabel'));
			Hook::add('Templates::Catalog::Book::Main', array($this, 'insertBookLabel'));

			// Add stylesheet
			Hook::add('TemplateManager::display',array($this, 'insertStylesheet'));

			// Handle schema and form
			Hook::add('Schema::get::publication', array($this, 'addToSchema'));
			Hook::add('Schema::get::submission', array($this, 'addToSchema'));
			Hook::add('Form::config::before', array($this, 'addToForm'));
			Hook::add('Publication::version', [$this, 'versionPublication']);

			// TODO Handle OAI

		}
		return $success;
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

	/**
	 * Add a property to the submission schema
	 *
	 * @param $hookName string `Schema::get::submission`
	 * @param $args [[
	 * 	@option object Submission schema
	 * ]]
	 */
	public function addToSchema($hookName, $args) {
		$schema = $args[0];
		$prop = '{
			"type": "boolean",
			"apiSummary": true,
			"validation": [
				"nullable"
			]
		}';
		$schema->properties->vertuLabel = json_decode($prop);
	}

	/**
	 * Add a form field to a form
	 *
	 * @param $hookName string `Form::config::before`
	 * @param $form FormHandler
	 */
	public function addtoForm($hookName, $form) {

		if ((!defined('FORM_ISSUE_ENTRY') || $form->id !== FORM_ISSUE_ENTRY) && (!defined('FORM_CATALOG_ENTRY') || $form->id !== FORM_CATALOG_ENTRY)) {
			return;
		}

		$request = Application::get()->getRequest();
		$templateMgr = TemplateManager::getManager($request);
		$submission = $templateMgr->getTemplateVars('submission');

		if (!$submission) {
			return;
		}

		$form->addField(new \PKP\components\forms\FieldOptions('vertuLabel', [
			'label' => __('plugins.generic.tsvVertu.vertuLabel.name'),
			'groupId' => 'publishing',
			'options' => [
				['value' => true, 'label' => __('plugins.generic.tsvVertu.vertuLabel.description')]
			],
			'value' => $submission->getCurrentPublication()->getData('vertuLabel'),
		]));
	}

	/**
	 * Copy vertuLabel value when a new version is created
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option Publication The new version of the publication
	 *		@option Publication The old version of the publication
	 *		@option Request
	 * ]
	 */
	public function versionPublication($hookName, $args) {
		$newPublication = $args[0];
		$oldPublication = $args[1];
		if ($vertuLabel = $oldPublication->getData('vertuLabel')) {
			$newPublication->setData('vertuLabel', $vertuLabel);
		}
	}

	/**
	 * Insert label to public TOC
	 */
	function insertTocLabel($hookName, $params) {
		if ($this->getEnabled()) {
			$templateMgr =& $params[1];
			$output =& $params[2];
			$request = Application::get()->getRequest();
			$article = $templateMgr->getTemplateVars('article');
			if ($article->getData('vertuLabel') || $article->getCurrentPublication()->getData('vertuLabel')){
				$output .= '<div class="prLabelSmall"><img src="' . $request->getBaseUrl() . '/' . $this->getPluginPath() . '/images/prlabel-small.jpg" /></div>';
			}
		}
		return false;
	}

	/**
	 * Insert label to Article landing page
	 */
	function insertArticleLabel($hookName, $params) {
		if ($this->getEnabled()) {
			$templateMgr = $params[1];
			$output =& $params[2];
			$request = Application::get()->getRequest();
			$article = $templateMgr->getTemplateVars('article');

			if ($article->getData('vertuLabel') || $article->getCurrentPublication()->getData('vertuLabel')){
				$output .= '<a href="http://www.tsv.fi/tunnus"><img src="' . $request->getBaseUrl() . '/' . $this->getPluginPath() . '/images/prlabel-large.jpg" class="prLabelLarge" /></a>';
			}
		}
		return false;
	}

	/**
	 * Insert label to Book landing page
	 */
	function insertBookLabel($hookName, $params) {
		if ($this->getEnabled()) {
			$templateMgr = $params[1];
			$output =& $params[2];
			$request = Application::get()->getRequest();
			$publishedSubmission = $templateMgr->getTemplateVars('publishedSubmission');

			if ($publishedSubmission->getData('vertuLabel') || $publishedSubmission->getCurrentPublication()->getData('vertuLabel')){
				$output .= '<a href="http://www.tsv.fi/tunnus"><img src="' . $request->getBaseUrl() . '/' . $this->getPluginPath() . '/images/prlabel-large.jpg" class="prLabelLarge" /></a>';
			}
		}
		return false;
	}

	/**
	 * Insert stylesheet
	 */
	function insertStylesheet($hookName, $params) {
		$template = $params[1];
		$request = Application::get()->getRequest();

		if ($template != 'frontend/pages/search.tpl' && $template != 'frontend/pages/issue.tpl' && $template != 'frontend/pages/article.tpl' && $template != 'frontend/pages/indexJournal.tpl') return false;

		$templateMgr = $params[0];
		$templateMgr->addStylesheet(
			'tsvVertu',
			$request->getBaseUrl() . '/' . $this->getPluginPath() . '/tsvVertu.css',
			array(
				'contexts' => array('frontend')
			)
		);

		return false;
	}
}
?>
