<?php

namespace Drupal\iq_multidomain_extensions\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class RancherForm.
 *
 * @package Drupal\iq_multidomain_extensions\Form
 */
class RancherForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rancher_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('iq_multidomain_extensions.rancher_settings');

    $base_theme = $config->get('base_theme');
    $directory_path = $config->get('directory_path');
    $rancher_endpoint = $config->get('rancher_endpoint');

    $form['directory_path'] = [
      '#type' => 'textfield',
      '#title' => 'Directory path',
      '#description' => $this->t('Directory path to the base theme. Example: themes/contrib'),
      '#default_value' => isset($directory_path) ? $directory_path : '',
    ];

    $form['base_theme'] = [
      '#type' => 'textfield',
      '#title' => 'Base theme',
      '#description' => $this->t('Name of the base theme to be copied.'),
      '#default_value' => isset($base_theme) ? $base_theme : '',
    ];
    $form['rancher_endpoint'] = [
      '#type' => 'textfield',
      '#title' => 'Rancher endpoint',
      '#description' => $this->t('The rancher api endpoint.'),
      '#default_value' => isset($rancher_endpoint) ? $rancher_endpoint : '',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('iq_multidomain_extensions.rancher_settings')
      ->set('directory_path', $form_state->getValue('directory_path'))
      ->set('base_theme', $form_state->getValue('base_theme'))
      ->set('rancher_endpoint', $form_state->getValue('rancher_endpoint'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Get Editable config names.
   *
   * @inheritDoc
   */
  protected function getEditableConfigNames() {
    return ['iq_multidomain_extensions.rancher_settings'];
  }

}
