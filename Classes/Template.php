<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a HTML template with markers (###MARKER###) and
 * subparts (<!-- ###SUBPART### --><!-- ###SUBPART### -->).
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Template {
	/**
	 * @var string the regular expression used to find subparts
	 */
	const SUBPART_PATTERN = '/<!-- *###([A-Z0-9_]+)###.*-->(.*)<!-- *###\1###.*-->/msU';

	/**
	 * @var string the regular expression used to find subparts
	 */
	const LABEL_PATTERN = '/###(LABEL_([A-Z0-9_]+))###/';

	/**
	 * @var string the complete HTML template
	 */
	private $templateCode = '';

	/**
	 * associative array of all HTML template subparts, using the uppercase marker names without ### as keys,
	 * for example "MY_MARKER"
	 *
	 * @var string[]
	 */
	private $subparts = array();

	/**
	 * all uppercased marker names in the current template without the hashes, for example ("FOO", "BAR")
	 *
	 * @var string[]
	 */
	private $markerNames = array();

	/**
	 * all lowercased label marker names in the current template without the hashes, for example ("label_foo", "label_bar")
	 *
	 * @var string[]
	 */
	private $labelMarkerNames = array();

	/**
	 * associative array of *populated* markers and their contents
	 * (with the keys being the marker names including the wrapping hash signs ###).
	 *
	 * @var string[]
	 */
	private $markers = array();

	/**
	 * Subpart names that shouldn't be displayed. Set a subpart key like "FIELD_DATE"
	 * (the value does not matter) to remove that subpart.
	 *
	 * @var string[]
	 */
	private $subpartsToHide = array();

	/**
	 * @var Tx_Oelib_Translator
	 */
	protected $translator = NULL;

	/**
	 * The constructor. Does nothing.
	 */
	public function __construct() {
	}

	/**
	 * Injects the translator.
	 *
	 * @param Tx_Oelib_Translator $translator
	 *
	 * @return void
	 */
	public function injectTranslator(Tx_Oelib_Translator $translator) {
		$this->translator = $translator;
	}

	/**
	 * Gets the HTML template in the file specified in the parameter $filename,
	 * stores it and retrieves all subparts, writing them to $this->subparts.
	 *
	 * @param string $fileName the file name of the HTML template to process, must be an existing file, must not be empty
	 *
	 * @return void
	 */
	public function processTemplateFromFile($fileName) {
		$this->processTemplate(
			file_get_contents(GeneralUtility::getFileAbsFileName($fileName))
		);
	}

	/**
	 * Stores the given HTML template and retrieves all subparts, writing them
	 * to $this->subparts.
	 *
	 * The subpart names are automatically retrieved from $templateCode and
	 * are used as array keys. For this, the ### are removed, but the names stay
	 * uppercase.
	 *
	 * Example: The subpart ###MY_SUBPART### will be stored with the array key
	 * 'MY_SUBPART'.
	 *
	 * @param string $templateCode the content of the HTML template
	 *
	 * @return void
	 */
	public function processTemplate($templateCode) {
		$this->templateCode = $templateCode;
		$this->extractSubparts($templateCode);
		$this->findMarkers();
	}

	/**
	 * Recursively extracts all subparts from $templateCode and writes them to
	 * $this->subparts.
	 *
	 * @param string $templateCode the template code to process, may be empty
	 *
	 * @return void
	 */
	private function extractSubparts($templateCode) {
		// If there are no HTML comments in  the template code, there cannot be
		// any subparts. So there's no need to use an expensive regular
		// expression to find any subparts in that case.
		if (strpos($templateCode, '<!--') === FALSE) {
			return;
		}

		$matches = array();
		preg_match_all(
			self::SUBPART_PATTERN,
			$templateCode, $matches, PREG_SET_ORDER
		);
		foreach ($matches as $match) {
			$subpartName = $match[1];
			if (!isset($this->subparts[$subpartName])) {
				$subpartContent = $match[2];
				$this->subparts[$subpartName] = $subpartContent;
				$this->extractSubparts($subpartContent);
			}
		}
	}

	/**
	 * Finds all markers within the current HTML template and writes their names
	 * to $this->markerNames.
	 *
	 * In addition, it stores the lowercased label marker names in $this->labelMarkerNames.
	 *
	 * @return void
	 */
	private function findMarkers() {
		$matches = array();

		preg_match_all(
			'/###([A-Z0-9_]+)###/',
			$this->templateCode,
			$matches
		);

		$this->markerNames = array_unique($matches[1]);
		foreach ($this->markerNames as $markerName) {
			if (substr($markerName, 0, 6) === 'LABEL_') {
				$this->labelMarkerNames[] = strtolower($markerName);
			}
		}
	}

	/**
	 * Gets a list of markers with a given prefix.
	 * Example: If the prefix is "WRAPPER" (or "wrapper", case is not relevant),
	 * the following array might be returned: ("WRAPPER_FOO", "WRAPPER_BAR")
	 *
	 * If there are no matches, an empty array is returned.
	 *
	 * @deprecated 2015-03-01
	 *
	 * @param string $prefix case-insensitive prefix for the marker names to look for
	 *
	 * @return string[] matching marker names, might be empty
	 */
	public function getPrefixedMarkers($prefix) {
		GeneralUtility::logDeprecatedFunction();

		$upperCasePrefix = strtoupper($prefix) . '_';

		$result = array();
		foreach ($this->markerNames as $marker) {
			if (strpos($marker, $upperCasePrefix) === 0) {
				$result[] = $marker;
			}
		}

		return $result;
	}

	/**
	 * Gets a list of marker names with the "LABEL" prefix.
	 *
	 * If there are no matches, an empty array is returned.
	 *
	 * @return string[] matching marker names (lowercased), might be empty
	 */
	public function getLabelMarkerNames() {
		return $this->labelMarkerNames;
	}

	/**
	 * Sets a marker's content.
	 *
	 * Example: If the prefix is "field" and the marker name is "one", the
	 * marker "###FIELD_ONE###" will be written.
	 *
	 * If the prefix is empty and the marker name is "one", the marker
	 * "###ONE###" will be written.
	 *
	 * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 * @param string $content the marker's content, may be empty
	 * @param string $prefix prefix to the marker name (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return void
	 */
	public function setMarker($markerName, $content, $prefix = '') {
		$unifiedMarkerName = $this->createMarkerName($markerName, $prefix);

		if ($this->isMarkerNameValidWithHashes($unifiedMarkerName)) {
			$this->markers[$unifiedMarkerName] = $content;
		}
	}

	/**
	 * Gets a marker's content.
	 *
	 * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 *
	 * @return string the marker's content or an empty string if the
	 *                marker has not been set before
	 */
	public function getMarker($markerName) {
		$unifiedMarkerName = $this->createMarkerName($markerName);
		if (!isset($this->markers[$unifiedMarkerName])) {
			return '';
		}

		return $this->markers[$unifiedMarkerName];
	}

	/**
	 * Sets a subpart's content.
	 *
	 * Example: If the prefix is "field" and the subpart name is "one", the
	 * subpart "###FIELD_ONE###" will be written.
	 *
	 * If the prefix is empty and the subpart name is "one", the subpart
	 * "###ONE###" will be written.
	 *
	 * @param string $subpartName
	 *        the subpart's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 * @param string $content the subpart's content, may be empty
	 * @param string $prefix prefix to the subpart name (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return void
	 */
	public function setSubpart($subpartName, $content, $prefix = '') {
		$subpartName = $this->createMarkerNameWithoutHashes(
			$subpartName, $prefix
		);

		if (!$this->isMarkerNameValidWithoutHashes($subpartName)) {
			throw new InvalidArgumentException('The value of the parameter $subpartName is not valid.', 1331489182);
		}

		$this->subparts[$subpartName] = $content;
	}

	/**
	 * Sets a marker based on whether the int content is non-zero.
	 *
	 * If (int)$content is non-zero, this function sets the marker's content, working
	 * exactly like setMarker($markerName, $content, $markerPrefix).
	 *
	 * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 * @param mixed $content content with which the marker will be filled, may be empty
	 * @param string $markerPrefix prefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return bool TRUE if the marker content has been set, FALSE otherwise
	 *
	 * @see setMarkerIfNotEmpty
	 */
	public function setMarkerIfNotZero($markerName, $content, $markerPrefix = '') {
		$condition = ((int)$content) !== 0;
		if ($condition) {
			$this->setMarker($markerName, ((string) $content), $markerPrefix);
		}
		return $condition;
	}

	/**
	 * Sets a marker based on whether the (string) content is non-empty.
	 * If $content is non-empty, this function sets the marker's content,
	 * working exactly like setMarker($markerName, $content, $markerPrefix).
	 *
	 * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 * @param string $content content with which the marker will be filled, may be empty
	 * @param string $markerPrefix prefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return bool TRUE if the marker content has been set, FALSE otherwise
	 *
	 * @see setMarkerIfNotZero
	 */
	public function setMarkerIfNotEmpty($markerName, $content, $markerPrefix = '') {
		$condition = !empty($content);
		if ($condition) {
			$this->setMarker($markerName, $content, $markerPrefix);
		}
		return $condition;
	}

	/**
	 * Checks whether a subpart is visible.
	 *
	 * Note: If the subpart to check does not exist, this function will return
	 * FALSE.
	 *
	 * @param string $subpartName name of the subpart to check (without the ###), must not be empty
	 *
	 * @return bool TRUE if the subpart is visible, FALSE otherwise
	 */
	public function isSubpartVisible($subpartName) {
		if ($subpartName === '') {
			return FALSE;
		}

		return (isset($this->subparts[$subpartName])
			&& !isset($this->subpartsToHide[$subpartName]));
	}

	/**
	 * Takes a comma-separated list of subpart names and sets them to hidden. In
	 * the process, the names are changed from 'aname' to '###BLA_ANAME###' and
	 * used as keys.
	 *
	 * Example: If the prefix is "field" and the list is "one,two", the subparts
	 * "###FIELD_ONE###" and "###FIELD_TWO###" will be hidden.
	 *
	 * If the prefix is empty and the list is "one,two", the subparts
	 * "###ONE###" and "###TWO###" will be hidden.
	 *
	 * @param string $subparts comma-separated list of at least 1 subpart name to hide (case-insensitive, will get uppercased)
	 * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return void
	 */
	public function hideSubparts($subparts, $prefix = '') {
		$subpartNames = GeneralUtility::trimExplode(',', $subparts, TRUE);

		$this->hideSubpartsArray($subpartNames, $prefix);
	}

	/**
	 * Takes an array of subpart names and sets them to hidden. In the process,
	 * the names are changed from 'aname' to '###BLA_ANAME###' and used as keys.
	 *
	 * Example: If the prefix is "field" and the array has two elements "one"
	 * and "two", the subparts "###FIELD_ONE###" and "###FIELD_TWO###" will be
	 * hidden.
	 *
	 * If the prefix is empty and the array has two elements "one" and "two",
	 * the subparts "###ONE###" and "###TWO###" will be hidden.
	 *
	 * @param string[] $subparts subpart names to hide (may be empty, case-insensitive, will get uppercased)
	 * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return void
	 */
	public function hideSubpartsArray(array $subparts, $prefix = '') {
		foreach ($subparts as $currentSubpartName) {
			$fullSubpartName = $this->createMarkerNameWithoutHashes(
				$currentSubpartName,
				$prefix
			);

			$this->subpartsToHide[$fullSubpartName] = TRUE;
		}
	}

	/**
	 * Takes a comma-separated list of subpart names and unhides them if they
	 * have been hidden beforehand.
	 *
	 * Note: All subpartNames that are provided with the second parameter will
	 * not be unhidden. This is to avoid unhiding subparts that are hidden by
	 * the configuration.
	 *
	 * In the process, the names are changed from 'aname' to '###BLA_ANAME###'.
	 *
	 * Example: If the prefix is "field" and the list is "one,two", the subparts
	 * "###FIELD_ONE###" and "###FIELD_TWO###" will be unhidden.
	 *
	 * If the prefix is empty and the list is "one,two", the subparts
	 * "###ONE###" and "###TWO###" will be unhidden.
	 *
	 * @param string $subparts
	 *        comma-separated list of at least 1 subpart name to unhide (case-insensitive, will get uppercased), must not be empty
	 * @param string $permanentlyHiddenSubparts
	 *        comma-separated list of subpart names that shouldn't get unhidden
	 * @param string $prefix
	 *        prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return void
	 */
	public function unhideSubparts(
		$subparts, $permanentlyHiddenSubparts = '', $prefix = ''
	) {
		$subpartNames = GeneralUtility::trimExplode(',', $subparts, TRUE);

		$hiddenSubpartNames = GeneralUtility::trimExplode(
			',', $permanentlyHiddenSubparts, TRUE
		);

		$this->unhideSubpartsArray($subpartNames, $hiddenSubpartNames, $prefix);
	}

	/**
	 * Takes an array of subpart names and unhides them if they have been hidden
	 * beforehand.
	 *
	 * Note: All subpartNames that are provided with the second parameter will
	 * not be unhidden. This is to avoid unhiding subparts that are hidden by
	 * the configuration.
	 *
	 * In the process, the names are changed from 'aname' to '###BLA_ANAME###'.
	 *
	 * Example: If the prefix is "field" and the array has two elements "one"
	 * and "two", the subparts "###FIELD_ONE###" and "###FIELD_TWO###" will be
	 * unhidden.
	 *
	 * If the prefix is empty and the array has two elements "one" and "two",
	 * the subparts "###ONE###" and "###TWO###" will be unhidden.
	 *
	 * @param string[] $subparts subpart names to unhide (may be empty, case-insensitive, will get uppercased)
	 * @param string[] $permanentlyHiddenSubparts subpart names that shouldn't get unhidden
	 * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return void
	 */
	public function unhideSubpartsArray(
		array $subparts, array $permanentlyHiddenSubparts = array(), $prefix = ''
	) {
		foreach ($subparts as $currentSubpartName) {
			// Only unhide the current subpart if it is not on the list of
			// permanently hidden subparts (e.g. by configuration).
			if (!in_array($currentSubpartName, $permanentlyHiddenSubparts, TRUE)) {
				$currentMarkerName = $this->createMarkerNameWithoutHashes(
					$currentSubpartName, $prefix
				);
				unset($this->subpartsToHide[$currentMarkerName]);
			}
		}
	}

	/**
	 * Sets or hides a marker based on $condition.
	 * If $condition is TRUE, this function sets the marker's content, working
	 * exactly like setMarker($markerName, $content, $markerPrefix).
	 * If $condition is FALSE, this function removes the wrapping subpart,
	 * working exactly like hideSubparts($markerName, $wrapperPrefix).
	 *
	 * @param string $markerName
	 *        the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 * @param bool $condition
	 *        if this is TRUE, the marker will be filled, otherwise the wrapped marker will be hidden
	 * @param string $content
	 *        content with which the marker will be filled, may be empty
	 * @param string $markerPrefix
	 *        prefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
	 * @param string $wrapperPrefix
	 *        prefix to the subpart name for hiding (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return bool TRUE if the marker content has been set, FALSE if
	 *                 the subpart has been hidden
	 *
	 * @see setMarkerContent
	 * @see hideSubparts
	 */
	public function setOrDeleteMarker($markerName, $condition, $content,
		$markerPrefix = '', $wrapperPrefix = ''
	) {
		if ($condition) {
			$this->setMarker($markerName, $content, $markerPrefix);
		} else {
			$this->hideSubparts($markerName, $wrapperPrefix);
		}

		return $condition;
	}

	/**
	 * Sets or hides a marker based on whether the int content is non-zero.
	 *
	 * If (int)$content is non-zero, this function sets the marker's content,
	 * working exactly like setMarker($markerName, $content,
	 * $markerPrefix).
	 * If (int)$condition is zero, this function removes the wrapping
	 * subpart, working exactly like hideSubparts($markerName, $wrapperPrefix).
	 *
	 * @param string $markerName
	 *        the marker's name without the ### signs, case-insensitive, will get uppercased, must not be* empty
	 * @param mixed $content
	 *        content with which the marker will be filled, may be empty
	 * @param string $markerPrefix
	 *        prefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
	 * @param string $wrapperPrefix
	 *        prefix to the subpart name for hiding (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return bool TRUE if the marker content has been set, FALSE if
	 *                 the subpart has been hidden
	 *
	 * @see setOrDeleteMarker
	 * @see setOrDeleteMarkerIfNotEmpty
	 * @see setMarkerContent
	 * @see hideSubparts
	 */
	public function setOrDeleteMarkerIfNotZero($markerName, $content,
		$markerPrefix = '', $wrapperPrefix = ''
	) {
		return $this->setOrDeleteMarker(
			$markerName,
			((int)$content) !== 0,
			((string)$content),
			$markerPrefix,
			$wrapperPrefix
		);
	}

	/**
	 * Sets or hides a marker based on whether the (string) content is
	 * non-empty.
	 * If $content is non-empty, this function sets the marker's content,
	 * working exactly like setMarker($markerName, $content,
	 * $markerPrefix).
	 * If $condition is empty, this function removes the wrapping subpart,
	 * working exactly like hideSubparts($markerName, $wrapperPrefix).
	 *
	 * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
	 * @param string $content content with which the marker will be filled, may be empty
	 * @param string $markerPrefix prefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
	 * @param string $wrapperPrefix prefix to the subpart name for hiding (may be empty, case-insensitive, will get uppercased)
	 *
	 * @return bool TRUE if the marker content has been set, FALSE if
	 *                 the subpart has been hidden
	 *
	 * @see setOrDeleteMarker
	 * @see setOrDeleteMarkerIfNotZero
	 * @see setMarkerContent
	 * @see hideSubparts
	 */
	public function setOrDeleteMarkerIfNotEmpty($markerName, $content,
		$markerPrefix = '', $wrapperPrefix = ''
	) {
		return $this->setOrDeleteMarker(
			$markerName,
			(!empty($content)),
			$content,
			$markerPrefix,
			$wrapperPrefix
		);
	}

	/**
	 * Creates an uppercase marker (or subpart) name from a given name and an
	 * optional prefix, wrapping the result in three hash signs (###).
	 *
	 * Example: If the prefix is "field" and the marker name is "one", the
	 * result will be "###FIELD_ONE###".
	 *
	 * If the prefix is empty and the marker name is "one", the result will be
	 * "###ONE###".
	 *
	 * @param string $markerName the name of the marker, must not be empty
	 * @param string $prefix an optional prefix, may be empty
	 *
	 * @return string the created marker name (including the hashes), will not be empty
	 */
	private function createMarkerName($markerName, $prefix = '') {
		return '###' . $this->createMarkerNameWithoutHashes($markerName, $prefix) . '###';
	}

	/**
	 * Creates an uppercase marker (or subpart) name from a given name and an
	 * optional prefix, but without wrapping it in hash signs.
	 *
	 * Example: If the prefix is "field" and the marker name is "one", the
	 * result will be "FIELD_ONE".
	 *
	 * If the prefix is empty and the marker name is "one", the result will be
	 * "ONE".
	 *
	 * @param string $markerName the name of the marker, must not be empty
	 * @param string $prefix an optional prefix, may be empty
	 *
	 * @return string the created marker name (without the hashes), will not be empty
	 */
	private function createMarkerNameWithoutHashes($markerName, $prefix = '') {
		// If a prefix is provided, uppercases it and separates it with an underscore.
		if ($prefix !== '') {
			$prefix .= '_';
		}

		return strtoupper($prefix . trim($markerName));
	}

	/**
	 * Renders the complete template.
	 *
	 * @return string the rendered template, might be empty
	 */
	public function render() {
		return $this->replaceMarkersAndSubparts($this->templateCode);
	}

	/**
	 * Retrieves a named subpart, recursively filling in its inner subparts
	 * and markers. Inner subparts that are marked to be hidden will be
	 * substituted with empty strings.
	 *
	 * This function either works on the subpart with the name $key or the
	 * complete HTML template if $key is an empty string.
	 *
	 * @param string $subpartKey
	 *        key of an existing subpart, for example 'LIST_ITEM' (without the ###),
	 *        or an empty string to use the complete HTML template
	 *
	 * @return string the subpart content or an empty string if the subpart is hidden or the subpart name is missing
	 *
	 * @throws InvalidArgumentException if $subpartKey is not valid
	 * @throws tx_oelib_Exception_NotFound if there is no subpart with the provided name
	 */
	public function getSubpart($subpartKey = '') {
		if ($subpartKey === '') {
			return $this->render();
		}
		if (!$this->isMarkerNameValidWithoutHashes($subpartKey)) {
			throw new InvalidArgumentException('The value of the parameter $key is not valid.', 1331489215);
		}
		if (!isset($this->subparts[$subpartKey])) {
			throw new tx_oelib_Exception_NotFound(
				'$key contained the subpart name "' . $subpartKey . '", but only the following subparts are available: (' .
					implode(', ', array_keys($this->subparts)) . ')'
			);
		}
		if (!$this->isSubpartVisible($subpartKey)) {
			return '';
		}

		return $this->replaceMarkersAndSubparts($this->subparts[$subpartKey]);
	}

	/**
	 * Retrieves a named subpart, recursively filling in its inner subparts
	 * and markers. Inner subparts that are marked to be hidden will be
	 * substituted with empty strings.
	 *
	 * This function either works on the subpart with the name $key or the
	 * complete HTML template if $key is an empty string.
	 *
	 * All label markers in the rendered subpart are automatically replaced with their corresponding localized labels,
	 * removing the need use the very expensive setLabels method.
	 *
	 * @param string $subpartKey
	 *        key of an existing subpart, for example 'LIST_ITEM' (without the ###),
	 *        or an empty string to use the complete HTML template
	 *
	 * @return string the subpart content or an empty string if the subpart is hidden or the subpart name is missing
	 *
	 * @throws \BadMethodCallException
	 */
	public function getSubpartWithLabels($subpartKey = '') {
		if ($this->translator === NULL) {
			throw new \BadMethodCallException('Please inject the translator before calling this method.', 1440106254);
		}

		$renderedSubpart = $this->getSubpart($subpartKey);

		$translator = $this->translator;
		return preg_replace_callback(
			self::LABEL_PATTERN,
			function(array $matches) use ($translator) {
				return $translator->translate(strtolower($matches[1]));
			},
			$renderedSubpart
		);
	}

	/**
	 * Recursively replaces all subparts and markers in $templateCode.
	 *
	 * @param string $templateCode the template, may be empty
	 *
	 * @return string the template with all subparts and markers replaced
	 */
	protected function replaceMarkersAndSubparts($templateCode) {
		return $this->replaceMarkers($this->replaceSubparts($templateCode));
	}

	/**
	 * Recursively replaces subparts with their contents.
	 *
	 * @param string $templateCode the template, may be empty
	 *
	 * @return string the template with the subparts replaced
	 */
	protected function replaceSubparts($templateCode) {
		$template = $this;
		return preg_replace_callback(
			self::SUBPART_PATTERN,
			function(array $matches) use ($template) {
				return $template->getSubpart($matches[1]);
			},
			$templateCode
		);
	}

	/**
	 * Replaces all markers with their contents.
	 *
	 * @param string $templateCode the template, may be empty
	 *
	 * @return string the template with the markers replaced
	 */
	protected function replaceMarkers($templateCode) {
		return str_replace(array_keys($this->markers), $this->markers, $templateCode);
	}

	/**
	 * Checks whether a marker name (or subpart name) is valid (including the
	 * leading and trailing hashes ###).
	 *
	 * A valid marker name must be a non-empty string, consisting of uppercase
	 * and lowercase letters ranging A to Z, digits and underscores. It must
	 * start with a lowercase or uppercase letter ranging from A to Z. It must
	 * not end with an underscore. In addition, it must be prefixed and suffixed
	 * with ###.
	 *
	 * @param string $markerName marker name to check (with the hashes), may be empty
	 *
	 * @return bool TRUE if the marker name is valid, FALSE otherwise
	 */
	private function isMarkerNameValidWithHashes($markerName) {
		return isset($this->markers[$markerName])
			|| (bool)preg_match('/^###[a-zA-Z](?:[a-zA-Z0-9_]*[a-zA-Z0-9])?###$/', $markerName);
	}

	/**
	 * Checks whether a marker name (or subpart name) is valid (excluding the
	 * leading and trailing hashes ###).
	 *
	 * A valid marker name must be a non-empty string, consisting of uppercase
	 * and lowercase letters ranging A to Z, digits and underscores. It must
	 * start with a lowercase or uppercase letter ranging from A to Z. It must
	 * not end with an underscore.
	 *
	 * @param string $markerName marker name to check (without the hashes), may be empty
	 *
	 * @return bool TRUE if the marker name is valid, FALSE otherwise
	 */
	private function isMarkerNameValidWithoutHashes($markerName) {
		return $this->isMarkerNameValidWithHashes('###' . $markerName . '###');
	}

	/**
	 * Resets the list of subparts to hide.
	 *
	 * @return void
	 */
	public function resetSubpartsHiding() {
		$this->subpartsToHide = array();
	}
}