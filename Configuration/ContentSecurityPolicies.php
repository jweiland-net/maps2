<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

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
                MutationMode::Extend,
                Directive::ScriptSrcElem,
                SourceKeyword::strictDynamic, // requires(!) Nonce everywhere
                SourceScheme::https, // thx Google!
                SourceKeyword::unsafeEval, // thx Google!
                SourceScheme::blob, // thx Google!
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::ImgSrc,
                new UriValue('https://*.googleapis.com'),
                new UriValue('https://*.gstatic.com'),
                new UriValue('*.google.com'),
                new UriValue('*.googleusercontent.com'),
                new UriValue('*.openstreetmap.org'),
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::FrameSrc,
                new UriValue('*.google.com'),
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::ConnectSrc,
                new UriValue('*.google.com'),
                new UriValue('https://*.googleapis.com'),
                new UriValue('https://*.gstatic.com'),
                new UriValue('https://nominatim.openstreetmap.org'),
                SourceScheme::blob, // thx Google!
                SourceScheme::data, // thx Google!
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::FontSrc,
                new UriValue('https://fonts.gstatic.com'),
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::StyleSrcElem,
                SourceKeyword::unsafeInline,
                new UriValue('https://fonts.gstatic.com'),
                new UriValue('https://fonts.googleapis.com'),
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
                MutationMode::Extend,
                Directive::ScriptSrcElem,
                SourceKeyword::strictDynamic, // requires(!) Nonce everywhere
                SourceScheme::https, // thx Google!
                SourceKeyword::unsafeEval, // thx Google!
                SourceScheme::blob, // thx Google!
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::ImgSrc,
                new UriValue('https://*.googleapis.com'),
                new UriValue('https://*.gstatic.com'),
                new UriValue('*.google.com'),
                new UriValue('*.googleusercontent.com'),
                new UriValue('*.openstreetmap.org'),
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::FrameSrc,
                new UriValue('*.google.com'),
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::ConnectSrc,
                new UriValue('*.google.com'),
                new UriValue('https://*.googleapis.com'),
                new UriValue('https://*.gstatic.com'),
                new UriValue('https://nominatim.openstreetmap.org'),
                SourceScheme::blob, // thx Google!
                SourceScheme::data, // thx Google!
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::FontSrc,
                new UriValue('https://fonts.gstatic.com'),
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::StyleSrcElem,
                SourceKeyword::unsafeInline,
                new UriValue('https://fonts.gstatic.com'),
                new UriValue('https://fonts.googleapis.com'),
            ),
            new Mutation(
                MutationMode::Extend,
                Directive::WorkerSrc,
                SourceScheme::blob,
            ),
        ),
    ],
);
