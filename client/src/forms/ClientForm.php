<?php

namespace Drupal\client\forms;

use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Url;
use Drupal\client\ClientStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\client\events\ClientWelcomeEvent;
use Drupal\file\Entity\File;

/**
 * Client Form.
 */
class ClientForm implements FormInterface {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'client_add';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form,
  FormStateInterface $form_state,
    $client = NULL) {
    if ($client) {
      if ($client == 'invalid') {
        drupal_set_message(t('Invalid client record'), 'error');
        return new RedirectResponse(Drupal::url('client.list'));
      }
      $form['eid'] = [
        '#type' => 'hidden',
        '#value' => $client->id,
      ];
    }
    $form['#attributes']['novalidate'] = '';
    $form['general'] = [
      '#type' => 'details',
      "#title" => "General Details",
      '#open' => TRUE,
    ];

    $form['general']['name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#required' => TRUE,
      '#default_value' => ($client) ? $client->name : '',
    ];

    $form['general']['email'] = [
      '#type' => 'email',
      '#title' => t('Email'),
      '#required' => TRUE,
      '#default_value' => ($client) ? $client->email : '',
    ];

    $form['general']['department'] = [
      '#type' => 'select',
      '#title' => t('Department'),
      '#options' => [
        '' => 'Select Department',
        'Development' => 'Development',
        'HR' => 'HR',
        'Sales' => 'Sales',
        'Marketing' => 'Marketing',
      ],
      '#required' => TRUE,
      '#default_value' => ($client) ? $client->department : '',
    ];

    $form['general']['status'] = [
      '#type' => 'checkbox',
      '#title' => t('Active?'),
      '#default_value' => ($client) ? $client->status : 1,
    ];

    $form['address_details'] = [
      '#type' => 'details',
      "#title" => "Address Details",
      '#open' => TRUE,
    ];

    $form['address_details']['address'] = [
      '#type' => 'textarea',
      '#title' => t('Address'),
      '#required' => TRUE,
      '#default_value' => ($client) ? $client->address : '',
    ];

    $form['address_details']['country'] = [
      '#type' => 'select',
      '#title' => t('Country'),
      '#options' => $this->getCountries(),
      '#required' => TRUE,
      '#default_value' => ($client) ? $client->country : '',
      '#ajax' => [
        'callback' => [$this, 'loadStates'],
        'event' => 'change',
        'wrapper' => 'states',
      ],
    ];
    $changed_country = $form_state->getValue('country');
    if ($client) {
      if (!empty($changed_country)) {
        $selected_country = $changed_country;
      }
      else {
        $selected_country = $client->country;
      }
    }
    else {
      $selected_country = $changed_country;
    }

    $states = $this->getStates($selected_country);
    $form['address_details']['state'] = [
      '#type' => 'select',
      '#prefix' => '<div id="states">',
      '#title' => t('State'),
      '#options' => $states,
      '#required' => TRUE,
      '#suffix' => '</div>',
      '#default_value' => ($client) ? $client->state : '',
      '#validated' => TRUE,
    ];

    $form['upload'] = [
      '#type' => 'details',
      "#title" => "Profile Pic",
      '#open' => TRUE,
    ];

    $form['upload']['profile_pic'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://client_images/',
      '#multiple' => FALSE,
      '#upload_validators' => [
        'file_validate_extensions' => ['png gif jpg jpeg jfif'],
        'file_validate_size' => [25600000],
        // 'file_validate_image_resolution' => array('800x600', '400x300'),.
      ],
      '#title' => t('Upload a Profile Picture'),
      '#default_value' => ($client) ? [$client->profile_pic] : '',
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Save',
    ];

    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => 'Cancel',
      '#attributes' => ['class' => ['button', 'button--primary']],
      '#url' => Url::fromRoute('client.list'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function loadStates(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
    return $form['address_details']['state'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCountries() {
    return [
      '' => 'Select Country',
      'India' => 'India',
      'Usa' => "Usa",
      'Russia' => "Russia",
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getStates($selected_country) {
    $states = [
      'India' => [
        '' => 'Select State',
        'Odisha' => 'Odisha',
        'Telangana' => 'Telangana',
        'Gujarat' => 'Gujarat',
        'Rajasthan' => 'Rajasthan',
      ],
      'Usa' => [
        '' => 'Select State',
        'Texas' => 'Texas',
        'Californea' => 'Californea',
      ],
      'Russia' => [
        '' => 'Select State',
        'Moscow' => 'Moscow',
        'Saints Petesberg' => 'Saints Petesberg',
      ],
    ];
    return ($selected_country) ? $states[$selected_country] : ['' => 'Select State'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    if (!empty($email) && (filter_var($email,
        FILTER_VALIDATE_EMAIL) === FALSE)) {
      $form_state->setErrorByName('email', t('Invalid email'));
    }
    $id = $form_state->getValue('eid');
    if (!empty($id)) {
      if (!ClientStorage::checkUniqueEmail($email, $id)) {
        $form_state->setErrorByName('email', t('This email has already been taken!'));
      }
    }
    else {
      if (!ClientStorage::checkUniqueEmail($email)) {
        $form_state->setErrorByName('email', t('The email has already been taken!'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $id = $form_state->getValue('eid');
    $file_usage = Drupal::service('file.usage');
    $profile_pic_fid = NULL;
    $image = $form_state->getValue('profile_pic');
    if (!empty($image)) {
      $profile_pic_fid = $image[0];
    }
    $fields = [
      'name' => SafeMarkup::checkPlain($form_state->getValue('name')),
      'email' => SafeMarkup::checkPlain($form_state->getValue('email')),
      'department' => $form_state->getValue('department'),
      'country' => $form_state->getValue('country'),
      'state' => $form_state->getValue('state'),
      'address' => SafeMarkup::checkPlain($form_state->getValue('address')),
      'status' => $form_state->getValue('status'),
      'profile_pic' => $profile_pic_fid,
    ];
    if (!empty($id) && ClientStorage::exists($id)) {
      $client = ClientStorage::load($id);
      if ($profile_pic_fid) {
        if ($profile_pic_fid !== $client->profile_pic) {
          file_delete($client->profile_pic);
          $file = File::load($profile_pic_fid);
          $file->setPermanent();
          $file->save();
          $file_usage->add($file, 'client', 'file', $id);
        }
      }
      else {
        file_delete($client->profile_pic);
      }
      ClientStorage::update($id, $fields);
      $message = 'Client updated sucessfully';
    }
    else {
      $new_client_id = ClientStorage::add($fields);
      if ($profile_pic_fid) {
        $file = File::load($profile_pic_fid);
        $file->setPermanent();
        $file->save();
        $file_usage->add($file, 'client', 'file', $new_client_id);
      }
      // $this->dispatchClientWelcomeMailEvent($new_client_id);
      $message = 'Client created sucessfully';
    }
    drupal_set_message($message);
    $form_state->setRedirect('client.list');
  }

  /**
   * {@inheritdoc}
   */
  private function dispatchClientWelcomeMailEvent($client_id) {
    $dispatcher = \Drupal::service('event_dispatcher');
    $event = new ClientWelcomeEvent($client_id);
    $dispatcher->dispatch('client.welcome.mail', $event);
  }

}
