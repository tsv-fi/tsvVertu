<?php

/**
 * @file TsvVertuHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.tsvVertu
 * @class TsvVertuHandler
 * Find static page content and display it when requested.
 */

import('classes.handler.Handler');

class TsvVertuHandler extends Handler {
	/** @var TsvVertuPlugin */
	static $plugin;


	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Provide the static pages plugin to the handler.
	 * @param $plugin TsvVertuPlugin
	 */
	static function setPlugin($plugin) {
		self::$plugin = $plugin;
	}

	/**
	 * Set a static page to view.
	 * @param $tsvVertu StaticPage
	 */
	static function setPage($tsvVertu) {
		self::$tsvVertu = $tsvVertu;
	}

}

?>
