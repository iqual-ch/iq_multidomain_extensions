<?php

$node_types = ['tg_category', 'iqbm_page'];
$domain_source = 'transgourmet_ch';

drush_print("Changing Domain source to ".$domain_source);

$nids = \Drupal::entityQuery('node')->condition('type', $node_types, 'IN')->execute();
$nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);

foreach( $nodes as $node ){
  drush_print("Updating node ".$node->id() );
  $node->field_domain_source = 'transgourmet_ch';
  $node->field_domain_access = ['transgourmet_ch'];
  $node->save();
}
