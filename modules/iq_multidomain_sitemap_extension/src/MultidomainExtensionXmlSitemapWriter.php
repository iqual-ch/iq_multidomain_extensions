<?php

namespace Drupal\iq_multidomain_sitemap_extension;

use Drupal\xmlsitemap\XmlSitemapWriter;

/**
 * Custom XMLSitemapWriter that filters out empty elements.
 */
class MultidomainExtensionXmlSitemapWriter extends XmlSitemapWriter {

  /**
   * {@inheritdoc}
   */
  public function writeElement($name, $content = NULL) {
    // Only print element if it has content.
    if (count($content)) {
      parent::writeElement($name, $content);
    }
  }

}
