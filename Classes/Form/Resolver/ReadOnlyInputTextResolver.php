<?php
namespace JWeiland\Maps2\Form\Resolver;

/*
 * This file is part of the maps2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use JWeiland\Maps2\Form\Element\ReadOnlyInputTextElement;
use JWeiland\Maps2\Form\Element\ReadOnlyInputTextElement76;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Form\NodeResolverInterface;

/**
 * This resolver will return a full working readonly field for TYPO3 backend forms
 */
class ReadOnlyInputTextResolver implements NodeResolverInterface
{
    /**
     * Global options from NodeFactory
     *
     * @var array
     */
    protected $data = [];

    /**
     * Default constructor receives full data array
     *
     * @param NodeFactory $nodeFactory
     * @param array $data
     */
    public function __construct(NodeFactory $nodeFactory, array $data)
    {
        $this->data = $data;
    }

    /**
     * Returns RichTextElement as class name if RTE widget should be rendered.
     *
     * @return string|null New class name or void if this resolver does not change current class name.
     */
    public function resolve()
    {
        if (version_compare(TYPO3_branch, '7.6') <= 0) {
            return ReadOnlyInputTextElement76::class;
        } else {
            return ReadOnlyInputTextElement::class;
        }
    }
}
