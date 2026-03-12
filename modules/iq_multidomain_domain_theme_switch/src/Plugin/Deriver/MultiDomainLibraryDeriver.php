<?php

namespace Drupal\iq_multidomain_domain_theme_switch\Plugin\Deriver;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\domain\DomainStorageInterface;
use Drupal\domain_config\Config\DomainConfigCollectionUtils;
use Drupal\domain_config_ui\DomainConfigUIManagerInterface;
use Drupal\ui_patterns_library\Plugin\Deriver\LibraryDeriver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The deriver for ui_patterns.
 *
 * @todo Move pattern deriving to separate module.
 */
class MultiDomainLibraryDeriver extends LibraryDeriver {

  /**
   * The config storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected StorageInterface $configStorage;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The domain storage.
   *
   * @var \Drupal\domain\DomainStorageInterface
   */
  protected DomainStorageInterface $domainStorage;

  /**
   * The domain config UI manager.
   *
   * @var \Drupal\domain_config_ui\DomainConfigUIManagerInterface
   */
  protected DomainConfigUIManagerInterface $domainConfigUIManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    $instance = parent::create($container, $base_plugin_id);
    $instance->configStorage = $container->get('config.storage');
    $instance->configFactory = $container->get('config.factory');
    $instance->domainStorage = $container->get('entity_type.manager')->getStorage('domain');
    $instance->domainConfigUIManager = $container->get('domain_config_ui.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getPatterns() {
    $patterns = parent::getPatterns();
    $prefix_themes = [];

    // Get the default site theme - this one should NOT be prefixed.
    $default_theme = $this->configFactory->get('system.theme')->get('default');

    // Collect all secondary themes to prefix the pattern ids.
    // Only themes that differ from the default site theme should be prefixed.
    /** @var \Drupal\domain\Entity\Domain $domain */
    foreach ($this->domainStorage->loadMultipleSorted() as $domain) {
      $theme_name = NULL;

      // First try Domain 3.x format (collection-based config).
      if ($this->domainConfigUIManager->isConfigurationRegisteredForDomain($domain->id(), 'system.theme')) {
        $domain_collection = $this->configStorage->createCollection(
          DomainConfigCollectionUtils::createDomainConfigCollectionName($domain->id())
        );
        $theme_config = $domain_collection->read('system.theme');
        if (!empty($theme_config['default'])) {
          $theme_name = $theme_config['default'];
        }
      }

      // Fall back to legacy Domain 2.x format.
      if (!$theme_name) {
        $legacy_config = $this->configFactory->get('domain.config.' . $domain->id() . '.system.theme');
        if (!empty($legacy_config->get('default'))) {
          $theme_name = $legacy_config->get('default');
        }
      }

      // Only add to prefix_themes if it's different from the default theme.
      if ($theme_name && $theme_name !== $default_theme && !in_array($theme_name, $prefix_themes)) {
        $prefix_themes[] = $theme_name;
      }
    }

    // Loop over all patterns and
    // prefix patterns provided by secondary domain themes.
    /** @var \Drupal\ui_patterns\Definition\PatternDefinition $definition */
    foreach ($patterns as $delta => $definition) {
      $provider = $definition->getProvider();
      if (in_array($provider, $prefix_themes)) {
        $arrayDefinition = $definition->toArray();
        $arrayDefinition['id'] = $provider . '_' . $arrayDefinition['id'];
        $newDefinition = $this->getPatternDefinition($arrayDefinition);
        $patterns[$delta] = $newDefinition;
      }
    }
    return $patterns;
  }

}
