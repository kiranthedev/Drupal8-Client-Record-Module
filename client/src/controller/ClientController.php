<?php

namespace Drupal\client\controller;

use Drupal\client\forms\ClientTableForm;
use Drupal;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\file\Entity\File;

/**
 * Controller class.
 */
class ClientController extends ControllerBase {

  /**
   * The Form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */

  protected $formBuilder;

  /**
   * Databse Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */

  protected $db;

  /**
   * Request.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */

  protected $request;

  /**
   * Constructs the ClientController.
   *
   * @param \Drupal\Core\Form\FormBuilder $form_builder
   *   The Form builder.
   * @param \Drupal\Core\Database\Connection $con
   *   The database connection.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Request stack.
   */
  public function __construct(FormBuilder $form_builder,
    Connection $con,
    RequestStack $request) {
    $this->formBuilder = $form_builder;
    $this->db = $con;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('form_builder'),
        $container->get('database'),
        $container->get('request_stack')
      );
  }

  /**
   * Lists all the clients.
   */
  public function listClients() {
    $content = [];
    $content['search_form'] =
      $this->formBuilder->getForm('Drupal\client\forms\ClientSearchForm');
    $search_key = $this->request->getCurrentRequest()->get('search');
    $client_table_form_instance =
      new ClientTableForm($this->db, $search_key);
    $content['table'] =
      $this->formBuilder->getForm($client_table_form_instance);
    $content['pager'] = [
      '#type' => 'pager',
    ];
    $content['#attached'] = ['library' => ['core/drupal.dialog.ajax']];
    return $content;
  }

  /**
   * To view an client details.
   */
  public function viewClient($client, $js = 'nojs') {
    global $base_url;
    if ($client == 'invalid') {
      drupal_set_message(t('Invalid client record'), 'error');
      return new RedirectResponse(Drupal::url('client.list'));
    }
    $rows = [
        [
          ['data' => 'Id', 'header' => TRUE],
          $client->id,
        ],
        [
          ['data' => 'Name', 'header' => TRUE],
          $client->name,
        ],
        [
          ['data' => 'Email', 'header' => TRUE],
          $client->email,
        ],
        [
          ['data' => 'Department', 'header' => TRUE],
          $client->department,
        ],
        [
          ['data' => 'Country', 'header' => TRUE],
          $client->country,
        ],
        [
          ['data' => 'State', 'header' => TRUE],
          $client->state,
        ],
        [
          ['data' => 'Address', 'header' => TRUE],
          $client->address,
        ],
    ];
    $profile_pic = File::load($client->profile_pic);
    if ($profile_pic) {
      $profile_pic_url = file_create_url($profile_pic->getFileUri());
    }
    else {
      $module_handler = Drupal::service('module_handler');
      $path = $module_handler->getModule('client')->getPath();
      $profile_pic_url = $base_url . '/' . $path . '/assets/profile_placeholder.png';
    }
    $content['image'] = [
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => ['src' => $profile_pic_url, 'height' => 400],
    ];
    $content['details'] = [
      '#type' => 'table',
      '#rows' => $rows,
      '#attributes' => ['class' => ['client-detail']],
    ];
    $content['edit'] = [
      '#type' => 'link',
      '#title' => 'Edit',
      '#attributes' => ['class' => ['button button--primary']],
      '#url' => Url::fromRoute('client.edit', ['client' => $client->id]),
    ];
    $content['delete'] = [
      '#type' => 'link',
      '#title' => 'Delete',
      '#attributes' => ['class' => ['button']],
      '#url' => Url::fromRoute('client.delete', ['id' => $client->id]),
    ];
    if ($js == 'ajax') {
      $modal_title = t('Client #@id', ['@id' => $client->id]);
      $options = [
        'dialogClass' => 'popup-dialog-class',
        'width' => '70%',
        'height' => '80%',
      ];
      $response = new AjaxResponse();
      $response->addCommand(new OpenModalDialogCommand(
        $modal_title, $content, $options));
      return $response;
    }
    else {
      return $content;
    }
  }

  /**
   * Callback for opening the client quick edit form in modal.
   */
  public function openQuickEditModalForm($client = NULL) {
    if ($client == 'invalid') {
      drupal_set_message(t('Invalid client record'), 'error');
      return new RedirectResponse(Drupal::url('client.list'));
    }
    $response = new AjaxResponse();
    $modal_form = $this->formBuilder
      ->getForm('Drupal\client\forms\ClientQuickEditForm', $client);
    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(
      new OpenModalDialogCommand(t('Quick Edit Client #@id',
      ['@id' => $client->id]), $modal_form, ['width' => '800']
    ));
    return $response;
  }

  /**
   * Callback for opening the client mail form in modal.
   */
  public function openEmailModalForm($client = NULL) {
    if ($client == 'invalid') {
      drupal_set_message(t('Invalid client record'), 'error');
      return new RedirectResponse(Drupal::url('client.list'));
    }
    $response = new AjaxResponse();
    // Get the form using the form builder global.
    $modal_form = $this->formBuilder
      ->getForm('Drupal\client\forms\ClientMailForm', $client);
    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(
      new OpenModalDialogCommand(
        t('Send mail to: @email', ['@email' => $client->email]),
        $modal_form, ['width' => '800']
    ));
    return $response;
  }

}
