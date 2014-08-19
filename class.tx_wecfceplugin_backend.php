<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Foundation For Evangelism (info@evangelize.org)
* All rights reserved
*
* This file is part of the Web-Empowered Church (WEC)
* (http://webempoweredchurch.org) ministry of the Foundation for Evangelism
* (http://evangelize.org). The WEC is developing TYPO3-based
* (http://typo3.org) free software for churches around the world. Our desire
* is to use the Internet to help offer new life through Jesus Christ. Please
* see http://WebEmpoweredChurch.org/Jesus.
*
* You can redistribute this file and/or modify it under the terms of the
* GNU General Public License as published by the Free Software Foundation;
* either version 2 of the License, or (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This file is distributed in the hope that it will be useful for ministry,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the file!
***************************************************************/

class tx_wecfceplugin_backend {

	function getTemplateObjects(&$params, &$pObj) {
		$table = 'tx_templavoila_tmplobj';
		$row = $params['row'];
		
		$selectedDS = $this->getSelectedDataStructure($row['pi_flexform']);
		
		$TSconfig = t3lib_BEfunc::getTCEFORM_TSconfig($table, $row);
		$storagePID = $TSconfig['_STORAGE_PID']?$TSconfig['_STORAGE_PID']:0;
		
		$where = ' AND pid='.$storagePID.' AND parent=0';
		$records = t3lib_beFunc::getRecordsByField($table, 'datastructure', $selectedDS, $where, '', 'sorting');

		if(is_array($records)) {
			foreach($records as $record) {
				$params['items'][] = array($record['title'], $record['uid']);
				//$params['items'][] = array(t3lib_beFunc::getRecordTitle($table, $record['uid'], $record['uid']));
			}
		}
	}
	
	function getSelectedDataStructure($flexform) {
		$flexform = t3lib_div::xml2array($flexform);
		return $flexform['data']['default']['lDEF']['dataStructure']['vDEF'];
	}

}
