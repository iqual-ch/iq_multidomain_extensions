<?php

$node_types = ['tg_category', 'iqbm_page', 'event', 'action', 'news', 'banner', 'market', 'tg_customer_center'];
$domain_source = 'transgourmet_ch';

drush_print("Changing Domain source to ".$domain_source);

$nids = Drupal::entityQuery('node')->condition('type', $node_types, 'IN')->execute();
$nodes =  Drupal\node\Entity\Node::loadMultiple($nids);

foreach( $nodes as $node ){
  drush_print("Updating node ".$node->id() );
  $node->field_domain_source = $domain_source;
  $node->field_domain_access = [$domain_source];
  $node->save();
}
