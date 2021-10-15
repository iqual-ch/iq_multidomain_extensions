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
    $domainId = $domain->id();
    $form = parent::buildForm($form, $form_state, $domain);

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
    $fileUsage = \Drupal::service('file.usage');
    $faviconFile = File::load($favicon[0]);

    // Set file status permanent.
    if (!$faviconFile->isPermanent()) {
      $faviconFile->setPermanent();
    }

    // Check file usage , if it's empty, add new entry.
    $usage = $fileUsage->listUsage($faviconFile);
    if (empty($usage)) {
      // Let's assume it's image.
      $fileUsage->add($faviconFile, 'iq_multidomain_favicon_extension', 'image', $favicon[0]);
    }
    $faviconFile->save();

    $domainId = $form_state->getValue('domain_id');
    $config = $this->config('domain_site_settings.domainconfigsettings');
    $config->set($domainId . '.domain_favicon', $favicon);
    $config->save();
  }

}
