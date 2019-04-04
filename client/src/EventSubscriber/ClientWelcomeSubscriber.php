<?php

namespace Drupal\client\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\client\events\ClientWelcomeEvent;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Mail\MailManager;
use Drupal\Core\Session\AccountProxy;

/**
 * Event subscriber for client welcome event.
 */
class ClientWelcomeSubscriber implements EventSubscriberInterface {

  /**
   * The Mail Manager.
   *
   * @var Drupal\Core\Mail\MailManager
   */

  protected $mailManager;

  /**
   * The Logger Factory.
   *
   * @var Drupal\Core\Logger\LoggerChannelFactory
   */

  protected $logger;

  /**
   * The Account Proxy.
   *
   * @var Drupal\Core\Session\AccountProxy
   */

  protected $account;

  /**
   * Constructs the ClientWelcomeSubscriber.
   *
   * @param \Drupal\Core\Mail\MailManager $mail_manager
   *   The Mail Manager Plugin.
   * @param Drupal\Core\Logger\LoggerChannelFactory $logger
   *   The Logger Factory.
   * @param Drupal\Core\Session\AccountProxy $account
   *   The Account Proxy.
   */
  public function __construct(MailManager $mail_manager,
    LoggerChannelFactory $logger,
  AccountProxy $account) {
    $this->mailManager = $mail_manager;
    $this->logger = $logger;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['client.welcome.mail'][] = ['sendWelcomeMail', 0];
    return $events;
  }

  /**
   * Responds to the event "client.welcome.mail".
   *
   * @param Drupal\client\events\ClientWelcomeEvent $event
   *   The event object.
   */
  public function sendWelcomeMail(ClientWelcomeEvent $event) {
    $client = $event->getClientInfo();
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'client';
    $key = 'send_welcome_mail';
    $to = $client->email;
    $langcode = $this->account->getPreferredLangcode();
    $send = TRUE;
    $params['client'] = $client;
    $result = $this->mailManager->mail('client',
      'send_welcome_mail', $to, $langcode, $params, NULL, $send);
    $this->setLogMessage('Client ' . $client->id
        . ' added sucessfully and welcome mail has been sent !!');
  }

  /**
   * To set a log message.
   *
   * @param string $message
   *   The message to log.
   */
  private function setLogMessage($message) {
    $this->logger->get('default')
      ->info($message);
  }

}
