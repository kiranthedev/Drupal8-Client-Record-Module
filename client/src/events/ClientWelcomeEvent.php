<?php

namespace Drupal\client\events;

use Symfony\Component\EventDispatcher\Event;
use Drupal\client\ClientStorage;

/**
 * Client welcome event.
 */
class ClientWelcomeEvent extends Event {

  /**
   * The Client Id.
   *
   * @var int
   */
  private $clientId;

  /**
   * Constructs the ClientWelcomeEvent.
   *
   * @param int $client_id
   *   The Client Id.
   */
  public function __construct($client_id) {
    $this->clientId = $client_id;
  }

  /**
   * Loads client details.
   *
   * @return mixed
   *   The client details.
   */
  public function getClientInfo() {
    return ClientStorage::load($this->clientId);
  }

}
