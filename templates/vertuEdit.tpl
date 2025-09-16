{**
 * plugins/generic/tsvVertu/vertuEdit.tpl
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Edit TsvVertu vertuLabel 
 *
 *}
{fbvFormArea id="vertuLabel"}
	{fbvFormSection list="true"}
		{fbvElement type="checkbox" id="vertuLabel" label="plugins.generic.tsvVertu.vertuLabel.description" checked=$vertuLabel|compare:true}
	{/fbvFormSection}
{/fbvFormArea}