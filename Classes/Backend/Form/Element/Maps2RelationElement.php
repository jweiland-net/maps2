<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Backend\Form\Element;

use TYPO3\CMS\Backend\Form\Element\GroupElement;

/**
 * Dedicated form element for maps2 relation fields.
 *
 * This class extends TYPO3's GroupElement without changing its behavior. Its
 * sole purpose is to provide a custom renderType that identifies maps2 relation
 * fields in TCA.
 *
 * The required group field configuration is completed during TCA compilation,
 * allowing integrators and developers to use a consistent and minimal setup for
 * maps2 relations.
 */
class Maps2RelationElement extends GroupElement {}
