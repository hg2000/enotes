<?php

namespace OCA\Enotes\Contracts;

use OCA\Enotes\Db\Settings;

interface IMailAdapter
{
	public function getDefaultSettings(): Settings;
}
