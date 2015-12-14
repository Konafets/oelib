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
 * This class checks the extension configuration (TS setup) and some data for
 * basic sanity. This works for FE plug-ins, BE modules and free-floating data
 * structures.
 *
 * Functions for checking a class (optionally with a flavor) must follow the
 * naming schema "check_classname" or "check_classname_flavor"
 * (if a flavor is used).
 *
 * Example: The check method for objects of the class "tx_seminars_seminarbag"
 * (without any special flavor) must be named "check_tx_seminars_seminarbag".
 * The check method for objects of the class "tx_seminars_pi1" with the flavor
 * "seminar_registration" needs to be named
 * "check_tx_seminars_pi1_seminar_registration".
 *
 * The correct functioning of this class does not rely on any HTML templates or
 * language files so it works even under the worst of circumstances.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_ConfigCheck {
	/**
	 * the object whose configuration should be checked
	 *
	 * @var Tx_Oelib_TemplateHelper|Tx_Oelib_Interface_ConfigurationCheckable
	 */
	protected $objectToCheck = NULL;

	/** @var string the (cached) class name of $this->objectToCheck */
	private $className = '';

	/**
	 * @var string the "flavor" of the object in case the class name does
	 *             not to sufficiently indicate exactly which configuration
	 *             values to check
	 */
	private $flavor = '';

	/**
	 * @var string the error to return (or an empty string if there is no
	 *             error)
	 */
	private $errorText = '';

	/**
	 * The constructor.
	 *
	 * @param Tx_Oelib_TemplateHelper $objectToCheck
	 *        the object that will be checked for configuration problems
	 */
	public function __construct(Tx_Oelib_TemplateHelper $objectToCheck) {
		$this->objectToCheck = $objectToCheck;
		$this->className = get_class($this->objectToCheck);
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->objectToCheck);
	}

	/**
	 * Sets the "flavor" of the object to check. The flavor is used to
	 * differentiate between different kinds of objects of the same class,
	 * e.g. the list view and the single view (which both are pi1 objects).
	 *
	 * @param string $flavor
	 *        a short string identifying the "flavor" of the object to check (may be empty)
	 *
	 * @return void
	 */
	public function setFlavor($flavor) {
		$this->flavor = $flavor;
	}

	/**
	 * Returns the current flavor.
	 *
	 * @return string the current flavor (or an empty string if no flavor
	 *                is set)
	 */
	public function getFlavor() {
		return $this->flavor;
	}

	/**
	 * Detects the class of the object to check and performs the sanity checks.
	 * If everything is okay, an empty string is returned.
	 * If there are errors, the first error is returned (not wrapped).
	 * The error message always is in English.
	 *
	 * If there is more than one error message, the first error needs to be
	 * fixed before the second error can be seen. This is intended as some
	 * errors may cause a row of other errors which disappear when the first
	 * error has been fixed.
	 *
	 * Note: This function expected $this->checkByClassNameAndFlavor() to
	 * be defined!
	 *
	 * @return string an error message (or an empty string)
	 */
	public function checkIt() {
		$this->checkByClassNameAndFlavor();

		return $this->getRawMessage();
	}

	/**
	 * Detects the class of the object to check and performs the sanity checks.
	 * If everything is okay, an empty string is returned.
	 * If there are errors, the first error is returned (wrapped by wrap()).
	 * The error message always is in English.
	 *
	 * If there is more than one error message, the first error needs to be
	 * fixed before the second error can be seen. This is intended as some
	 * errors may cause a row of other errors which disappear when the first
	 * error has been fixed.
	 *
	 * Note: This function expected $this->checkByClassNameAndFlavor() to be
	 * defined!
	 *
	 * @return string an error message wrapped by wrap() (or an empty string)
	 */
	public function checkItAndWrapIt() {
		$this->checkByClassNameAndFlavor();

		return $this->getWrappedMessage();
	}

	/**
	 * Calls the correct configuration checks, depending on the class name of
	 * $this->objectToCheck and (if applicable) on $this->flavor.
	 *
	 * @return void
	 */
	protected function checkByClassNameAndFlavor() {
		$checkFunctionName = 'check_'.$this->className;
		if (!empty($this->flavor)) {
			$checkFunctionName .= '_'.$this->flavor;
		}

		// Check whether a check for the corresponding class exists.
		if (method_exists($this, $checkFunctionName)) {
			$this->$checkFunctionName();
		} else {
			trigger_error(
				'No configuration check '.$checkFunctionName.' created yet.'
			);
		}
	}

	/**
	 * Sets the error message in $this->errorText (unless no other error message
	 * has already been set).
	 *
	 * If $this->errorText is empty, it will be set to $message.
	 *
	 * $message should explain what the problem is, what its negative effects
	 * are and what the user can do to fix the problem.
	 *
	 * If $this->errorText is non-empty or $message is empty,
	 * this function is a no-op.
	 *
	 * @param string $message error text to set (may be empty)
	 *
	 * @return void
	 */
	public function setErrorMessage($message) {
		if (!empty($message) && empty($this->errorText)) {
			$this->errorText = $message;
		}
	}

	/**
	 * Sets the error message, consisting of $explanation and a request to
	 * change the TS setup variable $fieldName (with the current TS setup path
	 * prepended). If $canUseFlexforms is TRUE, the possibility to change the
	 * variable via flexforms is mentioned as well.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $explanation
	 *        error text to set (may be empty)
	 *
	 * @return void
	 */
	protected function setErrorMessageAndRequestCorrection(
		$fieldName, $canUseFlexforms, $explanation
	) {
		$message = $explanation
			.' Please correct the TS setup variable <strong>'
			.$this->getTSSetupPath().$fieldName.'</strong> in your TS '
			.'template setup';
		if ($canUseFlexforms) {
			$message .= ' or via FlexForms';
		}
		$message .= '.';
		$this->setErrorMessage($message);
	}

	/**
	 * Returns an empty string if there are no errors.
	 * Otherwise, returns $this->errorText.
	 *
	 * Use this method if you want to process this message furether, e.g.
	 * for bubbling it up to other configcheck objects.
	 *
	 * @return string $this->errorText (or an empty string if there are no
	 *                errors)
	 */
	public function getRawMessage() {
		return $this->errorText;
	}

	/**
	 * Returns an empty string if there are no errors.
	 * Otherwise, returns $this->errorText wrapped by $this->wrap().
	 *
	 * Use this method if you want to display this message pretty
	 * directly and it doesn't need to get handled to other configcheck
	 * objects.
	 *
	 * @return string $this->errorText wrapped by $this->wrap (or an empty
	 *                string if there are no errors)
	 */
	public function getWrappedMessage() {
		$result = '';

		if (!empty($this->errorText)) {
			$result = $this->wrap($this->errorText);
		}

		return $result;
	}

	/**
	 * Wraps $message in (in this case) <p></p>, styled nicely alarming,
	 * with the lang attribute set to "en".
	 * In addition, the message is prepended by "Configuration check warning: "
	 * and followed by "When that is done, please empty the FE cache and
	 * reload this page."
	 *
	 * This wrapping method can be overwritten for other wrappings.
	 *
	 * @param string $message text to be wrapped (may be empty)
	 *
	 * @return string $message wrapped in <p></p>
	 */
	protected function wrap($message) {
		return '<p lang="en" style="color: #000; background: #fff; '
			.'padding: .4em; border: 3px solid #f00; clear: both;">'
			.'<strong>Configuration check warning:</strong><br />'
			.$message
			.'<br />When that is done, please empty the '
			.'<acronym title="front-end">FE</acronym> cache and reload '
			.'this page.'
			.'<br /><em>The configuration check for this extension can be '
			.'disabled in the extension manager.</em>'
			.'</p>';
	}

	/**
	 * Checks whether the static template has been included.
	 *
	 * @return void
	 */
	protected function checkStaticIncluded() {
		if (!$this->objectToCheck->getConfValueBoolean('isStaticTemplateLoaded')) {
			$this->setErrorMessage(
				'The static template is not included. This has the effect '
					.'that important default values do not get set. To fix '
					.'this, please include this extension\'s template under '
					.'<em>Include static (from extensions)</em> in your TS '
					.'template.'
			);
		}
	}

	/**
	 * Checks whether the HTML template is provided and the file exists.
	 *
	 * @param bool $canUseFlexforms
	 *        whether the template can also be selected via flexforms
	 *
	 * @return void
	 */
	protected function checkTemplateFile($canUseFlexforms = FALSE) {
		if (TYPO3_MODE === 'BE') {
			return;
		}

		$this->checkForNonEmptyString(
			'templateFile', $canUseFlexforms, 's_template_special',
			'This value specifies the HTML template which is essential when ' .
				'creating any output from this extension.'
		);

		if (($this->getFrontEndController() !== NULL)
			&& $this->objectToCheck->hasConfValueString('templateFile', 's_template_special')
		) {
			$rawFileName = $this->objectToCheck->getConfValueString(
				'templateFile',
				's_template_special',
				TRUE
			);
			if (!is_file($this->getFrontEndController()->tmpl->getFileName($rawFileName))) {
				$message = 'The specified HTML template file <strong>'
					.htmlspecialchars($rawFileName)
					.'</strong> cannot be read. '
					.'The HTML template file is essential when creating any '
					.'output from this extension. '
					.'Please either create the file <strong>'.$rawFileName
					.'</strong> or select an existing file using the TS setup '
					.'variable <strong>'.$this->getTSSetupPath()
					.'templateFile</strong>';
				if ($canUseFlexforms) {
					$message .= ' or via FlexForms';
				}
				$message .= '.';
				$this->setErrorMessage($message);
			}
		}
	}

	/**
	 * Checks whether the CSS file (if a name is provided) actually is a file.
	 * If no file name is provided, no error will be displayed as this is
	 * perfectly allowed.
	 *
	 * @return void
	 */
	protected function checkCssFileFromConstants() {
		if ($this->objectToCheck->hasConfValueString('cssFile')) {
			$message = 'The TS setup variable <strong>'.$this->getTSSetupPath()
				.'cssFile</strong> is set, but should not be set. You will have to unset '
				.'the TS setup variable and set <strong>'.$this->getTSSetupPath()
				.'cssFile</strong> in your TS constants instead.';
			$this->setErrorMessage($message);
		} else {
			$message = '';
		}

		$frontEndController = $this->getFrontEndController();
		$typoScriptSetupPage =& $frontEndController->tmpl->setup['page.'];
		$fileName = $typoScriptSetupPage['includeCSS.'][$this->objectToCheck->prefixId];
		if (!empty($fileName)) {
			$fileName = $frontEndController->tmpl->getFileName($fileName);
			if (!is_file($fileName)) {
				$message .= 'The specified CSS file <strong>'
					.htmlspecialchars($fileName)
					.'</strong> cannot be read. '
					.'If that constant does not point to an existing file, no '
					.'special CSS will be used for styling this extension\'s '
					.'HTML. Please either create the file <strong>'.$fileName
					.'</strong> or select an existing file using the TS '
					.'constant <strong>'.$this->getTSSetupPath()
					.'cssFile</strong>'
					.'. If you do not want to use any special CSS, you '
					.'can set that variable to an empty string.';
				$this->setErrorMessage($message);
			}
		}
	}

	/**
	 * Checks the CSS class names provided in the TS setup for validity.
	 * Empty values are considered as valid.
	 *
	 * @deprecated 2015-03-01
	 *
	 * @return void
	 */
	protected function checkCssClassNames() {
		GeneralUtility::logDeprecatedFunction();

		$cssEntries = $this->objectToCheck->getPrefixedMarkers('class');

		foreach ($cssEntries as $currentCssEntry) {
			$setupVariable = strtolower($currentCssEntry);
			$cssClassName = $this->objectToCheck->getConfValueString($setupVariable);
			if (!preg_match('/^[A-Za-z0-9\-_\:\.]*$/', $cssClassName)) {
				$message = 'The specified CSS class name <strong>'
					.htmlspecialchars($cssClassName)
					.'</strong> is invalid. '
					.'This will cause the class to not get correctly applied '
					.'in web browsers. '
					.'Please set the TS setup variable <strong>'
					.$this->getTSSetupPath().$setupVariable
					.'</strong> to a valid CSS class or an empty string.';
				$this->setErrorMessage($message);
			}
		}
	}



	/**
	 * Checks whether a configuration value contains a non-empty-string.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for
	 *        and why it needs to be non-empty, must not be empty
	 *
	 * @return void
	 */
	public function checkForNonEmptyString(
		$fieldName, $canUseFlexforms, $sheet, $explanation
	) {
		$value = $this->objectToCheck->getConfValueString($fieldName, $sheet);
		$this->checkForNonEmptyStringValue(
			$value,
			$fieldName,
			$canUseFlexforms,
			$explanation
		);
	}

	/**
	 * Checks whether a provided value is a non-empty string. The
	 * value to check must be provided as a parameter and is not fetched
	 * automatically; the $fieldName parameter is only used to create the
	 * warning message.
	 *
	 * @param string $value
	 *        the value to check
	 * @param string $fieldName
	 *        TS setup field name to mention in the warning, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for
	 *        and why it needs to be non-empty, must not be empty
	 *
	 * @return void
	 */
	protected function checkForNonEmptyStringValue(
		$value, $fieldName, $canUseFlexforms, $explanation
	) {
		if ($value === '') {
			$message = 'The TS setup variable <strong>'
				.$this->getTSSetupPath().$fieldName
				.'</strong> is empty, but needs to be non-empty. '.$explanation;
			$this->setErrorMessageAndRequestCorrection(
				$fieldName,
				$canUseFlexforms,
				$message
			);
		}
	}

	/**
	 * Checks whether a configuration value is non-empty and lies within a set
	 * of allowed values.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 * @param string[] $allowedValues
	 *        allowed values (must not be empty)
	 *
	 * @return void
	 */
	protected function checkIfSingleInSetNotEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation, array $allowedValues
	) {
		$this->checkForNonEmptyString(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
		$this->checkIfSingleInSetOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation,
			$allowedValues
		);
	}

	/**
	 * Checks whether a configuration value either is empty or lies within a
	 * set of allowed values.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 * @param string[] $allowedValues
	 *        allowed values (must not be empty)
	 *
	 * @return void
	 */
	protected function checkIfSingleInSetOrEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation, array $allowedValues
	) {
		if ($this->objectToCheck->hasConfValueString($fieldName, $sheet)) {
			$value = $this->objectToCheck->getConfValueString($fieldName, $sheet);
			$this->checkIfSingleInSetOrEmptyValue(
				$value,
				$fieldName,
				$canUseFlexforms,
				$explanation,
				$allowedValues
			);
		}
	}

	/**
	 * Checks whether a provided value either is empty or lies within a
	 * set of allowed values. The value to check must be provided as a parameter
	 * and is not fetched automatically; the $fieldName parameter is only used
	 * to create the warning message.
	 *
	 * @param string $value
	 *        the value to check
	 * @param string $fieldName
	 *        TS setup field name to mention in the warning, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 * @param string[] $allowedValues
	 *        allowed values (must not be empty)
	 *
	 * @return void
	 */
	protected function checkIfSingleInSetOrEmptyValue(
		$value, $fieldName, $canUseFlexforms, $explanation, array $allowedValues
	) {
		if (!empty($value) && !in_array($value, $allowedValues, TRUE)) {
			$overviewOfValues = '('.implode(', ', $allowedValues).')';
			$message = 'The TS setup variable <strong>'
				.$this->getTSSetupPath().$fieldName
				.'</strong> is set to the value <strong>'
				.htmlspecialchars($value).'</strong>, but only the '
				.'following values are allowed: '
				.'<br /><strong>'.$overviewOfValues.'</strong><br />'
				.$explanation;
			$this->setErrorMessageAndRequestCorrection(
				$fieldName,
				$canUseFlexforms,
				$message
			);
		}
	}

	/**
	 * Checks whether a configuration value has a boolean value.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfBoolean($fieldName, $canUseFlexforms, $sheet, $explanation) {
		$this->checkIfSingleInSetNotEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation,
			array('0', '1')
		);
	}

	/**
	 * Checks whether a configuration value has an integer value (or is empty).
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfInteger($fieldName, $canUseFlexforms, $sheet, $explanation) {
		$value = $this->objectToCheck->getConfValueString($fieldName, $sheet);

		if (!preg_match('/^\d*$/', $value)) {
			$message = 'The TS setup variable <strong>'
				.$this->getTSSetupPath().$fieldName
				.'</strong> is set to the value <strong>'
				.htmlspecialchars($value).'</strong>, but only integers are '
				.'allowed. '
				.$explanation;
			$this->setErrorMessageAndRequestCorrection(
				$fieldName,
				$canUseFlexforms,
				$message
			);
		}
	}

	/**
	 * Checks whether a configuration value has an integer value in a specified
	 * range (or is empty).
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param int $minValue
	 *        the first value of the range which is allowed
	 * @param int $maxValue
	 *        the last value of the range which is allowed
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfIntegerInRange(
		$fieldName,
		$minValue,
		$maxValue,
		$canUseFlexforms,
		$sheet,
		$explanation
	) {
		// Checks if our minimum value is bigger then our maximum value and
		// swaps their values if this is the case.
		if ($minValue > $maxValue) {
			$temp = $maxValue;
			$maxValue = $minValue;
			$minValue = $temp;
		}

		$value = $this->objectToCheck->getConfValueInteger($fieldName, $sheet);

		if (($value < $minValue) || ($value > $maxValue)) {
			$message = 'The TS setup variable <strong>'
				.$this->getTSSetupPath().$fieldName
				.'</strong> is set to the value <strong>'
				.htmlspecialchars($value).'</strong>, but only integers from '
				.$minValue.' to '.$maxValue.' are allowed. '
				.$explanation;
			$this->setErrorMessageAndRequestCorrection(
				$fieldName,
				$canUseFlexforms,
				$message
			);
		}
	}

	/**
	 * Checks whether a provided value has an integer value (or is empty). The
	 * value to check must be provided as a parameter and is not fetched
	 * automatically; the $fieldName parameter is only used to create the
	 * warning message.
	 *
	 * @param string $value
	 *        the value to check
	 * @param string $fieldName
	 *        TS setup field name to mention in the warning, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfPositiveIntegerValue(
		$value, $fieldName, $canUseFlexforms, $explanation
	) {
		$this->checkForNonEmptyStringValue(
			$value,
			$fieldName,
			$canUseFlexforms,
			$explanation
		);
		if (!preg_match('/^[1-9]\d*$/', $value)) {
			$message = 'The TS setup variable <strong>'
				.$this->getTSSetupPath().$fieldName
				.'</strong> is set to the value <strong>'
				.htmlspecialchars($value).'</strong>, but only positive '
				.'integers are allowed. '
				.$explanation;
			$this->setErrorMessageAndRequestCorrection(
				$fieldName,
				$canUseFlexforms,
				$message
			);
		}
	}

	/**
	 * Checks whether a configuration value has a positive (thus non-zero)
	 * integer value.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfPositiveInteger(
		$fieldName, $canUseFlexforms, $sheet, $explanation
	) {
		$value = $this->objectToCheck->getConfValueString($fieldName, $sheet);
		$this->checkIfPositiveIntegerValue(
			$value,
			$fieldName,
			$canUseFlexforms,
			$explanation
		);
	}

	/**
	 * Checks whether a configuration value has a positive (thus non-zero)
	 * integer value or is empty.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, may be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfPositiveIntegerOrEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation
	) {
		$value = $this->objectToCheck->getConfValueString($fieldName, $sheet);
		if (!empty($value) && !preg_match('/^[1-9]\d*$/', $value)) {
			$message = 'The TS setup variable <strong>'
				.$this->getTSSetupPath().$fieldName
				.'</strong> is set to the value <strong>'
				.htmlspecialchars($value).'</strong>, but only positive '
				.'integers and empty strings are allowed. '
				.$explanation;
			$this->setErrorMessageAndRequestCorrection(
				$fieldName,
				$canUseFlexforms,
				$message
			);
		}
	}

	/**
	 * Checks whether a configuration value has a positive integer value or is
	 * zero.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfPositiveIntegerOrZero(
		$fieldName, $canUseFlexforms, $sheet, $explanation
	) {
		$value = $this->objectToCheck->getConfValueString($fieldName, $sheet);

		$this->checkForNonEmptyStringValue(
			$value,
			$fieldName,
			$canUseFlexforms,
			$explanation
		);

		if (!preg_match('/^\d+$/', $value)) {
			$message = 'The TS setup variable <strong>'
				.$this->getTSSetupPath().$fieldName
				.'</strong> is set to the value <strong>'
				.htmlspecialchars($value).'</strong>, but only positive '
				.'integers are allowed. '
				.$explanation;

			$this->setErrorMessageAndRequestCorrection(
				$fieldName,
				$canUseFlexforms,
				$message
			);
		}
	}

	/**
	 * Checks whether a configuration value is non-empty and its
	 * comma-separated values lie within a set of allowed values.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 * @param string[] $allowedValues
	 *        allowed values (must not be empty)
	 *
	 * @return void
	 */
	protected function checkIfMultiInSetNotEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation, array $allowedValues
	) {
		$this->checkForNonEmptyString(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
		$this->checkIfMultiInSetOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation,
			$allowedValues
		);
	}

	/**
	 * Checks whether a configuration value either is empty or its
	 * comma-separated values lie within a set of allowed values.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 * @param string[] $allowedValues
	 *        allowed values (must not be empty)
	 *
	 * @return void
	 */
	protected function checkIfMultiInSetOrEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation, array $allowedValues
	) {
		if ($this->objectToCheck->hasConfValueString($fieldName, $sheet)) {
			$allValues = GeneralUtility::trimExplode(
				',',
				$this->objectToCheck->getConfValueString($fieldName, $sheet),
				TRUE
			);

			$overviewOfValues = '('.implode(', ', $allowedValues).')';
			foreach ($allValues as $currentValue) {
				if (!in_array($currentValue, $allowedValues, TRUE)) {
					$message = 'The TS setup variable <strong>'
						.$this->getTSSetupPath().$fieldName
						.'</strong> contains the value <strong>'
						.htmlspecialchars($currentValue).'</strong>, '
						.'but only the following values are allowed: '
						.'<br /><strong>'.$overviewOfValues.'</strong><br />'
						.$explanation;
					$this->setErrorMessageAndRequestCorrection(
						$fieldName,
						$canUseFlexforms,
						$message
					);
				}
			}
		}
	}

	/**
	 * Checks whether a configuration value is non-empty and is one of the
	 * column names of a given DB table.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 * @param string $tableName
	 *        a DB table name (must not be empty)
	 *
	 * @return void
	 */
	public function checkIfSingleInTableNotEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation, $tableName
	) {
		$this->checkIfSingleInSetNotEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation,
			$this->getDbColumnNames($tableName)
		);
	}

	/**
	 * Checks whether a configuration value either is empty or is one of the
	 * column names of a given DB table.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 * @param string $tableName
	 *        a DB table name (must not be empty)
	 *
	 * @return void
	 */
	protected function checkIfSingleInTableOrEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation, $tableName
	) {
		$this->checkIfSingleInSetOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation,
			$this->getDbColumnNames($tableName)
		);
	}

	/**
	 * Checks whether a configuration value is non-empty and its
	 * comma-separated values lie within a set of allowed values.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 * @param string $tableName
	 *        a DB table name (must not be empty)
	 *
	 * @return void
	 */
	protected function checkIfMultiInTableNotEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation, $tableName
	) {
		$this->checkIfMultiInSetNotEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation,
			$this->getDbColumnNames($tableName)
		);
	}

	/**
	 * Checks whether a configuration value either is empty or its
	 * comma-separated values is a column name of a given DB table.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 * @param string $tableName
	 *        a DB table name (must not be empty)
	 *
	 * @return void
	 */
	protected function checkIfMultiInTableOrEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation, $tableName
	) {
		$this->checkIfMultiInSetOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation,
			$this->getDbColumnNames($tableName)
		);
	}

	/**
	 * Checks whether the salutation mode is set correctly.
	 *
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 *
	 * @return void
	 */
	protected function checkSalutationMode($canUseFlexforms = FALSE) {
		$this->checkIfSingleInSetNotEmpty(
			'salutation',
			$canUseFlexforms,
			'sDEF',
			'This variable controls the salutation mode (formal or informal). '
				.'If it is not set correctly, some output cannot be created '
				.'at all.',
			array('formal', 'informal')
		);
	}

	/**
	 * Gets the path for TS setup where $this->objectToCheck's configuration is
	 * located. This includes the extension key, (possibly) something like pi1
	 * and the trailing dot.
	 *
	 * @return string the TS setup configuration path including the
	 *                trailing dot, e.g. "plugin.tx_seminars_pi1."
	 */
	protected function getTSSetupPath() {
		if ($this->objectToCheck instanceof Tx_Oelib_Interface_ConfigurationCheckable) {
			return $this->objectToCheck->getTypoScriptNamespace();
		}

		$result = 'plugin.tx_' . $this->objectToCheck->extKey;
		$matches = array();
		if (preg_match('/_pi[0-9]+$/', $this->className, $matches)) {
			$result .= $matches[0];
		}

		$result .= '.';

		return $result;
	}

	/**
	 * Retrieves the column names of a given DB table name.
	 *
	 * @param string $tableName
	 *        the name of a existing DB table (must not be empty, must exist)
	 *
	 * @return string[] column names as values
	 */
	protected function getDbColumnNames($tableName) {
		return array_keys(Tx_Oelib_Db::getColumnsInTable($tableName));
	}

	/**
	 * Checks whether a configuration value matches a regular expression.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 * @param string $regExp
	 *        a regular expression (including the delimiting slashes)
	 *
	 * @return void
	 */
	protected function checkRegExp(
		$fieldName, $canUseFlexforms, $sheet, $explanation, $regExp
	) {
		$value = $this->objectToCheck->getConfValueString($fieldName, $sheet);

		if (!preg_match($regExp, $value)) {
			$message = 'The TS setup variable <strong>'.$this->getTSSetupPath()
				.$fieldName.'</strong> contains the value <strong>'
				.htmlspecialchars($value).'</strong> which isn\'t valid. '
				.$explanation;
			$this->setErrorMessageAndRequestCorrection(
				$fieldName,
				$canUseFlexforms,
				$message
			);
		}
	}

	/**
	 * Checks whether a configuration value is non-empty and matches a regular
	 * expression.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 * @param string $regExp
	 *        a regular expression (including the delimiting slashes)
	 *
	 * @return void
	 */
	protected function checkRegExpNotEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation, $regExp
	) {
		$this->checkForNonEmptyString(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
		$this->checkRegExp(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation,
			$regExp
		);
	}

	/**
	 * Checks whether a configuration value either is empty or contains a
	 * comma-separated list of integers (in this case, PIDs).
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfPidListOrEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation
	) {
		$this->checkRegExp(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation,
			'/^([0-9]+(,( *)[0-9]+)*)?$/'
		);
	}

	/**
	 * Checks whether a configuration value is non-empty and contains a
	 * comma-separated list of integers (in this case, PIDs).
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfPidListNotEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation
	) {
		$this->checkForNonEmptyString(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
		$this->checkIfPidListOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
	}

	/**
	 * Checks whether a configuration value is non-empty and contains a
	 * comma-separated list of front-end PIDs.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfFePagesNotEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation
	) {
		$this->checkForNonEmptyString(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
		$this->checkIfFePagesOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
	}

	/**
	 * Checks whether a configuration value is non-empty and contains a
	 * single front-end PID.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfSingleFePageNotEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation
	) {
		$this->checkIfPositiveInteger(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
		$this->checkIfFePagesOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
	}

	/**
	 * Checks whether a configuration value either is empty or contains a
	 * single front-end PID.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfSingleFePageOrEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation
	) {
		$this->checkIfInteger($fieldName, $canUseFlexforms, $sheet, $explanation);
		$this->checkIfFePagesOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
	}

	/**
	 * Checks whether a configuration value either is empty or contains a
	 * comma-separated list of front-end PIDs.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfFePagesOrEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation
	) {
		$pids = $this->objectToCheck->getConfValueString($fieldName, $sheet);

		// Uses the plural if the configuration value is empty or contains a
		// comma.
		if (($pids === '') || (strrpos($pids, ',') !== FALSE )) {
			$message = 'All the selected pages need to be front-end pages so '
				.'that links to them work correctly. '.$explanation;
		} else {
			$message = 'The selected page needs to be a front-end page so that '
				.'links to it work correctly. '.$explanation;
		}
		$this->checkPageTypeOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$message,
			'<199'
		);
	}

	/**
	 * Checks whether a configuration value is non-empty and contains a
	 * comma-separated list of system folder PIDs.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfSysFoldersNotEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation
	) {
		$this->checkForNonEmptyString(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
		$this->checkIfSysFoldersOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
	}

	/**
	 * Checks whether a configuration value is non-empty and contains a
	 * single system folder PID.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfSingleSysFolderNotEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation
	) {
		$this->checkIfPositiveInteger(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
		$this->checkIfSysFoldersOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
	}

	/**
	 * Checks whether a configuration value either is empty or contains a
	 * single system folder PID.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfSingleSysFolderOrEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation
	) {
		$this->checkIfInteger(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
		$this->checkIfSysFoldersOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);
	}

	/**
	 * Checks whether a configuration value either is empty or contains a
	 * comma-separated list of system folder PIDs.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkIfSysFoldersOrEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation
	) {
		$pids = $this->objectToCheck->getConfValueString($fieldName, $sheet);

		// Uses the plural if the configuration value is empty or contains a
		// comma.
		if (($pids === '') || (strrpos($pids, ',') !== FALSE )) {
			$message = 'All the selected pages need to be system folders so '
				.'that data records are tidily separated from front-end '
				.'content. '.$explanation;
		} else {
			$message = 'The selected page needs to be a system folder so that '
				.'data records are tidily separated from front-end content. '
				.$explanation;
		}
		$this->checkPageTypeOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$message,
			'=254'
		);
	}

	/**
	 * Checks whether a configuration value either is empty or contains a
	 * comma-separated list of PIDs that specify pages or a given type.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 * @param string $typeCondition
	 *        a comparison operator with a value that will be used in a SQL
	 *        query to check for the correct page types, for example "<199" or
	 *        "=254", must not be empty
	 *
	 * @return void
	 */
	protected function checkPageTypeOrEmpty(
		$fieldName, $canUseFlexforms, $sheet, $explanation, $typeCondition
	) {
		$this->checkIfPidListOrEmpty(
			$fieldName,
			$canUseFlexforms,
			$sheet,
			$explanation
		);

		if ($this->objectToCheck->hasConfValueString($fieldName, $sheet)) {
			$pids = $this->objectToCheck->getConfValueString($fieldName, $sheet);

			$offendingPids = Tx_Oelib_Db::selectColumnForMultiple(
				'uid',
				'pages',
				'uid IN (' .$pids . ') AND NOT (doktype' . $typeCondition . ')' .
					Tx_Oelib_Db::enableFields('pages')
			);
			$dbResultCount = count($offendingPids);

			if ($dbResultCount > 0) {
				$pageIdPlural = ($dbResultCount > 1) ? 's' : '';
				$bePlural = ($dbResultCount > 1) ? 'are' : 'is';

				$message = 'The TS setup variable <strong>' .
					$this->getTSSetupPath() . $fieldName .
					'</strong> contains the page ID' . $pageIdPlural .
					' <strong>' . implode(',', $offendingPids) . '</strong> ' .
					'which ' . $bePlural . ' of an incorrect page type. ' .
					$explanation.'<br />';
				$this->setErrorMessageAndRequestCorrection(
					$fieldName,
					$canUseFlexforms,
					$message
				);
			}
		}
	}

	/**
	 * This function does not do anything anymore. It used to check whether css_styled_content was installied,
	 * but nowadays we can safely assume that it's always installed.
	 *
	 * @return void
	 */
	protected function checkCssStyledContent() {
	}

	/**
	 * Checks all values within .listView (including .listView itself).
	 *
	 * @param string[] $allowedSortFields
	 *        allowed sort keys for the list view, must not be empty
	 *
	 * @return void
	 */
	protected function checkListView(array $allowedSortFields) {
		$fieldName = 'listView.';

		if (!isset($this->objectToCheck->conf[$fieldName])) {
			$this->setErrorMessageAndRequestCorrection(
				$fieldName,
				FALSE,
				'The TS setup variable group <strong>'.$this->getTSSetupPath()
					.$fieldName.'</strong> is not set. This setting controls '
					.'the list view. '
					.'If this part of the setup is missing, sorting and the '
					.'result browser will not work correctly.'
			);
		} else {
			$this->checkListViewIfSingleInSetNotEmpty(
				'orderBy',
				'This setting controls by which field the list view will be '
					.'sorted. '
					.'If this value is not set correctly, sorting will not '
					.'work correctly.',
				$allowedSortFields
			);
			$this->checkListViewIfSingleInSetNotEmpty(
				'descFlag',
				'This setting controls the default sort order (ascending or '
					.'descending). '
					.'If this value is not set correctly, the list view might '
					.'be sorted the wrong way round.',
				array('0', '1')
			);
			$this->checkListViewIfPositiveInteger(
				'results_at_a_time',
				'This setting controls how many events per page will be '
					.'displayed in the list view. '
					.'If this value is not set correctly, the wrong number of '
					.'events will be displayed.'
			);
			$this->checkListViewIfPositiveInteger(
				'maxPages',
				'This setting controls how many result pages will be linked in '
					.'the list view. '
					.'If this value is not set correctly, the result browser '
					.'will not work correctly.'
			);
		}
	}

	/**
	 * Checks whether a configuration value in listView. is non-empty and lies
	 * within a set of allowed values.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract (within listView.), must not be empty
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 * @param string[] $allowedValues
	 *        allowed values (must not be empty)
	 *
	 * @return void
	 */
	protected function checkListViewIfSingleInSetNotEmpty(
		$fieldName, $explanation, array $allowedValues
	) {
		$fieldSubPath = 'listView.'.$fieldName;
		$value = $this->objectToCheck->getListViewConfValueString($fieldName);

		$this->checkForNonEmptyStringValue(
			$value,
			$fieldSubPath,
			FALSE,
			$explanation
		);
		$this->checkIfSingleInSetOrEmptyValue(
			$value,
			$fieldSubPath,
			FALSE,
			$explanation,
			$allowedValues
		);
	}

	/**
	 * Checks whether a configuration value within listView. has a positive
	 * (thus non-zero) integer value.
	 *
	 * @param string $fieldName
	 *        TS setup field name to extract (within listView.), must not be empty
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	protected function checkListViewIfPositiveInteger($fieldName, $explanation) {
		$fieldSubPath = 'listView.'.$fieldName;
		$value = $this->objectToCheck->getListViewConfValueString($fieldName);

		$this->checkIfPositiveIntegerValue(
			$value,
			$fieldSubPath,
			FALSE,
			$explanation
		);
	}

	/**
	 * Checks whether the locale is set correctly.
	 *
	 * @deprecated 2015-01-12
	 *
	 * @return void
	 */
	public function checkLocale() {
		GeneralUtility::logDeprecatedFunction();

		// Skip this check if Windows is used to avoid a crash of the
		// TYPO3-Winstaller.
		if (TYPO3_OS === 'WIN') {
			return;
		}

		$message = '';
		$installedLocales = $this->getInstalledLocales();
		$frontEndController = $this->getFrontEndController();
		$valueToCheck = isset($frontEndController->config['config']['locale_all'])
			? $frontEndController->config['config']['locale_all'] : '';

		if (empty($installedLocales)) {
			$message = 'There are no locales installed on this web server. Please '
				.'install at least one locale. Otherwise, displayed numbers will '
				.'not be formatted correctly.';
		} else {
			if (empty($valueToCheck)) {
				$message = 'The locale is not configured yet. Please set '
					.'<strong>config.locale_all</strong> in your main template. ';
			} elseif (!$this->isAllowedLocale($valueToCheck)) {
				$message = 'The locale is set to <strong>'.$valueToCheck
					.'</strong>, but this locale is not installed on this '
					.'web server. Please either install the locale <strong>'
					.$valueToCheck.'</strong> or choose one of the installed '
					.'locales for <strong>config.locale_all</strong> in your '
					.'main template. ';
			}

			if ($message !== '') {
				$message .= 'Locales which are installed and therefore available '
					.'are <strong>'.implode(', ', $installedLocales).'</strong>. '
					.'If you intend to set another locale, you need to install '
					.'it on this web server first.';
			}
		}

		$this->setErrorMessage($message);
	}

	/**
	 * Checks whether the key of the locale is within the set of installed
	 * locales.
	 *
	 * @param string $localeKey
	 *        key of a locale, must not be empty
	 *
	 * @return bool whether the locale key is the key of an installed locale
	 */
	private function isAllowedLocale($localeKey) {
		// "UTF-8" is interpreted equally to "utf8". Therefore "-" need to be
		// stripped before comparing the keys.
		$unifiedLocaleKey = str_replace('-', '', strtolower($localeKey));

		$allowedLocales = array();
		foreach ($this->getInstalledLocales() as $key) {
			$allowedLocales[] = str_replace('-', '', strtolower($key));
		}

		return in_array($unifiedLocaleKey, $allowedLocales, TRUE);
	}

	/**
	 * Returns the keys of the locales installed on this web server.
	 *
	 * @return string[] locales installed on this web server, will be empty if there are none
	 */
	public function getInstalledLocales() {
		$result = array();

		foreach (GeneralUtility::trimExplode(LF, shell_exec('locale -a'), TRUE)
		         as $localeKey
		) {
			// The output of "locale -a" contains more lines than we need.
			if (strpos($localeKey, '_') !== FALSE) {
				$result[] = $localeKey;
			}
		}

		return $result;
	}

	/**
	 * Checks that an e-mail address is valid or empty.
	 *
	 * @param string $fieldName
	 *        TS setup field name to mention in the warning, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param bool $unused unused
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	public function checkIsValidEmailOrEmpty(
		$fieldName, $canUseFlexforms, $sheet, $unused, $explanation
	) {
		$value = $this->objectToCheck->getConfValueString($fieldName, $sheet);
		if ($value === '') {
			return;
		}

		if (!GeneralUtility::validEmail($value)) {
			$message = 'The e-mail address in <strong>'.$this->getTSSetupPath()
				.$fieldName.'</strong> is set to <strong>'.$value.'</strong> '
				.'which is not valid. E-mails might not be received as long as '
				.'this address is invalid.<br />';
			$this->setErrorMessageAndRequestCorrection(
				$fieldName, $canUseFlexforms, $message.$explanation
			);
		}
	}

	/**
	 * Checks that an e-mail address is valid and non-empty.
	 *
	 * @param string $fieldName
	 *        TS setup field name to mention in the warning, must not be empty
	 * @param bool $canUseFlexforms
	 *        whether the value can also be set via flexforms (this will be
	 *        mentioned in the error message)
	 * @param string $sheet
	 *        flexforms sheet pointer, eg. "sDEF", will be ignored if
	 *        $canUseFlexforms is set to FALSE
	 * @param bool $allowInternalAddresses
	 *        whether internal addresses ("user@servername") are considered valid
	 * @param string $explanation
	 *        a sentence explaining what that configuration value is needed for,
	 *        must not be empty
	 *
	 * @return void
	 */
	public function checkIsValidEmailNotEmpty(
		$fieldName, $canUseFlexforms, $sheet, $allowInternalAddresses, $explanation
	) {
		$this->checkForNonEmptyString(
			$fieldName, $canUseFlexforms, $sheet, $explanation
		);
		$this->checkIsValidEmailOrEmpty(
			$fieldName, $canUseFlexforms, $sheet, $allowInternalAddresses, $explanation
		);
	}

	/**
	 * Returns the current front-end instance.
	 *
	 * @return tslib_fe|NULL
	 */
	protected function getFrontEndController() {
		return isset($GLOBALS['TSFE']) ? $GLOBALS['TSFE'] : NULL;
	}
}