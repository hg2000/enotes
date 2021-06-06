<?php

namespace OCA\Enotes\Tests\Unit;

use OCA\Enotes\MailAdapter;
use OCA\Mail\Account;
use OCA\Mail\Db\MailAccount;
use OCA\Mail\Db\MailAccountMapper;
use OCA\Mail\Service\AccountService;
use PHPUnit\Framework\TestCase;

class MailAdapterTest extends TestCase
{
	protected MailAdapter $mailAdapter;

	public function setUp(): void
	{
		$this->userId = 'geronimo';

		$accountParams = [
			'emailAddress' => 'mail1@mail.de',
			'accountId' => '123'
		];

		$account = new MailAccount($accountParams);

		$mailAccountMapper = $this->getMockBuilder(MailAccountMapper::class)
			->disableOriginalConstructor()
			->getMock();

		$mailAccountMapper->method('findById')
			->willReturn($account);


		$accountService = $this->getMockBuilder(AccountService::class)
			->disableOriginalConstructor()
			->getMock();

		$accountService->method('findByUserId')
			->willreturn([$account]);

		$this->mailAdapter = new MailAdapter($accountService, $this->userId);
	}

	/**
	 * A MailAdapter will return the current mailAccountSettings
	 */
	public function testGetMailAccountSettings()
	{
		$result = $this->mailAdapter->getMailAccountSettings($this->userId);
		$this->assertEquals('mail1@mail.de', $result[0]['email']);
		$this->assertEquals('123', $result[0]['id']);
	}
}
