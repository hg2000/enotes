<?php
declare(strict_types=1);

namespace OCA\Enotes\Controller;

use OCA\Enotes\Db\SettingsMapper;
use OCA\Enotes\Db\Settings;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IL10N;
use OCP\IRequest;
use OCA\Enotes\Service\MailService;

class SettingsController extends Controller {

	/**
	 * @var MailService
	 */
	protected $mailService;

	/**
	 * @var SettingsMapper
	 */
	protected $settingsMapper;

	/**
	 * @var Settings
	 */
	protected $settings;

	public function __construct(
		$appName,
		IRequest $request,
		MailService $mailService,
		SettingsMapper $settingsMapper,
		Settings $settings,
		?string $UserId
	) {
		parent::__construct($appName, $request);
		$this->appName = $appName;
		$this->mailService = $mailService;
		$this->settingsMapper = $settingsMapper;
		$this->userId = $UserId;
		$this->settings = $settings;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function get() {

		try {
			$this->settings = $this->settingsMapper->findByUserId($this->userId);
			$this->settings->mapWithDefaultSettings($this->settingsMapper->getDefaultsettings());
		} catch (DoesNotExistException $e) {
			$this->settings = $this->settingsMapper->create($this->userId);
			$this->settings->mapWithDefaultSettings($this->settingsMapper->getDefaultsettings());
			$this->settingsMapper->insert($this->settings);
		}
		return new JSONResponse(json_encode($this->settings));;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param array $mail
	 * @param array $types
	 */
	public function update(array $mailAccounts = [], array $types = []) {

		$this->settings = $this->settingsMapper->findByUserId($this->userId);
		$this->settings->setMailAccounts($mailAccounts);
		$this->settings->setTypes($types);
		$this->settingsMapper->update($this->settings);
	}
}
