<?php

namespace Drupal\iq_multidomain_sitemap_extension;

use Drupal\xmlsitemap\XmlSitemapGenerator;
use Drupal\xmlsitemap\XmlSitemapInterface;

/**
 * Custom XmlSitemap generator service class to use custom XMLSitemapWriter.
 */
class MultidomainExtensionXmlSitemapGenerator extends XmlSitemapGenerator {

  /**
   * {@inheritdoc}
   */
  public function generatePage(XmlSitemapInterface $sitemap, $page) {
    $writer = new MultidomainExtensionXmlSitemapWriter($sitemap, $page);
    $writer->startDocument();
    $this->generateChunk($sitemap, $writer, $page);
    $writer->endDocument();
    return $writer->getSitemapElementCount();
  }

}
