<?php

/**
 * @defgroup plugins_generic_tsvVertu TsvVertu Plugin
 */
 
/**
 * @file plugins/generic/tsvVertu/index.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_tsvVertu
 * @brief Wrapper for tsvVertu plugin.
 *
 */
require_once('TsvVertuPlugin.inc.php');

return new TsvVertuPlugin();

?>
