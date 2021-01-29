<?php

namespace OCA\Enotes\Db;

use OCA\Enotes\AppInfo\Application;
use OCA\Enotes\Service\MailService;
use OCP\AppFramework\App;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;


class SettingsMapper extends QBMapper {

	/**
	 * @var MailService
	 */
	protected $mailService;

	/**
	 * @var array
	 */
	protected $defaultSettings;

	public function __construct(
		IDbConnection $db,
		MailService $mailService) {
		parent::__construct($db, 'enote_settings', Settings::class);

		$this->mailService = $mailService;
		$this->defaultSettings = [];
		$this->defaultSettings['mailAccounts'] = array_map(function ($v) {
			$mailAccount = [];
			$mailAccount['email'] = $v->getEMailAddress();
			$mailAccount['id'] = $v->getId();
			$mailAccount['active'] = true;
			return $mailAccount;
		}, $this->mailService->getMailAccounts());

		$this->defaultSettings['types'] = [
			[
				'key' => 'kindle',
				'name' => 'Amazon Kindle',
				'senders' => 'no-reply@amazon.com'
			]
		];

	}

	/**
	 * Creates a new empty settings object
	 *
	 * @param string $userId
	 * @return Entity
	 */
	public function create(string $userId): Entity {

		$app = new App(Application::APP_ID);
		$entity = $app->getContainer()->get(Settings::class);
		$entity->setUserId($userId);
		return $entity;
	}

	public function findByUserId(string $userId): Entity {

		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('enote_settings')
			->where(
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId))
			);
		return $this->findEntity($qb);
	}

	public function getDefaultsettings(): array {
		return $this->defaultSettings;
	}
}
