<?php
namespace OCA\Enotes;

use OCA\Mail\Service\AccountService;
use OCA\Mail\Contracts\IMailManager;
use OCA\Mail\Db\Mailbox;
use OCA\Mail\Contracts\IMailSearch;
use OCA\Enotes\Contracts\IMailAdapter;


use OCA\Enotes\Db\Settings;

class MailAdapter implements IMailAdapter
{
	protected string $currentUserId;

	/**
	 * @var AccountService
	 */
	protected $accountService;

	protected IMailManager $mailManager;

	protected IMailSearch  $mailSearch;

	public function __construct(
		AccountService $accountService,
		?string $userId
	) {
		$this->currentUserId = $userId;
		$this->accountService = $accountService;
	}

	public function getDefaultSettings(): Settings
	{
		$defaultSettings = new Settings();
		$defaultSettings->setMailAccounts($this->getMailAccountSettings());
		$defaultSettings->setTypes($this->getTypes());
		return $defaultSettings;
	}

	public function getMailAccountSettings(): array {

		$accounts =  $this->accountService->findByUserId($this->currentUserId);
		$settings = [];
		foreach ($accounts as $account) {
			$settings[] = [
				'id' => $account->getId(),
				'email' => $account->getEmail(),
				'active' => true
			];
		}
		return $settings;
	}

	public function getTypes(): string {

		return 'no-reply@amazon.com';
	}
}
