<?php

namespace Drupal\iq_multidomain_extensions;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\domain\Form\DomainForm as OrigForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\domain\DomainStorageInterface;
use Drupal\domain\DomainValidatorInterface;

/**
 * Base form for domain edit forms.
 */
class DomainForm extends OrigForm {

  /**
   * The iq_multidomain_extensions domain service.
   *
   * @var \Drupal\iq_multidomain_extensions\Service\DomainService
   */
  protected $domainService = NULL;

  /**
   * Constructs a DomainForm object.
   *
   * @param \Drupal\domain\DomainStorageInterface $domain_storage
   *   The domain storage manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\domain\DomainValidatorInterface $validator
   *   The domain validator.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(DomainStorageInterface $domain_storage, RendererInterface $renderer, DomainValidatorInterface $validator, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($domain_storage, $renderer, $validator, $entity_type_manager);
    $this->domainService = \Drupal::service('iq_multidomain_extensions.service.domain');

  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Get settings.
    $baseConfig = $this->config('iq_multidomain_extensions.settings');

    // Check for partner modules.
    $domainThemeSwitch = $this->moduleHandler->moduleExists('domain_theme_switch');
    $stylingProfileThemeSwitch = $this->moduleHandler->moduleExists('styling_profiles_domain_switch');

    /** @var \Drupal\domain\Entity\Domain $domain */
    $domain = $this->entity;

    $form['extensions'] = [
      '#type' => 'details',
      '#title' => $this->t('iqual extensions'),
      '#description' => $this->t('These settings enhance the domain creation. Default values can be set under /de/admin/config/system/iqual_domain/configuration'),
      '#open' => TRUE,
    ];
    $form['extensions']['url_prefix'] = [
      '#type' => 'textfield',
      '#required' => (!$domain->isDefault()) ? TRUE : FALSE,
      '#title' => $this->t('URL prefix'),
      '#description' => $this->t('The url prefix for aliases for this domain.'),
      '#default_value' => $domain->getThirdPartySetting('iq_multidomain_extensions', 'url_prefix'),
    ];
    $form['validate_url']['#default_value'] = FALSE;

    if ($domain->isNew()) {
      $form['extensions']['create_menu'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create menu.'),
        '#description' => $this->t('Whether to automatically create a menu for the new domain.'),
        '#default_value' => $baseConfig->get('create_menu'),
      ];
      $contentTypes = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
      $contentTypesList = [];
      foreach ($contentTypes as $contentType) {
        $contentTypesList[$contentType->id()] = $contentType->label();
      }

      $form['extensions']['menu_content_types'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Content types for new menu.'),
        '#description' => $this->t('Content types for new menu.'),
        '#options' => $contentTypesList,
        '#default_value' => $baseConfig->get('menu_content_types') ?: [],
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
        '#description' => $this->t('Whether to automatically create a styling profile with each new domain.') . ((!$stylingProfileThemeSwitch) ? '<br />' . $this->t('Only available if styling_profiles_domain_switch is installed.') : ''),
        '#default_value' => $baseConfig->get('create_styling_profile'),
        '#disabled' => !$stylingProfileThemeSwitch,
      ];

      $form['hostname']['#field_suffix'] = '.' . getenv('DOMAIN_BASE');
      $form['hostname']['#default_value'] = str_replace('.' . getenv('DOMAIN_BASE'), '', $form['hostname']['#default_value']);
      $form['hostname']['#default_value'] = '';
      $form['name']['#default_value'] = '';
    }

    if (!$this->currentUser()->hasPermission('administer iq_multidomain_extensions domains')) {

      $form['validate_url']['#type'] = 'hidden';
      $form['status']['#type'] = 'hidden';
      $form['scheme']['#type'] = 'hidden';
      $form['is_default']['#type'] = 'hidden';
      if (!$domain->isNew()) {
        $form['hostname']['#disabled'] = TRUE;
      }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\domain\Entity\Domain $domain */
    $domain = $this->entity;

    if (!$this->currentUser()->hasPermission('administer iq_multidomain_extensions domains')) {
      if ($domain->isNew()) {
        $form_state->setValue('hostname', $form_state->getValue('hostname') . '.' . getenv('DOMAIN_BASE'));
        $domain->setHostname($form_state->getValue('hostname'));
      }
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\domain\Entity\Domain $domain */
    $domain = $this->entity;
    if ($domain->isNew()) {
      $label = $form_state->getValue('name');
      $id = $form_state->getValue('id');
      if ($form_state->getValue('create_menu')) {
        $this->domainService->addMenu($label, 'multidomain-' . str_replace('_', '-', $id), $form_state->getValue('menu_content_types'));
      }

      if ($form_state->getValue('create_styling_profile')) {
        $this->domainService->createStylingProfile($label, $id . '_site');
      }
    }
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\domain\Entity\Domain $domain */
    $domain = $this->entity;
    $domain->setThirdPartySetting('iq_multidomain_extensions', 'url_prefix', $form_state->getValue('url_prefix'));
    parent::save($form, $form_state);
  }

}
