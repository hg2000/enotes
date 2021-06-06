<?php

namespace OCA\Enotes\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version2Date20210529144156 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();
		$this->addBookTable($schema);
		$this->addNoteTable($schema);
		$this->addSettingsTable($schema);
		return $schema;
	}

	public function addBookTable($schema) {

		if (!$schema->hasTable('enote_book')) {
			$table = $schema->createTable('enote_book');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);

			$table->addColumn('title', 'string', [
				'notnull' => true,
				'length' => 256,
			]);

			$table->addColumn('device_id', 'string', [
				'notnull' => true,
				'length' => 256,
			]);

			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 256,
			]);

			$table->addColumn('hash', 'string', [
				'notnull' => true,
				'length' => 256,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['device_id'], 'device_id_index');
			$table->addIndex(['user_id'], 'user_id_index');
			$table->addUniqueIndex(['hash'], 'uniqueHash');
		}

		return $schema;
	}

	public function addNoteTable($schema) {

		if (!$schema->hasTable('enote_note')) {
			$table = $schema->createTable('enote_note');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);

			$table->addColumn('book_id', 'integer', [
				'notnull' => true,
			]);

			$table->addColumn('content', 'text', [
				'notnull' => true,
			]);

			$table->addColumn('type', 'string', [
				'notnull' => true,
				'length' => 256,
			]);

			$table->addColumn('location', 'string', [
				'notnull' => true,
				'length' => 256,
			]);

			$table->addColumn('hash', 'string', [
				'notnull' => true,
				'length' => 256,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['book_id'], 'book_id_index');
			$table->addUniqueIndex(['hash'], 'uniqueHash');
		}

		return $schema;
	}

	public function addSettingsTable($schema) {

		if (!$schema->hasTable('enote_settings')) {
			$table = $schema->createTable('enote_settings');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);

			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 256,
			]);

			$table->addColumn('mail_accounts', 'text', [
				'notnull' => true,
			]);

			$table->addColumn('types', 'text', [
				'notnull' => true,
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'user_id_index');
			$table->addUniqueIndex(['user_id'], 'user_id_unique');
		}

		return $schema;
	}
}
