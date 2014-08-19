<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')    {       	
	/* Add the plugin to the New Content Element wizard */
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_wecfceplugin_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_wecfceplugin_pi1_wizicon.php';

	require_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_wecfceplugin_backend.php');
}

/* Set up the tt_content fields for the two frontend plugins */
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

/* Adds the plugins and flexforms to the TCA */
t3lib_extMgm::addPlugin(array('LLL:EXT:wec_fceplugin/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:wec_fceplugin/pi1/flexform_ds.xml');

/* Add static TS template for plugins */
t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Generic FCE Plugin");

?>
