<?php
/**
 * @package   AkeebaLoginGuard
 * @copyright Copyright (c)2016-2017 Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Prevent direct access
defined('_JEXEC') or die;

class LoginGuardViewCaptive extends JViewLegacy
{
	/**
	 * The TFA method records for the current user which correspond to enabled plugins
	 *
	 * @var  array
	 */
	public $records = array();

	/**
	 * The currently selected TFA method record against which we'll be authenticating
	 *
	 * @var  null|stdClass
	 */
	public $record = null;

	/**
	 * The captive TFA page's rendering options
	 *
	 * @var   array|null
	 */
	public $renderOptions = null;

	/**
	 * The title to display at the top of the page
	 *
	 * @var   string
	 */
	public $title = '';

	/**
	 * Is this an administrator page?
	 *
	 * @var   bool
	 */
	public $isAdmin = false;

	/**
	 * Does the currently selected method allow authenticating against all of its records?
	 *
	 * @var   bool
	 */
	public $allowEntryBatching = false;

	/**
	 * All enabled TFA methods (plugins)
	 *
	 * @var   array
	 */
	public $tfaMethods;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     JViewLegacy::loadTemplate()
	 */
	function display($tpl = null)
	{
		/** @var LoginGuardModelCaptive $model */
		$model = $this->getModel();

		// Load data from the model
		$this->isAdmin         = LoginGuardHelperTfa::isAdminPage();
		$this->records         = $this->get('records');
		$this->record          = $this->get('record');
		$this->tfaMethods      = LoginGuardHelperTfa::getTfaMethods();

		if (!empty($this->records))
		{
			if (!class_exists('LoginGuardModelBackupcodes'))
			{
				require_once JPATH_BASE . '/components/com_loginguard/models/backupcodes.php';
			}

			/** @var LoginGuardModelBackupcodes $codesModel */
			$codesModel = JModelLegacy::getInstance('Backupcodes', 'LoginGuardModel');
			$backupCodesRecord = $codesModel->getBackupCodesRecord();
			$backupCodesRecord->title = JText::_('COM_LOGINGUARD_LBL_BACKUPCODES');
			$this->records[] = $backupCodesRecord;
		}

		// If we only have one record there's no point asking the user to select a TFA method
		if (empty($this->record))
		{
			// Default to the first record
			$this->record = $this->records[0];

			// If we have multiple records try to make this record the default
			if (count($this->records) > 1)
			{
				foreach ($this->records as $record)
				{
					if ($record->default)
					{
						$this->record = $record;

						break;
					}
				}
			}
		}

		$this->renderOptions      = $model->loadCaptiveRenderOptions($this->record);
		$this->allowEntryBatching = isset($this->renderOptions['allowEntryBatching']) ? $this->renderOptions['allowEntryBatching'] : 0;

		// Set the correct layout based on the availability of a TFA record
		$this->setLayout('default');

		if (is_null($this->record) || ($model->getState('task') == 'select'))
		{
			$this->setLayout('select');
		}

		// Which title should I use for the page?
		$this->title = $this->get('PageTitle');

		// Back-end: always show a title in the 'title' module position, not in the page body
		if ($this->isAdmin)
		{
			JToolbarHelper::title(JText::_('COM_LOGINGUARD_HEAD_TFA_PAGE'), 'lock');
			$this->title = '';
		}

		// Get the media version
		JLoader::register('LoginGuardHelperVersion', JPATH_SITE . '/components/com_loginguard/helpers/version.php');
		$mediaVersion = md5(LoginGuardHelperVersion::component('com_loginguard'));

		// Include CSS
		if (version_compare(JVERSION, '3.6.999', 'le'))
		{
			JHtml::_('stylesheet', 'com_loginguard/captive.min.css', array(
				'version'     => $mediaVersion,
				'relative'    => true,
				'detectDebug' => true
			), true, false, false, true);
		}
		else
		{
			JHtml::_('stylesheet', 'com_loginguard/captive.min.css', array(
				'version'       => $mediaVersion,
				'relative'      => true,
				'detectDebug'   => true,
				'pathOnly'      => false,
				'detectBrowser' => true,
			), array(
				'type' => 'text/css',
			));
		}

		// Display the view
		return parent::display($tpl);
	}
}