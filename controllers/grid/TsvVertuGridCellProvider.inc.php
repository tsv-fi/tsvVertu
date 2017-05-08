<?php

/**
 * @file controllers/grid/TsvVertuGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TsvVertuGridCellProvider
 * @ingroup controllers_grid_tsvVertu
 *
 * @brief Class for a cell provider to display information about static pages
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class TsvVertuGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function NavigationGridCellProvider() {
		parent::GridCellProvider();
	}
	//
	// Template methods from GridCellProvider
	//
	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		$navigationItem = $row->getData();
		switch ($column->getId()) {
			case 'id':
				return array('label' => $navigationItem->getId());
			case 'type':
				return array('label' => $navigationItem->getType());
			case 'title':
				$title = NavigationPlugin::createTitle($navigationItem, 'Smarty');
				if ($navigationItem->getParent() != '0')
					$title = "-- ".$title;	
				return array('label' => $title);
			case 'parent':
				return array('label' => $navigationItem->getParent());	
			case 'sort':
				return array('label' => $navigationItem->getSort());	
		}
	}

}

?>
