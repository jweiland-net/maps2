<?php
namespace JWeiland\Maps2\Controller;

/**
 * This file is part of the TYPO3 CMS project.
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
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class AbstractController
 *
 * @category Controller
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class AbstractController extends ActionController
{

    /**
     * @var \JWeiland\Maps2\Configuration\ExtConf
     */
    protected $extConf;

    /**
     * @var \JWeiland\Maps2\Utility\DataMapper
     */
    protected $dataMapper;

    /**
     * inject extConf
     *
     * @param \JWeiland\Maps2\Configuration\ExtConf $extConf
     * @return void
     */
    public function injectExtConf(\JWeiland\Maps2\Configuration\ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    /**
     * inject dataMapper
     *
     * @param \JWeiland\Maps2\Utility\DataMapper $dataMapper
     * @return void
     */
    public function injectDataMapper(\JWeiland\Maps2\Utility\DataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    /**
     * initialize view
     * add some global vars to view
     *
     * @return void
     */
    public function initializeView()
    {
        $this->view->assign('extConf', ObjectAccess::getGettableProperties($this->extConf));
        $this->view->assign('id', $GLOBALS['TSFE']->id);
        $this->view->assign('data', $this->configurationManager->getContentObject()->data);
    }

    /**
     * prepare address for an uri
     * further it will add some additional informations like country
     *
     * @param string $address The address to update
     * @return string A prepared address which is valid for an uri
     */
    public function updateAddressForUri($address)
    {
        // check if it can be interpreted as a zip code
        if (MathUtility::canBeInterpretedAsInteger($address) && strlen($address) === 5) {
            $address .= ' Deutschland';
        }
        return rawurlencode($address);
    }
}
