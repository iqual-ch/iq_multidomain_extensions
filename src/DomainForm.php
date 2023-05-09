<?php

namespace Drupal\iq_multidomain_extensions;

use Drupal\domain\Form\DomainForm as OrigForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base form for domain edit forms.
 */
class DomainForm extends OrigForm {

  /**
   *
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $user = \Drupal::currentUser();
    $baseConfig = $this->config('iq_multidomain_extensions.rancher_settings');
    $moduleHandler = \Drupal::service('module_handler');
    $domainThemeSwitch = $moduleHandler->moduleExists('domain_theme_switch');
    $stylingProfileThemeSwitch = $moduleHandler->moduleExists('styling_profiles_domain_switch');
    $form['extensions'] = [
      '#type' => 'details',
      '#title' => $this->t('iqual extensions'),
      '#description' => $this->t('These settings enhance the domain creation. Default values can be set under /de/admin/config/system/iqual_domain/configuration'),
      '#open' => TRUE,
    ];
    $form['extensions']['url_prefix'] = [
      '#type' => 'textfield',
      '#required' => (!$form_state->getFormObject()->getEntity()->isDefault()) ? TRUE : FALSE,
      '#title' => $this->t('URL prefix'),
      '#description' => $this->t('The url prefix for aliases for this domain.'),
      '#default_value' => $this->entity->getThirdPartySetting('iq_multidomain_extensions', 'url_prefix'),
    ];
    $form['validate_url']['#default_value'] = FALSE;

    if ($this->entity->isNew()) {
      $form['extensions']['create_menu'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create menu.'),
        '#description' => $this->t('Whether to automatically create a menu for the new domain.'),
        '#default_value' => $baseConfig->get('create_menu'),
      ];
      $contentTypes = \Drupal::service('entity_type.manager')->getStorage('node_type')->loadMultiple();
      $contentTypesList = [];
      foreach ($contentTypes as $contentType) {
        $contentTypesList[$contentType->id()] = $contentType->label();
      }
      $form['extensions']['menu_content_types'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Content types for new menu.'),
        '#description' => $this->t('Content types for new menu.'),
        '#options' => $contentTypesList,
        '#default_value' => $baseConfig->get('menu_content_types'),
      ];

      if ($domainThemeSwitch) {
        $form['extensions']['copy_theme'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Copy the base theme'),
          '#description' => $this->t('Whether to automatically copy the base theme on creating a new domain entry.'),
          '#default_value' => $baseConfig->get('copy_theme'),
          '#disabled' => !$domainThemeSwitch,
        ];
      }
      $form['extensions']['create_styling_profile'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create styling profile'),
        '#description' => $this->t('Whether to automatically create a styling profile with each new domain.')   . ((!$stylingProfileThemeSwitch) ? '<br />' . $this->t('Only available if styling_profiles_domain_switch is installed.') : ''),
        '#default_value' => $baseConfig->get('create_styling_profile'),
        '#disabled' => !$stylingProfileThemeSwitch,
      ];

      $form['#isnew'] = TRUE;
      $form['hostname']['#field_suffix'] = '.' . getenv('DOMAIN_BASE');
      $form['hostname']['#default_value'] = str_replace('.' . getenv('DOMAIN_BASE'), '', $form['hostname']['#default_value']);
      $form['hostname']['#default_value'] = '';
      $form['name']['#default_value'] = '';
    }
    else {
      $form['#isnew'] = FALSE;
    }
    if (!$user->hasPermission('administer iq_multidomain_extensions domains')) {

      $form['validate_url']['#type'] = 'hidden';
      $form['status']['#type'] = 'hidden';
      $form['scheme']['#type'] = 'hidden';
      $form['is_default']['#type'] = 'hidden';
      if (!$this->entity->isNew()) {
        $form['hostname']['#disabled'] = TRUE;
      }
    }
    return $form;
  }

  /**
   *
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    if (!$user->hasPermission('administer iq_multidomain_extensions domains')) {
      if ($this->entity->isNew()) {
        $form_state->setValue('hostname', $form_state->getValue('hostname') . '.' . getenv('DOMAIN_BASE'));
        $this->entity->setHostname($form_state->getValue('hostname'));
      }
    }
    parent::validateForm($form, $form_state);
  }

  /**
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::service('iq_multidomain_extensions.service.domain')->processDomainForm($form, $form_state);
    parent::submitForm($form, $form_state);
  }

  /**
   *
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->setThirdPartySetting('iq_multidomain_extensions', 'url_prefix', $form_state->getValue('url_prefix'));
    parent::save($form, $form_state);
    $user = \Drupal::currentUser();
  }

}
