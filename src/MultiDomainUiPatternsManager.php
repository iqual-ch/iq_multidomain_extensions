<?php

namespace Drupal\iq_multidomain_extensions;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;


/**
 * Provides the default ui_patterns manager.
 *
 * @method \Drupal\ui_patterns\Definition\PatternDefinition getDefinition($plugin_id, $exception_on_invalid = TRUE)
 */
class MultiDomainUiPatternsManager extends UiPatternsManager {



  // public function __construct(\Traversable $namespaces, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, CacheBackendInterface $cache_backend) {

  //   print_r( $namespaces );
  //   die();
  // }




  public function getPatterns() {
    \Drupal::logger('iq_multidomain_extensions')->notice('MultiDomainUiPatternsManager -> getPatterns()');


    return [];


  }


}
