<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Jeff Segars <jeff@webempoweredchurch.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Generic FCE Plugin' for the 'wec_fceplugin' extension.
 *
 * @author	Jeff Segars <jeff@webempoweredchurch.org>
 * @package	TYPO3
 * @subpackage	tx_wecfceplugin
 */
class tx_wecfceplugin_pi1 extends tslib_pibase {
	var $prefixId = 'tx_wecfceplugin_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wecfceplugin_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_fceplugin';	// The extension key.
	var $pi_checkCHash = TRUE;
	var $pi_USER_INT_obj;	// we don't set it here, we set it on demand

	var $ds;
	var $singleTO;
	var $listTO;
	var $ignoreListTO;
	var $maxRecords;
	var $invert;
	var $singlePid;
	var $singlePidPluginID;
	var $tv;
	var $pluginid;
	var $mode; // 'single' 'list'


	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pluginid = $this->cObj->data['uid'];

		/* Initialize the Flexform and pull the data into a new object */
		$this->pi_initPIflexform();
		$piFlexForm = $this->cObj->data['pi_flexform'];

		$this->ds = $piFlexForm['data']['default']['lDEF']['dataStructure']['vDEF'];
		$this->ds = $this->ds > 0? $this->ds : $this->conf['dataStructure'];
		$this->listTO = $piFlexForm['data']['default']['lDEF']['listViewTO']['vDEF'];
		$this->listTO = $this->listTO > 0? $this->listTO : $this->conf['listViewTO'];
		$this->ignoreListTO = $piFlexForm['data']['default']['lDEF']['ignoreListTO']['vDEF'];
		$this->ignoreListTO = $this->ignoreListTO > 0? $this->ignoreListTO : $this->conf['ignoreListTO'];
		$this->singleTO = $piFlexForm['data']['default']['lDEF']['singleViewTO']['vDEF'];
		$this->singleTO = $this->singleTO > 0? $this->singleTO : $this->conf['singleViewTO'];
		$this->maxRecords = $piFlexForm['data']['default']['lDEF']['maxRecords']['vDEF'];
		$this->maxRecords = $this->maxRecords > 0? $this->maxRecords : $this->conf['maxRecords'];
		$this->maxRecords = $this->maxRecords > 0? $this->maxRecords: null;
		$this->invert = $piFlexForm['data']['default']['lDEF']['invert']['vDEF'];
		$this->invert = $this->invert > 0? $this->invert : $this->conf['invert'];
		$this->singlePid = $piFlexForm['data']['default']['lDEF']['singlePid']['vDEF'];
		$this->singlePid = $this->singlePid > 0? $this->singlePid : $this->conf['singlePid'];
		if ($this->singlePid < 1) { $this->singlePid = null; }
		$this->singlePidPluginID = $piFlexForm['data']['default']['lDEF']['singlePidPluginID']['vDEF'];
		$this->singlePidPluginID = $this->singlePidPluginID > 0? $this->singlePidPluginID : $this->conf['singlePidPluginID'];
		if ($this->singlePidPluginID < 1) { $this->singlePidPluginID = null; }
		$this->dontWrapWithDiv = $piFlexForm['data']['default']['lDEF']['dontWrapWithDiv']['vDEF'];
		$this->dontWrapWithDiv = $this->dontWrapWithDiv > 0? $this->dontWrapWithDiv : $this->conf['dontWrapWithDiv'];
		if ($this->dontWrapWithDiv < 1) { $this->dontWrapWithDiv = null; }

		$this->pid = $piFlexForm['data']['default']['lDEF']['pid']['vDEF'];
		$this->pid = $this->pid > 0? $this->pid : $this->conf['pid'];
		if ($this->pid < 1) { $this->pid = null; }

		/* Set up TemplaVoila */
		require_once(t3lib_extMgm::extPath('templavoila').'pi1/class.tx_templavoila_pi1.php');
		$this->tv = t3lib_div::makeInstance('tx_templavoila_pi1');
		$this->tv->cObj = $this->cObj;
		$this->tv->initVars(array());

		$this->mode = 'list';
		if($this->piVars['uid'] > 0) {
			if ($this->pluginid == $this->piVars['pluginid']) {
				$this->mode = 'single';
			}
		}

		switch($this->mode) {
		case 'single' :
			$content = $this->singleView($this->piVars['uid']);
			break;
		default:
			$content = $this->listView();
			break;
		}	

		if ($this->dontWrapWithDiv != 1) {
			$content = $this->pi_wrapInBaseClass($content);
		}
		return $content;
	}

	function listView() {
		$pluginid = $this->singlePidPluginID > 0? $this->singlePidPluginID : $this->pluginid;
		$where = "CType='templavoila_pi1' AND tx_templavoila_ds=".$this->ds;
		$where .= $this->cObj->enableFields('tt_content');
		$where .= ! $this->ignoreListTO ? " AND tx_templavoila_to=".$this->listTO : '';
		$where .= $this->pid ? " AND pid=".$this->pid : '';
		$orderby = $this->invert == 1? 'sorting DESC' : 'sorting';
		/*
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
											'*', 
											'tt_content', 
											$where,
											'',
											$orderby,
											$this->maxRecords);
		print_r($query);
		 */
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*', 
			'tt_content', 
			$where,
			'',
			$orderby,
			$this->maxRecords);
		$content = '';

		while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))) {
			$cache = 1;
			$this->pi_USER_INT_obj = 0;

			$detaillink = $this->pi_linkTP_keepPIvars_url(
				array('pluginid' => $pluginid
				, 'uid' => $row['uid']
			)
			, $cache
			, 1
			, $this->singlePid
		);
			$content .= str_replace('###DETAILLINK###', $detaillink, $this->tv->renderElement($row, 'tt_content') );
		}

		return $content;
	}

	function singleView($uid) {
		// MLC 20090918 don't pull via to data might not be inputted multiple
		// times
		// AND tx_templavoila_to=".$this->listTO."
		$where = "CType='templavoila_pi1'
			AND tx_templavoila_ds=".$this->ds."
			AND uid=".$uid.
			$this->cObj->enableFields('tt_content')
			;

		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			'*',
			'tt_content',
			$where
		);

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tt_content',
			$where
		);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
		$row['tx_templavoila_to'] = $this->singleTO;

		$content = $this->tv->renderElement($row, 'tt_content');

		$cache = 0;
		$this->pi_USER_INT_obj = 1;

		$content .= $this->pi_linkTP_keepPIvars(
			'Back to List View'
			, array()
			, $cache
			, 1
			, $this->singlePid
		);

		return $content;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_fceplugin/pi1/class.tx_wecfceplugin_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_fceplugin/pi1/class.tx_wecfceplugin_pi1.php']);
}

?>
