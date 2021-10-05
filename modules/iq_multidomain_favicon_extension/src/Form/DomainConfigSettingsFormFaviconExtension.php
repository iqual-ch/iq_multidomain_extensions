<?php

namespace Drupal\iq_multidomain_favicon_extension\Form;

use Drupal\domain\DomainInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\domain_site_settings\Form\DomainConfigSettingsForm;
use Drupal\file\Entity\File;

/**
 * Class DomainConfigSettingsForm.
 *
 * @package Drupal\domain_site_settings\Form
 */
class DomainConfigSettingsFormFaviconExtension extends DomainConfigSettingsForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, DomainInterface $domain = NULL) {

    $config = $this->config('domain_site_settings.domainconfigsettings');
    $domainId = $this->getRequest()->get('domain_id');

    $form = parent::buildForm($form, $form_state);

    $form['site_information']['domain_favicon'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://domain-favicons/',
      '#title' => $this->t('Favicon'),
      '#default_value' => ($config->get($domainId) != NULL) ? $config->get($domainId . '.domain_favicon') : '',
      '#description' => $this->t("Custom favicon.ico for domain. If this is not set, the default favicon is used."),
      '#upload_validators' => [
        'file_validate_extensions' => ['ico'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $favicon = $form_state->getValue('domain_favicon');
    $faviconFile = File::load($favicon[0]);

    // Save file to filesystem.
    if (!empty($faviconFile)) {
      $faviconFile->setPermanent();
      $faviconFile->save();
    }

    $domainId = $form_state->getValue('domain_id');
    $config = $this->config('domain_site_settings.domainconfigsettings');
    $config->set($domainId . '.domain_favicon', $favicon);
    $config->save();
  }

}
