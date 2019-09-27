<?php

namespace Drupal\iq_multidomain_extensions;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\domain\DomainListBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of domain entities.
 *
 * @see \Drupal\domain\Entity\Domain
 */
class IqualDomainListBuilder extends DomainListBuilder {
    public function buildOperations(EntityInterface $entity)
    {
        $build = [
            '#type' => 'operations',
            '#links' => $this->getOperations($entity),
        ];
        return $build;
    }
}
