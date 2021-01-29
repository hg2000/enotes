<?php
declare(strict_types=1);

namespace OCA\Enotes\Service;

use OCA\Mail\Service\AccountService;
use OCA\Mail\Contracts\IMailManager;
use OCA\Mail\Db\Mailbox;
use OCA\Mail\Contracts\IMailSearch;

class MailService {

	/**
	 * @var string
	 */
	protected $currentUserId;

	/**
	 * @var AccountService
	 */
	protected $accountService;

	/**
	 * @var IMailManager
	 */
	protected $mailManager;

	/**
	 * @var string
	 */
	public $mailFilter = 'From:no-reply@amazon.com';

	public function __construct(
		AccountService $accountService,
		IMailManager $mailManager,
		IMailSearch $mailSearch,


		?string $UserId
	) {
		$this->accountService = $accountService;
		$this->currentUserId = $UserId;
		$this->mailManager = $mailManager;
		$this->mailSearch = $mailSearch;
	}

	public function getMailAttachments(): array {

		$mailAccounts = $this->getMailAccounts();
		$attachments = [];

		foreach ($mailAccounts as $mailAccount) {
			try {
				$mailbox = $this->mailManager->getMailbox($this->currentUserId, $mailAccount->getId());
				$mails = $this->getMails($mailAccount, $mailbox);
				$folder = $mailAccount->getMailbox($mailbox->getName());
				$attachments = $this->getCsvAttachments($mails, $folder);
			} catch (\Exception $e) {
				if (!empty($attachments)) {
					return $attachments;
				}
				throw $e;
			}
		}
		return $attachments;
	}

	public function getMailAccounts(): array {

		return $this->accountService->findByUserId($this->currentUserId);
	}

	/**
	 * @param MailAccount $mailAccount
	 * TODO: remove Filter Magic number
	 */
	public function getMails($mailAccount, $mailbox): array {

		return $this->mailSearch->findMessages(
			$mailAccount,
			$mailbox,
			$this->mailFilter,
			$cursor = null,
			$limit = 100
		);
	}

	public function getCsvAttachments(array $mails, $folder): array {

		$csvAttachments = [];
		foreach ($mails as $mail) {
			$message = $folder->getMessage($mail->getUid());

			foreach ($message->attachments as $attachmentArray) {
				$attachment = $folder->getAttachment($message->getUid(), $attachmentArray['id']);
				if (preg_match('#\.csv$#', $attachment->getName(), $matches) === 1) {
					$csvAttachments[] = $attachment;
				}
			}
		}
		return $csvAttachments;
	}
}
