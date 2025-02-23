# NoTrailingSlashSeoUrl (Shopware 6 Plugin)

Allows SEO URLs for categories without a trailing slash. 

Without this extension, category URLs that do not end with a trailing slash would_
 * before 6.6.1.0 - lead to a 404 Not Found error page.
 * [since 6.6.1.0](https://github.com/shopware/shopware/pull/3040) - yield the same page as the URL with a trailing slash, which might be penalized by search engines as duplicate conent.

With this extension, those URLs will lead to the expected category page.
The extension respects Shopware's SEO forwarding behavior setting, either a 301 redirect or a rel=\"canonical\" HTTP header.

The problem is described in this issue: https://github.com/shopware/platform/issues/468

There is also a forum discussions about it (german): https://forum.shopware.com/t/seiten-nur-mit-am-ende-der-url-aufrufbar/65242

## How to use it

> [!IMPORTANT]
> Always test new plugin versions in a dev environment before using it in a production/live shop.

1. Download the ZIP archive from the latest [release](https://github.com/codebarista-de/notrailingslashseourl/releases).
2. Copy the ZIP archive into the `custom/plugins` directory of your Shopware 6 installation.
3. Unzip the ZIP archive.
4. There should now be a folder named `BaristaNoTrailingSlashSeoUrl` in the `custom/plugins` directory of your Shopware 6 installation.
5. Log in to the Admin UI of your shop and go to `Extensions > MyExtensions`.
6. Find the `SEO category URLs without trailing slash` extension (Deutsch: `SEO Kategorie URLs ohne abschließenden Schrägstrich`) in the list.
7. Install and activate it.

## How it works

The `Shopware\Core\Content\Seo\SeoResolver` class resolves SEO URLs to native Shopware URLs.
`SeoResolver` is called by `Shopware\Storefront\Framework\Routing\RequestTransformer` for each request.
`SeoResolver` searches in the seo_url for an entry that matches the actual path (`seo_path_info`).
That entry will contain the shopware native path (`path_info`).
`RequestTransformer` then replaces the actual path of the request URL with the value of `path_info`.
If `SeoResolver` does not find a matching entry, it just returns the actual path for `path_info`.

Luckily, `SeoResolver` is not called directly by `RequestTransformer`.
Instead, an instance of `AbstractSeoResolver`, which `SeoResolver` extends, is injected as a service into `RequestTransformer`.
That means we can use [Symfonys service decoration pattern](https://symfony.com/doc/current/service_container/service_decoration.html) to adjust the behavior of `SeoResolver` to our needs.
See [this chapter](https://developer.shopware.com/docs/guides/plugins/plugins/plugin-fundamentals/adjusting-service#decorating-the-service) in the Shopware 6 documentation for an explanation of how that is done.
