<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

use \TYPO3\TsAutoloader\TypoScript\Init as TypoScriptInitiator;

$typoscript =	'# Setting ' . $_EXTKEY . ' plugin TypoScript' . PHP_EOL . PHP_EOL . 
				TypoScriptInitiator::instance()->autoloadTypoScript();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript($_EXTKEY, 'setup', $typoscript, 43);
?>