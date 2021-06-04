<?php

namespace OCA\Enotes\Tests\Unit\Controller;

use OC\L10N\LazyL10N;
use OCA\Enotes\Db\BookMapper;
use OCA\Enotes\Db\Settings;
use OCA\Enotes\Db\SettingsMapper;
use OCA\Enotes\MailAdapterTest;
use OCA\Enotes\Service\MailService;
use OCA\Enotes\Service\NoteService;
use OCA\Enotes\Controller\SettingsController;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IL10N;

use PHPunit\Framework\TestCase;
use OCA\NotesTutorial\Service\NotFoundException;


class SettingsControllerTest extends TestCase
{
	protected $controller;
	/**
	 * @var IL10N
	 */
	protected $l;
	/**
	 * @var IMailManager
	 */
	protected $mailManager;

	/**
	 * @var string
	 */
	protected $currentUserId;

	/**
	 * @var AccountService
	 */
	protected $accountService;

	/**
	 * @var MailService
	 */
	protected $mailService;

	/**
	 * @var NoteService
	 */
	protected $noteService;

	/**
	 * @var BookMapper
	 */
	protected $bookMapper;

	/**
	 * @var SettingsMapper
	 */
	protected $settingsMapper;

	protected Settings $exampleSettings;

	protected MailAdapterTest $mailAdapter;

	protected array $settingsParams;

	public function setUp(): void
	{
		$this->settings = new Settings();
		$this->settings->setMailAccounts([
				['id' => 1,
					'email' => 'mail1@xyz.de',
					'active' => true
				],
				[
					'id' => 2,
					'email' => 'mail2@xyz.de',
					'active' => true
				]
			]
		);

		$this->defaultSettings = new Settings();
		$this->defaultSettings->setMailAccounts([
				['id' => 1,
					'email' => 'mail1@xyz.de',
					'active' => true
				],
				[
					'id' => 2,
					'email' => 'mail2@xyz.de',
					'active' => true
				],
				[
					'id' => 3,
					'email' => 'mail4@xyz.de',
					'active' => true
				]
			]
		);

		$this->settingsParams = [
			'userId' => 'admin',
			'mailAccounts' => [
				['id' => 1,
					'email' => 'mail1@xyz.de',
					'active' => true
				],
				[
					'id' => 2,
					'email' => 'mail2@xyz.de',
					'active' => true
				],
			]
		];

		$this->settingsMapper = $this->getMockBuilder(SettingsMapper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->settingsMapper->method('findByUserId')->willReturn($this->settings);

		$this->mailAdapter = $this->getMockBuilder(MailAdapterTest::class)
			->getMock();

		$this->mailAdapter->method('getDefaultSettings')
			->willReturn($this->defaultSettings);

		$this->request = $this->getMockBuilder('OCP\IRequest')->getMock();
	}

	protected function createSettingsController()
	{
		$appName = 'enotes';
		$userId = 'alice';

		$l = $this->getMockBuilder(LazyL10N::class)
			->disableOriginalConstructor()
			->getMock();

		return new SettingsController(
			$appName,
			$l,
			$this->request,
			$this->settingsMapper,
			$this->mailAdapter,
			$userId
		);
	}

	/**
	 * For a user who updates settings for the first time,
	 * a new Settings record will be created in the database.
	 */
	public function testCreateSettings()
	{
		$this->settingsMapper->method('findByUserId')
			->willThrowException(new DoesNotExistException('does not exist'));

		$this->settingsMapper->expects($this->once())->method('insert');
		$settingsController = $this->createSettingsController();

		$settingsController->update($this->settingsParams);
	}

	/**
	 * When an account will be added to the default account, it must be added to the userAccount.
	 * The already existing mailAccounts will not e changed.
	 */
	public function testUpdateMailAccounts()
	{
		$userMailAccounts = [
			['id' => 1,
				'email' => 'mail1@xyz.de',
				'active' => false
			],
			[
				'id' => 2,
				'email' => 'mail2@xyz.de',
				'active' => false
			]
		];

		$newMailAccounts = [
			['id' => 1,
				'email' => 'mail1@xyz.de',
				'active' => true
			],
			[
				'id' => 2,
				'email' => 'mail2@xyz.de',
				'active' => true
			],
			[
				'id' => 3,
				'email' => 'mail3@xyz.de',
				'active' => true
			]
		];

		$this->settings->setMailAccounts([]);
		$this->settings->mergeWithDefaultMailaccounts($newMailAccounts);

		$resultMailAccounts = $this->settings->getMailAccounts();

		$this->assertCount(3, $resultMailAccounts);
		$this->assertEquals('mail2@xyz.de', $resultMailAccounts[1]['email']);


		$this->settings->setMailAccounts($userMailAccounts);
		$this->settings->mergeWithDefaultMailaccounts($newMailAccounts);

		$resultMailAccounts = $this->settings->getMailAccounts();

		$this->assertCount(3, $resultMailAccounts);
		$this->assertEquals('mail3@xyz.de', $resultMailAccounts[2]['email']);
		$this->assertFalse($resultMailAccounts[0]['active']);

	}

	/**
	 * For a user who updates settings which already exist in the database,
	 * the existing Settings record will be updated.
	 */
	public function testUpdateSettings()
	{
		$this->settingsMapper->method('findByUserId')
			->willReturn($this->settings);
		$this->settingsMapper->expects($this->once())->method('update');
		$settingsController = $this->createSettingsController();

		$settingsController->update($this->settingsParams);
	}

	/**
	 * A settings controller get method returns a setting object of the current user if it exists.
	 */
	public function testGetStoredSettings()
	{
		$settingsController = $this->createSettingsController();
		$result = $settingsController->get()->getData();
		$decodedResult = json_decode($result);
		$this->assertEquals($decodedResult->mailAccounts[0]->email, $this->settings->getMailAccounts()[0]['email']);
		$this->assertEquals($decodedResult->mailAccounts[1]->email, $this->settings->getMailAccounts()[1]['email']);
	}

	/**
	 * A settings controller returns default settings if no settings are stored for
	 * the current user.
	 */
	public function testGetNewSettings()
	{
		$this->settingsMapper->method('findByUserId')
			->willThrowException(new DoesNotExistException('does not exist'));
		$settingsController = $this->createSettingsController();

		$result = json_decode($settingsController->get()->getData());
		$this->assertEquals('mail4@xyz.de', $result->mailAccounts[2]->email);
	}
}
