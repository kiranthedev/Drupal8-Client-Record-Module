<?php

namespace Drupal\client\forms;

use Drupal;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\client\ClientStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Client bulck action form.
 */
class ClientBulkActionForm extends ConfirmFormBase {

  /**
   * The action name.
   *
   * @var string
   */

  private $action;

  /**
   * The request.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */

  protected static $session;

  /**
   * The records on which the action to be performed.
   *
   * @var mixed
   */

  private $records;

  /**
   * Constructs the ClientController.
   *
   * @param \Symfony\Component\HttpFoundation\Session\Session $session
   *   The session service.
   */
  public function __construct(Session $session) {
    $this->session = $session;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('session')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'client_bulk_action';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to %action selected clients?',
      ['%action' => $this->action]);
  }

  /**
   * {@inheritdoc}
   */
  public static function getPageTitle() {
    return t('Are you sure you want to %action selected clients?',
      ['%action' => $this->action]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Confirm');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return new Url('client.list');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('client.list');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $action = NULL) {
    $this->action = $action;
    $session_client = $this->session->get('client');
    if ($this->records = $session_client['selected_items']) {
      $form['clients'] = [
        '#theme' => 'item_list',
        '#items' => $this->records,
      ];
    }
    else {
      drupal_set_message(t('No client record to process.'), 'error');
      return new RedirectResponse(Drupal::url('client.list'));
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $request = \Drupal::request();

    $batch = [
      'title' => t('Applying action @action to selected clients', ['@action' => $this->action]),
      'operations' => [
        [
          'Drupal\client\forms\ClientBulkActionForm::performBatchAction',
          [$this->records, $this->action],
        ],
      ],
      'finished' => 'Drupal\client\forms\ClientBulkActionForm::onFinishBatchCallback',
    ];
    batch_set($batch);
    $this->session->remove('client');
    $form_state->setRedirect('client.list');
  }

  /**
   * Batch operation callback.
   */
  public static function performBatchAction($records, $action, &$context) {
    switch ($action) {
      case 'delete':
        $message = "Deleting the clients";
        break;

      case 'activate':
        $message = "Activating the clients";
        break;

      case 'block':
        $message = "Blocking the clients";
        break;

      default:
        $message = "Deleting the clients";
    }

    foreach ($records as $id => $name) {
      switch ($action) {
        case 'delete':
          $result = ClientStorage::delete($id);
          break;

        case 'activate':
          $result = ClientStorage::changeStatus($id, 1);
          break;

        case 'block':
          $result = ClientStorage::changeStatus($id, 0);
          break;

        default:
          $result = ClientStorage::delete($id);
      }
      $results[] = $result;
    }
    $context['message'] = $message;
    $context['results'] = $results;
  }

  /**
   * Finish callback for batch process.
   */
  public static function onFinishBatchCallback($success, $results, $operations) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One client record processed.', '@count client records processed.'
      );
      drupal_set_message($message);
    }
    else {
      $message = t('Finished with an error.');
      drupal_set_message($message, 'error');
    }
  }

}
