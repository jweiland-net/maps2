<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceKeyword;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceScheme;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\UriValue;
use TYPO3\CMS\Core\Type\Map;

return Map::fromEntries([
    Scope::backend(),
    // NOTICE: When using `MutationMode::Set` existing declarations will be overridden

    new MutationCollection(
        new Mutation(
            MutationMode::Set,
            Directive::DefaultSrc,
            SourceKeyword::self,
        ),
        new Mutation(
            MutationMode::Extend,
            Directive::ScriptSrc,
            SourceScheme::https,
            new UriValue('https://maps.googleapis.com'),
        ),
        new Mutation(
            MutationMode::Extend,
            Directive::ConnectSrc,
            SourceScheme::https,
            new UriValue('https://maps.googleapis.com'),
        ),
        new Mutation(
            MutationMode::Extend,
            Directive::FontSrc,
            SourceScheme::https,
            new UriValue('https://maps.gstatic.com'),
        ),
        new Mutation(
            MutationMode::Extend,
            Directive::ImgSrc,
            SourceScheme::https,
            new UriValue('https://maps.gstatic.com'),
        ),
        new Mutation(
            MutationMode::Extend,
            Directive::ImgSrc,
            SourceScheme::https,
            new UriValue('https://maps.googleapis.com'),
        ),
        new Mutation(
            MutationMode::Extend,
            Directive::StyleSrcElem,
            SourceScheme::https,
            new UriValue('https://fonts.googleapis.com'),
        ),
    ),
]);
