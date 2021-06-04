<?php

namespace OCA\Enotes\Db;

use OCA\Enotes\Service\MailService;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class SettingsMapper extends QBMapper
{
	/**
	 * @var MailService
	 */
	protected $mailService;

	/**
	 * @var array
	 */
	protected $defaultSettings;

	protected $propertiesToSerialize = ['mailAccounts'];

	public function __construct(
		IDbConnection $db,
		MailService $mailService)
	{
		parent::__construct($db, 'enote_settings', Settings::class);
	}

	/**
	 * @param Settings $entity
	 * @return Settings
	 */
	protected function prepareforStorage(Settings $entity): Settings
	{
		$properties = get_object_vars($entity);
		foreach ($properties as $propertyKey => $propertyValue) {
			if (in_array($propertyKey, $this->propertiesToSerialize)) {
				$entity->$propertyKey = serialize($propertyValue);
			}
		}
		return $entity;
	}

	protected function prepareForLoading(Settings $entity)
	{
		$properties = get_object_vars($entity);
		foreach ($properties as $propertyKey => $propertyValue) {
			if (in_array($propertyKey, $this->propertiesToSerialize)) {
				$entity->$propertyKey = unserialize($propertyValue);
			}
		}
		return $entity;
	}

	public function findByUserId(string $userId): Entity
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('enote_settings')
			->where(
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId))
			);
		return $this->findEntity($qb);
	}

	/**
	 * Creates a new entry in the db from an entity
	 * @param Entity $entity the entity that should be created
	 * @psalm-param T $entity the entity that should be created
	 * @return Entity the saved entity with the set id
	 * @psalm-return T the saved entity with the set id
	 * @since 14.0.0
	 */
	public function insert(Entity $entity): Entity
	{
		$entity = $this->prepareForStorage($entity);
		return parent::insert($entity);
	}

	/**
	 * Updates an entry in the db from an entity
	 * @param Entity $entity the entity that should be created
	 * @psalm-param T $entity the entity that should be created
	 * @return Entity the saved entity with the set id
	 * @psalm-return T the saved entity with the set id
	 * @throws \InvalidArgumentException if entity has no id
	 * @since 14.0.0
	 */
	public function update(Entity $entity): Entity
	{
		$entity = $this->prepareforStorage($entity);
		return parent::update($entity);
	}
}

