<?php
namespace Drupal\iq_multidomain_extensions;

use Drupal\domain\DomainForm as OrigForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base form for domain edit forms.
 */
class DomainForm extends OrigForm
{

  public function form(array $form, FormStateInterface $form_state)
  {
    $form = parent::form($form, $form_state);
    $form['url_prefix'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('URL Prefix'),
      '#size' => 40,
      '#maxlength' => 80,
      '#default_value' => $this->entity->getThirdPartySetting('iq_multidomain_extensions', 'url_prefix', ''),
      '#description' => $this->t('URL Prefix for specific domain. Leave empty for main domain'),
    );

    return $form;
  }
  public function save(array $form, FormStateInterface $form_state)
  {
    $this->entity->setThirdPartySetting('iq_multidomain_extensions', 'url_prefix', $form_state->getValue('url_prefix'));
    parent::save($form, $form_state);
  }
}
