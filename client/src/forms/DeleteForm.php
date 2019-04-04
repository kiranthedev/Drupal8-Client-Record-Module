<?php

namespace Drupal\client\forms;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\client\ClientStorage;

/**
 * Confirm client delete form.
 */
class DeleteForm extends ConfirmFormBase {

  protected $id;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'client_delete';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete client %id?', ['%id' => $this->id]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
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
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    if (!ClientStorage::exists($id)) {
      drupal_set_message(t('Invalid client record'), 'error');
      return new RedirectResponse(Drupal::url('client.list'));
    }
    $this->id = $id;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    ClientStorage::delete($this->id);
    drupal_set_message(t('Client %id has been deleted.', ['%id' => $this->id]));
    $form_state->setRedirect('client.list');
  }

}
