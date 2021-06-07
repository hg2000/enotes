<?php

namespace OCA\Enotes\AppInfo;

use OCA\Mail\Contracts\IAttachmentService;
use OCA\Mail\Contracts\IAvatarService;
use OCA\Mail\Contracts\IMailManager;
use OCA\Mail\Contracts\IMailSearch;
use OCA\Mail\Contracts\IMailTransmission;
use OCA\Mail\Contracts\IUserPreferences;
use OCA\Mail\Dashboard\MailWidget;
use OCA\Mail\Events\DraftSavedEvent;
use OCA\Mail\Events\MailboxesSynchronizedEvent;
use OCA\Mail\Events\MessageDeletedEvent;
use OCA\Mail\Events\MessageFlaggedEvent;
use OCA\Mail\Events\MessageSentEvent;
use OCA\Mail\Events\NewMessagesSynchronized;
use OCA\Mail\Events\SynchronizationEvent;
use OCA\Mail\HordeTranslationHandler;
use OCA\Mail\Http\Middleware\ErrorMiddleware;
use OCA\Mail\Http\Middleware\ProvisioningMiddleware;
use OCA\Mail\Listener\AccountSynchronizedThreadUpdaterListener;
use OCA\Mail\Listener\AddressCollectionListener;
use OCA\Mail\Listener\DeleteDraftListener;
use OCA\Mail\Listener\FlagRepliedMessageListener;
use OCA\Mail\Listener\InteractionListener;
use OCA\Mail\Listener\MailboxesSynchronizedSpecialMailboxesUpdater;
use OCA\Mail\Listener\MessageCacheUpdaterListener;
use OCA\Mail\Listener\NewMessageClassificationListener;
use OCA\Mail\Listener\SaveSentMessageListener;
use OCA\Mail\Listener\UserDeletedListener;
use OCA\Mail\Search\Provider;
use OCA\Mail\Service\Attachment\AttachmentService;
use OCA\Mail\Service\AvatarService;
use OCA\Mail\Service\MailTransmission;
use OCA\Mail\Service\Search\MailSearch;
use OCA\Mail\Service\UserPreferenceSevice;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IServerContainer;
use OCP\User\Events\UserDeletedEvent;
use OCP\Util;
use Psr\Container\ContainerInterface;
use OCA\Enotes\MailAdapter;
use OCA\Enotes\Contracts\IMailAdapter;

use OCA\Mail\Service\MailManager;

class Application extends App implements IBootstrap {

	public const APP_ID = 'enotes';

	public function __construct(array $urlParams=array()){
		parent::__construct(self::APP_ID, $urlParams);

	}

	public function register(IRegistrationContext $context): void {

		$context->registerParameter('hostname', Util::getServerHostName());

		$context->registerService('userFolder', function (ContainerInterface $c) {
			$userContainer = $c->get(IServerContainer::class);
			$uid = $c->get('UserId');

			return $userContainer->getUserFolder($uid);
		});

		$context->registerServiceAlias(IMailManager::class, MailManager::class);
		$context->registerServiceAlias(IMailAdapter::class, MailAdapter::class);

	}

	public function boot(IBootContext $context): void {
	}

}
