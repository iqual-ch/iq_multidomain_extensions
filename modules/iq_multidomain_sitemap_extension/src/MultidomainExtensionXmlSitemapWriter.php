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
  public function writeElement(string $name, string|array|null $content = NULL): bool {
    // Only print element if it has content.
    if ($content === NULL ? 0 : count($content)) {
      return parent::writeElement($name, $content);
    }
    return FALSE;
  }

}
