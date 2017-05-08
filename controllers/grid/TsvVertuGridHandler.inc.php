<?php

/**
 * @file controllers/grid/TsvVertuGridHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TsvVertuGridHandler
 * @ingroup controllers_grid_tsvVertu
 *
 * @brief Handle static pages grid requests.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');

class TsvVertuGridHandler extends GridHandler {
	/** @var NavigationPlugin The Navigation plugin */
	static $plugin;
	/**
	 * Set the Navigation plugin.
	 * @param $plugin NavigationPlugin
	 */
	static function setPlugin($plugin) {
		self::$plugin = $plugin;
	}
	/**
	 * Constructor
	 */
	function NavigationGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER),
			array('index', 'fetchGrid', 'fetchRow')
		);
	}
	//
	// Overridden template methods
	//
	/**
	 * @copydoc Gridhandler::initialize()
	 */
	function initialize($request, $args = null) {
		parent::initialize($request);
		$context = $request->getContext();
		// Set the grid details.
		$this->setTitle('plugins.generic.navigation.navigation');
		$this->setEmptyRowText('plugins.generic.navigation.noneCreated');
		// Get the navigation items and add the data to the grid
		#$navigationDao = DAORegistry::getDAO('NavigationDAO');
		#$this->setGridDataElements($navigationDao->getOrderedByContextId($context->getId()));
		
		// Add grid-level actions
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');

		// Columns
		$cellProvider = new TsvVertuGridCellProvider();
		$this->addColumn(new GridColumn(
			'id',
			'common.id',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));
		$this->addColumn(new GridColumn(
			'title',
			'common.title',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));
		$this->addColumn(new GridColumn(
			'type',
			'common.type',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));
		$this->addColumn(new GridColumn(
			'parent',
			'plugins.generic.navigation.parent',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));
		$this->addColumn(new GridColumn(
			'sort',
			'plugins.generic.navigation.sort',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));		
		
		
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * @copydoc Gridhandler::getRowInstance()
	 */
	function getRowInstance() {
		return new NavigationGridRow();
	}
	//
	// Public Grid Actions
	//
	/**
	 * Display the grid's containing page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, $request) {
		$context = $request->getContext();
		import('lib.pkp.classes.form.Form');
		$form = new Form(self::$plugin->getTemplatePath() . 'navigation.tpl');
		$json = new JSONMessage(true, $form->fetch($request));
		return $json->getString();
	}

}

?>
