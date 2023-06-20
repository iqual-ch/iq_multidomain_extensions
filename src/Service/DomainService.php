<?php

namespace Drupal\iq_multidomain_extensions\Service;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Entity\Menu;
use Drupal\styling_profiles\Entity\StylingProfile;

/**
 *
 */
class DomainService {

  /**
   * The drupal messenger.
   *
   * @var Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;
  /**
   * The drupal messenger.
   *
   * @var Drupal\Core\Messenger\MessengerInterface
   */
  protected $client;
  /**
   * The drupal messenger.
   *
   * @var Drupal\Core\Messenger\MessengerInterface
   */
  protected $domainBase;


  protected $domainNamespace = '';
  protected $domainService = '';
  protected $projectId = '';
  protected $notificationEmail = '';

  /**
   * Create a new instance.
   *
   * @param Drupal\Core\Messenger\MessengerInterface $messenger
   *   The drupal messenger.
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   The configuration.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;

    $this->configuration = \Drupal::config('iq_multidomain_extensions.settings');

    $this->themeDirectory = $this->configuration->get('directory_path');
    $this->baseTheme = $this->configuration->get('base_theme');
    $this->domainBase = getenv('DOMAIN_BASE');
  }

  /**
   *
   */
  public function addMenu(string $label, string $id, $contentTypes = []) {
    // Add a menu.
    $menu = Menu::load($id);
    if ($menu == NULL) {
      $menu = Menu::create([
        'id' => $id,
        'label' => $label . ' - Main navigation',
        'description' => 'Main navigation menu for ' . $label,
      ]);
      $menu->save();
    }
    foreach ($contentTypes as $contentTypeId => $label) {
      $contentType = \Drupal::service('entity_type.manager')->getStorage('node_type')->load($contentTypeId);
      $menus = $contentType->getThirdPartySetting('menu_ui', 'available_menus');
      if (!in_array($id, $menus)) {
        $menus[] = $id;
        $contentType->setThirdPartySetting('menu_ui', 'available_menus', $menus);
        $contentType->save();
      }
    }
    if (\Drupal::moduleHandler()->moduleExists('pagetree')) {
      $pagetree_settings = \Drupal::configFactory()->getEditable('pagetree.settings');
      $pagetree_settings->set('menus', array_merge($pagetree_settings->get('menus'), [$menu->id() => $menu->id()]));
      $pagetree_settings->save();
    }
  }

  /**
   *
   */
  public function createStylingProfile(string $label, string $id) {
    $profile = StylingProfile::create(['id' => $id, 'label' => $label]);
    $profile->save();
    $config = \Drupal::service('config.factory')->getEditable('styling_profiles_domain_switch.settings');
    $config->set($id, $profile->id());
    $config->save();
  }

  /**
   * Undocumented function.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form stage.
   */
  public function processDomainForm($form, FormStateInterface $form_state) {
    if ($form['#isnew']) {
      $label = $form_state->getValue('name');
      $id = $form_state->getValue('id');
      if ($form_state->getValue('create_menu')) {
        $this->addMenu($label, 'multidomain-' . str_replace('_', '-', $id), $form_state->getValue('menu_content_types'));
      }

      $moduleHandler = \Drupal::service('module_handler');
      $stylingProfileThemeSwitch = $moduleHandler->moduleExists('styling_profiles_domain_switch');
      if ($form_state->getValue('create_styling_profile') && $stylingProfileThemeSwitch) {
        $this->createStylingProfile($label, $id . '_site');
      }
    }
  }

}
