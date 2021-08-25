# iq_multidomain_extensions

Contains extensions, config etc. for a multidomain setup

Installation guide

1. get domain modules
	- composer require drupal/domain
	- composer require drupal/domain_theme_switch
	- composer require drupal/domain_site_settings

	apply patch for domain_site_settings:
	https://www.drupal.org/files/issues/2018-10-09/2930391-21.patch


2. Install iq_multidomain_extensions

3. Uninstall Ui patterns for pagedesigner.

4. add domain records
/admin/config/domain

5. choose themes for domains
/admin/config/domain/domain_theme_switch/config

IMPORTANT NOTES
- Make sure that the pattern ids and template names have to be identical (Pay attention to - and _)

### XML Sitemaps per domain
Install the iq_multidomain_sitemap_extension submodule:

    drush en iq_multidomain_sitemap_extension

Go to /admin/config/search/xmlsitemap to add sitemaps. On each sitemap, there's a new field available to set the domain.

### Favicon
If you're not working with a mutli-theme setup (one them per domain), the iq_multidomain_favicon_extension submodule is needed. Install it with

    drush en iq_multidomain_favicon_extension

Once installed, a new field is available on the domain site settings for the favicon.
/admin/config/domain/domain_site_settings/{domain_id}/edit
