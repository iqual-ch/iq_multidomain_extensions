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
    $moduleHandler = \Drupal::service('module_handler');

    $base_theme = $config->get('base_theme');
    $directory_path = $config->get('directory_path');
    $rancher_endpoint = $config->get('rancher_endpoint');
    $copy_theme = $config->get('copy_theme');
    $domainThemeSwitch = $moduleHandler->moduleExists('domain_theme_switch');
    $stylingProfileThemeSwitch = $moduleHandler->moduleExists('styling_profiles_domain_switch');

    $form['rancher_endpoint'] = [
      '#type' => 'textfield',
      '#title' => 'Rancher endpoint',
      '#description' => $this->t('The rancher api endpoint.'),
      '#default_value' => isset($rancher_endpoint) ? $rancher_endpoint : '',
    ];

    $form['notification_email'] = [
      '#type' => 'email',
      '#title' => 'Notification email',
      '#description' => $this->t('The email address to notify about a new domain record.'),
      '#default_value' => !empty($config->get('notification_email')) ? $config->get('notification_email') : '',
    ];

    $form['menu'] = [
      '#type' => 'details',
      '#title' => $this->t('Menu'),
      '#description' => $this->t('This section allows to preset the menu settings.'),
      '#open' => true
    ];

    $form['menu']['create_menu'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create a menu per domain.'),
      '#description' => $this->t('Whether to automatically create a menu for a new domain entry.'),
      '#default_value' => !empty($config->get('create_menu')) ? $config->get('create_menu') : 0,
    ];
    $contentTypes = \Drupal::service('entity_type.manager')->getStorage('node_type')->loadMultiple();
    $contentTypesList = [];
    foreach ($contentTypes as $contentType) {
      $contentTypesList[$contentType->id()] = $contentType->label();
    }
    $form['menu']['menu_content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content types for new menu.'),
      '#description' => $this->t('Content types for new menu.'),
      '#options' => $contentTypesList,
      '#default_value' => $config->get('menu_content_types'),
    ];

    $form['menu']['create_menu'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create a menu per domain.'),
      '#description' => $this->t('Whether to automatically create a menu for a new domain entry.'),
      '#default_value' => !empty($config->get('create_menu')) ? $config->get('create_menu') : 0,
    ];

    $form['styling'] = [
      '#type' => 'details',
      '#title' => $this->t('Design'),
      '#description' => $this->t('This section allows to automate design and styling when creating a new domain.'),
    ];
    $form['styling']['create_styling_profile'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create styling profile'),
      '#description' => $this->t('Whether to automatically create a styling profile with each new domain.')   . ((!$stylingProfileThemeSwitch) ? '<br />' . $this->t('Only available if styling_profiles_domain_switch is installed.') : ''),
      '#default_value' => $config->get('create_styling_profile'),
      '#disabled' => !$stylingProfileThemeSwitch,
    ];

    // $form['styling'] = [
    //   '#type' => 'details',
    //   '#title' => $this->t('Theme settings'),
    //   '#description' => $this->t('These settings allow to automatically copy a base theme with each new domain. Only available if domain_theme_switch is installed.'),

    // ];
    $form['styling']['copy_theme'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Copy the base theme'),
      '#description' => $this->t('Whether to automatically copy the base theme on creating a new domain entry.')  . ((!$domainThemeSwitch) ? '<br />' . $this->t('Only available if domain_theme_switch is installed.') : ''),
      '#default_value' => isset($copy_theme) ? $copy_theme : 0,
      '#disabled' => !$domainThemeSwitch,
    ];

    $form['styling']['directory_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Theme directory'),
      '#description' => $this->t('Path to the base theme, e.g. "themes/contrib".') . ((!$domainThemeSwitch) ? '<br />' . $this->t('Only available if domain_theme_switch is installed.') : ''),
      '#default_value' => isset($directory_path) ? $directory_path : '',
      '#disabled' => !$domainThemeSwitch,
    ];

    $form['styling']['base_theme'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base theme'),
      '#description' => $this->t('Machine name of the base theme to be copied.')  . ((!$domainThemeSwitch) ? '<br />' . $this->t('Only available if domain_theme_switch is installed.') : ''),
      '#default_value' => isset($base_theme) ? $base_theme : '',
      '#disabled' => !$domainThemeSwitch,
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
      ->set('copy_theme', $form_state->getValue('copy_theme'))
      ->set('create_menu', $form_state->getValue('create_menu'))
      ->set('notification_email', $form_state->getValue('notification_email'))
      ->set('menu_content_types', $form_state->getValue('menu_content_types'))
      ->set('create_styling_profile', $form_state->getValue('create_styling_profile'))
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
