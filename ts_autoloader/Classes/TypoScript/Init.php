<?php
namespace TYPO3\TsAutoloader\TypoScript;
use \TYPO3\CMS\Core\Utility\GeneralUtility,
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/***************************************************************
*  Copyright notice
*
*  (c) 2013 Mikel Fröse <mikel@froe.se>
*  			
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

/**
 * TypoScript Class for the TypoScript autoload Actions
 * 
 * @author Mikel Fröse <mikel@froe.se>
 * @package TypoScript
 */
class Init  {

	const EXTENSIONNAME = 'ts_autoloader';
	
	/**
	 * The path to the typoscript with the autoloader configuration
	 *
	 * @var string
	 */
	protected static $initiatorTypoScript = 'fileadmin/ts_autoloader.ts';

	/**
	 * Holds an instance of the Tx_TsAutoloader_TypoScript_Init class
	 *
	 * @var Tx_TsAutoloader_TypoScript_Init
	 */
	private static $instance;

	/**
	 * Creates an Instance of the current Class
	 * 
	 * @return Tx_TsAutoloader_TypoScript_Init
	 */
	public static function instance() {
		if (self::$instance) {
			return self::$instance;
		}

		return self::$instance = GeneralUtility::makeInstance('TYPO3\\TsAutoloader\\TypoScript\\Init');
	}

	private static $settings;

	protected function getSettings() {
		if (self::$settings) {
			return self::$settings;
		}

		$typoScriptFile = GeneralUtility::getFileAbsFileName(self::$initiatorTypoScript);

		$typoscript = '';

		if (file_exists($typoScriptFile)) {
			$typoscript = file_get_contents($typoScriptFile);
		}

		$parserInstance = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\\Parser\\TypoScriptParser');
		$realTypoScript = $parserInstance::checkIncludeLines($typoscript);
		$parserInstance->parse($realTypoScript);
		$parsedTypoScript = $parserInstance->setup;

		return self::$settings = @isset($parsedTypoScript['plugin.']['tx_tsautoload.']) ? $parsedTypoScript['plugin.']['tx_tsautoload.'] : array();
	}

	public function autoloadTypoScript() {
		$settings = $this->getSettings();

		$loadedFiles = array();
		foreach ($settings as $index => $loadSettings) {
			if (isset($loadSettings['type'])) {
				$pattern = false;
				switch ($loadSettings['type']) {
					case 'file':
						if (isset($loadSettings['pattern'])) {
							$pattern = $loadSettings['pattern'];

							if (isset($loadSettings['directory'])) {
								$relDirectory = str_replace(array('/','\\'),DIRECTORY_SEPARATOR, $loadSettings['directory']);
								if (substr($relDirectory,-1) != DIRECTORY_SEPARATOR) {
									$relDirectory .= DIRECTORY_SEPARATOR;
								}
								$directory = GeneralUtility::getFileAbsFileName($relDirectory);

								$pattern = str_replace('|','*',$pattern);

								$files = glob($directory . $pattern);
								foreach ($files as $file) {
									$loadedFiles[$file] = $relDirectory . basename($file);
								}

								if (isset($loadSettings['ignore'])) {
									$ignorePattern = GeneralUtility::trimExplode(',',$loadSettings['ignore']);

									foreach ($ignorePattern as $ignore) {
										$ignoreFiles = glob($directory . $ignore);

										if (count($ignoreFiles)) {
											foreach ($ignoreFiles as $ignoreFile) {
												if (isset($loadedFiles[$ignoreFile])) {
													unset($loadedFiles[$ignoreFile]);
												}
											}
										}
									}
								}
							}
						}
					break;
				}
			}
		}

		return $this->includeFiles($loadedFiles);
	}

	protected function includeFiles($loadedFiles) {
		$typoscript = '';

		foreach ($loadedFiles as $file) {
			$typoscript .= '<INCLUDE_TYPOSCRIPT: source="FILE:' . $file . '">' . PHP_EOL;
		}

		return $typoscript;
	}
}