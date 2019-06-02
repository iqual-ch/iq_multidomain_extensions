<?php

namespace Drupal\iq_multidomain_extensions;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\ui_patterns\UiPatternsManager;


/**
 * Provides the default ui_patterns manager.
 *
 * @method \Drupal\ui_patterns\Definition\PatternDefinition getDefinition($plugin_id, $exception_on_invalid = TRUE)
 */
class MultiDomainUiPatternsManager extends UiPatternsManager {

}
