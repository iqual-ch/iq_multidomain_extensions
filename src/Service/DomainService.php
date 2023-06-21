<?php

namespace Drupal\iq_multidomain_extensions\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;

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
   * The drupal config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Create a new instance.
   *
   * @param Drupal\Core\Messenger\MessengerInterface $messenger
   *   The drupal messenger.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The drupal config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(
    MessengerInterface $messenger,
    ConfigFactory $configFactory,
    EntityTypeManagerInterface $entity_type_manager,
    ModuleHandlerInterface $module_handler
    ) {
    $this->messenger = $messenger;
    $this->configFactory = $configFactory;
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;

  }

  /**
   * Add a new menu.
   *
   * @param string $label
   *   Name of the menu.
   * @param string $id
   *   The id of the menu.
   * @param array $contentTypes
   *   The allowed content types.
   */
  public function addMenu(string $label, string $id, array $contentTypes = []) {
    $storageManager = $this->entityTypeManager->getStorage('menu');
    $menu = $storageManager->load($id);
    if ($menu == NULL) {
      $menu = $storageManager->create([
        'id' => $id,
        'label' => $label . ' - Main navigation',
        'description' => 'Main navigation menu for ' . $label,
      ]);
      $menu->save();
    }
    foreach ($contentTypes as $contentTypeId => $label) {
      /** @var \Drupal\node\NodeTypeInterface $content_type */
      $content_type = $this->entityTypeManager->getStorage('node_type')->load($contentTypeId);
      $menus = $content_type->getThirdPartySetting('menu_ui', 'available_menus');
      if (!in_array($id, $menus)) {
        $menus[] = $id;
        $content_type->setThirdPartySetting('menu_ui', 'available_menus', $menus);
        $content_type->save();
      }
    }
    if ($this->moduleHandler->moduleExists('pagetree')) {
      $pagetree_settings = $this->configFactory->getEditable('pagetree.settings');
      $pagetree_settings->set('menus', array_merge($pagetree_settings->get('menus'), [$menu->id() => $menu->id()]));
      $pagetree_settings->save();
    }
  }

  /**
   * Create a new styling profile.
   *
   * @param string $label
   *   The label of the styling profile.
   * @param string $id
   *   The id of the the of the styling profile.
   */
  public function createStylingProfile(string $label, string $id) {
    if ($this->moduleHandler->moduleExists('styling_profiles_domain_switch')) {
      $profile = $this->entityTypeManager->getStorage('styling_profile')->create(['id' => $id, 'label' => $label]);
      $profile->save();
      $config = $this->configFactory->getEditable('styling_profiles_domain_switch.settings');
      $config->set($id, $profile->id());
      $config->save();
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
