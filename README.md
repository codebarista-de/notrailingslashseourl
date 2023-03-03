# NoTrailingSlashSeoUrl (Shopware 6 Plugin)

Allows SEO URLs for categories without a trailing slash. 

Without this extension, URLs without a trailing slash would lead to a 404 Not Found error page.
With this extension, those same URLs will lead to the expected category page.
The extension respects shopwares SEO forwarding behavior setting, either a 301 redirect or a rel=\"canonical\" HTTP header.

The problem is described in this issue, which is open since 2020: https://github.com/shopware/platform/issues/468

There are multiple forum discussion where people complain about it: https://forum.shopware.com/t/seiten-nur-mit-am-ende-der-url-aufrufbar/65242

## How to use it

Just install and activate the plugin. That's it.

## How it works

The `Shopware\Core\Content\Seo\SeoResolver` class resolves SEO URLs to native shopware URLs.
`SeoResolver` is called by `Shopware\Storefront\Framework\Routing\RequestTransformer` for each request.
`SeoResolver` searches in the seo_url for an entry that matches the actual path (`seo_path_info`).
That entry will contain the shopware native path (`path_info`).
`RequestTransformer` then replaces the actual path of the request URL with the value of `path_info`.
If `SeoResolver` does not find a matching entry, it just returns the actual path for `path_info`.

Luckily, `SeoResolver` is not called directly by `RequestTransformer`.
Instead, an instance of `AbstractSeoResolver`, which `SeoResolver` extends, is injected as a service into `RequestTransformer`.
That means we can use [Symfonys service decoration pattern](https://symfony.com/doc/current/service_container/service_decoration.html) to adjust the behavior of `SeoResolver` to our needs.
See [this chapter](https://developer.shopware.com/docs/guides/plugins/plugins/plugin-fundamentals/adjusting-service#decorating-the-service) in the Shopware 6 documentation for an explanation of how that is done.
