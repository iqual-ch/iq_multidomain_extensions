<?php

namespace Drupal\iq_multidomain_extensions;

use Drupal\Core\Entity\EntityInterface;
use Drupal\domain\DomainListBuilder;

/**
 * Defines a class to build a listing of domain entities.
 *
 * @see \Drupal\domain\Entity\Domain
 */
class IqualDomainListBuilder extends DomainListBuilder {

  /**
   *
   */
  public function buildOperations(EntityInterface $entity) {
    $build = [
      '#type' => 'operations',
      '#links' => $this->getOperations($entity),
    ];
    return $build;
  }

}
