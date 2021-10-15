<?php

namespace Drupal\iq_multidomain_robotstxt_extension\Form;

use Drupal\domain\DomainInterface;
use Drupal\iq_multidomain_favicon_extension\Form\DomainConfigSettingsFormFaviconExtension;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DomainConfigSettingsForm.
 *
 * @package Drupal\domain_site_settings\Form
 */
class DomainConfigSettingsFormRobotstxtExtension extends DomainConfigSettingsFormFaviconExtension {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, DomainInterface $domain = NULL) {

    $config = $this->config('domain_site_settings.domainconfigsettings');
    $domainId = $this->getRequest()->get('domain_id');

    if (!$domain) {
      $domain = \Drupal::service('entity_type.manager')->getStorage('domain')->load($domainId);
    }

    $form = parent::buildForm($form, $form_state, $domain);

    $form['site_information']['domain_robotstxt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('robotx.txt'),
      '#default_value' => ($config->get($domainId) != NULL) ? $config->get($domainId . '.domain_robotstxt') : file_get_contents(\Drupal::root() . '/robots.txt'),
      '#description' => $this->t("Custom robots.txt for domain. If this is not set, the default robotx.txt is used."),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $robotstxt = $form_state->getValue('domain_robotstxt');
    $domainId = $form_state->getValue('domain_id');
    $config = $this->config('domain_site_settings.domainconfigsettings');
    $config->set($domainId . '.domain_robotstxt', $robotstxt);
    $config->save();
  }

}
