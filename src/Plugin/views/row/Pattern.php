<?php

namespace Drupal\iq_multidomain_extensions\Plugin\views\row;

use Drupal\iq_multidomain_extensions\Form\PatternDisplayFormTrait;
use Drupal\ui_patterns_views\Plugin\views\row\Pattern as OriginalPattern;

/**
 * Overwrites default row plugin.
 */
class Pattern extends OriginalPattern {

  use PatternDisplayFormTrait;

}
