# iq_multidomain_extensions

Contains extensions, config etc. for a multidomain setup

Installation guide

1. Install iq_multidomain_extensions

2. add domain records
/admin/config/domain

IMPORTANT NOTES
- Make sure that the pattern ids and template names have to be identical (Pay attention to - and _)

### XML Sitemaps per domain
Install the iq_multidomain_sitemap_extension submodule:

    drush en iq_multidomain_sitemap_extension

Go to /admin/config/search/xmlsitemap to add sitemaps. On each sitemap, there's a new field available to set the domain.

### Favicon
The "iq_multidomain_favicon_extension" has being removed.

### Robots.txt
If you want to register a `robots.txt` file per domain, you must activate iq_multidomain_robotstxt_extension. Install it with

    drush en iq_multidomain_robotstxt_extension

Once installed, robots.txt can be configured at /admin/config/search/robotstxt.

Additionally incoming public requests have to be passed to the module (i.e. PHP) on either the `/robots` or `/robots.txt` path.

#### Kubernetes nginx ingress setup

You also need to add the following annotation to all main domain ingresses:

```yaml
nginx.ingress.kubernetes.io/configuration-snippet: |-
  location = /robots.txt {
    rewrite ^ /robots last;
  }
```

> It is also advised to enable the www-redirect option and to not set the `www` or non-`www` domain in the ingress respectively. (i.e. `nginx.ingress.kubernetes.io/from-to-www-redirect: "true"`)

This rewrites incoming requests to `robots.txt` to the correct dynamic endpoint (`/robots`) bypassing any existing robots file.

#### Nginx example

If you want to pass the request to php directly in your application nginx, you can use an approach like this:

```nginx
location = /robots.txt {
    rewrite ^ /index.php last;
}
```
