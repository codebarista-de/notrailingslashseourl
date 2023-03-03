<?php

declare(strict_types=1);

namespace Codebarista;

use Shopware\Core\Content\Seo\AbstractSeoResolver;

/**
 * @phpstan-import-type ResolvedSeoUrl from AbstractSeoResolver
 */
class NoTrailingSlashSeoResolver extends AbstractSeoResolver
{
    private AbstractSeoResolver $decorated;

    /**
     * @internal
     */
    public function __construct(AbstractSeoResolver $decorated)
    {
        $this->decorated = $decorated;
    }

    public function getDecorated(): AbstractSeoResolver
    {
        return $this->decorated;
    }

    /**
     * @return ResolvedSeoUrl
     */
    public function resolve(string $languageId, string $salesChannelId, string $pathInfo): array
    {
        /*
         The response of shopwares SeoResolver is an associative array of key value pairs:
         id: Database ID of the seo_url entity if one was found for $pathInfo otherwise not present.
         pathInfo: Shopwares native path to the page
         isCanonical: 1 if the $pathInfo is the canonical SEO path.
         canonicalPathInfo: The canonical seo path. Only present if isCanonical is false (0).
        */
        

        /** @var array $seoPath */
        $seoPath = $this->getDecorated()->resolve($languageId, $salesChannelId, $pathInfo);
        if (!$seoPath['isCanonical'] && !array_key_exists('canonicalPathInfo', $seoPath) && !str_ends_with($pathInfo, '/')) {
            // If the seoPath is not canonical but no canonicalPathInfo is set then
            // the seo path was not found in the database.
            // Unless the path already ends with a slash, append a slash to the path and try again.
            /** @var array $seoPathWithSlash */
            $seoPathWithSlash = $this->getDecorated()->resolve($languageId, $salesChannelId, $pathInfo . '/');

            // Its important that we do not just return $seoPathWithSlash,
            // because then shopware would just respond with the corresponding page.
            // However, if core.seo.redirectToCanonicalUrl is set shopware should respond with
            // a redirect to the path with the trailing slash (see CanonicalRedirectService) and
            // otherwise add the rel="canonical" HTTP header to the response with the canonical SEO path.
            // Without that two different URLs point to the same page and search engines will flag and
            // penalize that as duplicate content.
            if ($seoPathWithSlash['isCanonical']) {
                // The path with a trailing slash is the canonical SEO path.
                $seoPath['pathInfo'] = $seoPathWithSlash['pathInfo'];
                $seoPath['canonicalPathInfo'] = '/' . $pathInfo . '/';
            } else if (array_key_exists('canonicalPathInfo', $seoPathWithSlash)) {
                // The path with a trailing slash is a valid SEO path but not the canonical path.
                $seoPath['pathInfo'] = $seoPathWithSlash['pathInfo'];
                $seoPath['canonicalPathInfo'] = $seoPathWithSlash['canonicalPathInfo'];
            }
        }
        return $seoPath;
    }
}
