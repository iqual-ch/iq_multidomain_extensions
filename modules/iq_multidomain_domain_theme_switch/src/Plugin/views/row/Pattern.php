<?php

namespace Drupal\iq_multidomain_domain_theme_switch\Plugin\views\row;

use Drupal\iq_multidomain_domain_theme_switch\Form\PatternDisplayFormTrait;
use Drupal\ui_patterns_views\Plugin\views\row\Pattern as OriginalPattern;

/**
 * Overwrites default row plugin.
 */
class Pattern extends OriginalPattern {

  use PatternDisplayFormTrait;

}
