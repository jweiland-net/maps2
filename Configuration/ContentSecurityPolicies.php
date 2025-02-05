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

return Map::fromEntries(
    [
        Scope::backend(),
        new MutationCollection(
            new Mutation(
                MutationMode::Set,
                Directive::DefaultSrc,
                SourceKeyword::self,
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::ScriptSrc,
                SourceKeyword::nonceProxy,
                SourceScheme::https,
                SourceKeyword::strictDynamic,
                SourceKeyword::unsafeEval,
                SourceScheme::blob,
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::ImgSrc,
                SourceKeyword::self,
                new UriValue('https://*.googleapis.com'),
                new UriValue('https://*.gstatic.com'),
                new UriValue('*.google.com'),
                new UriValue('*.googleusercontent.com'),
                new UriValue('*.openstreetmap.org'),
                SourceScheme::data
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::FrameSrc,
                new UriValue('*.google.com'),
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::ConnectSrc,
                new UriValue('https://*.googleapis.com'),
                new UriValue('*.google.com'),
                new UriValue('https://*.gstatic.com'),
                new UriValue('https://nominatim.openstreetmap.org'),
                SourceScheme::data,
                SourceScheme::blob
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::FontSrc,
                new UriValue('https://fonts.gstatic.com'),
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::StyleSrc,
                SourceKeyword::nonceProxy,
                new UriValue('https://fonts.googleapis.com')
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::WorkerSrc,
                SourceScheme::blob,
            ),
        ),
    ],
    [
        Scope::frontend(),
        new MutationCollection(
            new Mutation(
                MutationMode::Set,
                Directive::DefaultSrc,
                SourceKeyword::self,
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::ScriptSrc,
                SourceKeyword::nonceProxy,
                SourceScheme::https,
                SourceKeyword::strictDynamic,
                SourceKeyword::unsafeEval,
                SourceScheme::blob,
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::ImgSrc,
                SourceKeyword::self,
                new UriValue('https://*.googleapis.com'),
                new UriValue('https://*.gstatic.com'),
                new UriValue('*.google.com'),
                new UriValue('*.googleusercontent.com'),
                new UriValue('*.openstreetmap.org'),
                SourceScheme::data
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::FrameSrc,
                new UriValue('*.google.com'),
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::ConnectSrc,
                new UriValue('https://*.googleapis.com'),
                new UriValue('*.google.com'),
                new UriValue('https://*.gstatic.com'),
                new UriValue('https://nominatim.openstreetmap.org'),
                SourceScheme::data,
                SourceScheme::blob
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::FontSrc,
                new UriValue('https://fonts.gstatic.com'),
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::StyleSrc,
                SourceKeyword::nonceProxy,
                new UriValue('https://fonts.googleapis.com')
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::WorkerSrc,
                SourceScheme::blob,
            ),
        ),
    ]
);
