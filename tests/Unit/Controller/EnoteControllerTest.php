<?php

namespace OCA\NotesTutorial\Tests\Unit\Controller;

use OCA\Enotes\Db\BookMapper;
use OCA\Enotes\Service\MailService;
use OCA\Enotes\Service\NoteService;
use OCA\Enotes\Controller\NoteController;
use OCP\IL10N;

use OCA\NotesTutorial\Service\NotFoundException;


class EnoteControllerTest extends TestCase
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

	public function setUp(): void
	{
		$this->request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$this->mailManager = $this->getMockBuilder('OCA\Mail\Contracts\IMailManager')->getMock();
		$this->l = $this->getMockBuilder('OCP\IL10N')->getMock();
		$this->mailSearch = $this->getMockBuilder('OCA\Mail\Contracts\IMailSearch')->getMock();
		$this->accountService = $this->getMockBuilder('OCA\Mail\Service\AccountService')
			->disableOriginalConstructor()
			->getMock();
		$this->aliasesService = $this->getMockBuilder('OCA\Mail\Service\AliasesService')
			->disableOriginalConstructor()
			->getMock();
		$this->mailService = $this->getMockBuilder('OCA\Enotes\Service\MailService')
			->disableOriginalConstructor()
			->getMock();
		$this->noteService = $this->getMockBuilder('OCA\Enotes\Service\NoteService')
			->disableOriginalConstructor()
			->getMock();
		$this->bookMapper = $this->getMockBuilder('OCA\Enotes\Db\BookMapper')
			->disableOriginalConstructor()
			->getMock();
		$this->controller = $this->getMockBuilder('OCA\Enotes\Controller\NoteController')
			->disableOriginalConstructor()
			->getMock();

		$this->controller = new NoteController(
			'enotes',
			$this->request,
			$this->mailManager,
			$this->l,
			$this->mailSearch,
			$this->accountService,
			$this->aliasesService,
			$this->mailService,
			$this->noteService,
			$this->bookMapper,
			'Alice'
		);
	}
}
