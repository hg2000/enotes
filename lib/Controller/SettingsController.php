<?php
declare(strict_types=1);

namespace OCA\Enotes\Controller;

use OCA\Enotes\AppInfo\Application;
use OCA\Enotes\Db\SettingsMapper;
use OCA\Enotes\Db\Settings;
use OCA\Enotes\Contracts\IMailAdapter;
use OCP\AppFramework\App;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use Exception;
use OCP\IL10N;
use OCP\IRequest;

class SettingsController extends Controller
{
	protected IL10N $l;

	protected SettingsMapper $settingsMapper;

	protected Settings $settings;

	protected string $userId;

	/**
	 * @var MailAdapterInterface MailAdapterTest
	 */
	protected $mailAdapter;

	public function __construct(
		$appName,
		IL10N $l,
		IRequest $request,
		SettingsMapper $settingsMapper,
		IMailAdapter $mailAdapter,
		?string $UserId
	)
	{
		parent::__construct($appName, $request);
		$this->appName = $appName;
		$this->l = $l;
		$this->settingsMapper = $settingsMapper;
		$this->mailAdapter = $mailAdapter;
		$this->userId = $UserId;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function get()
	{
		try {
			$defaultSettings = $this->mailAdapter->getDefaultSettings();
			$settings = $this->settingsMapper->findByUserId($this->userId);
			$settings->mergeWithDefaultMailaccounts($defaultSettings->getMailAccounts());
		} catch (DoesNotExistException $e) {
			$settings = $defaultSettings;
			$settings->setUserId($this->userId);
		}

		$settingsJson = json_encode($settings);
		return new JSONResponse($settingsJson);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param array $mail
	 * @param array $types
	 */
	public function update(array $settings = [])
	{
		$settingsParams = $settings;
		$isInsert = false;
		try {
			$settings = $this->settingsMapper->findByUserId($this->userId);
		} catch (DoesNotExistException $e) {
			$app = new App(Application::APP_ID);
			$settings = $app->getContainer()->get(Settings::class);
			$isInsert = true;
		}
		$settings->setUserId($this->userId);
		$settings->setMailAccounts($settingsParams['mailAccounts']);
		$settings->setTypes($settingsParams['types']);

		try {
			if ($isInsert) {
				return $this->settingsMapper->insert($settings);
			}
			$this->settingsMapper->update($settings);
		} catch (Exception $e) {
			$message = $this->l->t('error.update') . $e->getMessage();
			return new JSONResponse($message, Http::STATUS_CONFLICT);
		}
	}
}
