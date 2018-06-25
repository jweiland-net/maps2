<?php
namespace JWeiland\Maps2\Tests\Unit\Domain\Model;

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

use JWeiland\Maps2\Domain\Model\RadiusResult;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class RadiusResultTest
 */
class RadiusResultTest extends UnitTestCase
{
    /**
     * @var RadiusResult
     */
    protected $subject;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->subject = new RadiusResult();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getAddressComponentsInitiallyReturnsObjectStorage() {
        $this->assertEquals(
            new ObjectStorage(),
            $this->subject->getAddressComponents()
        );
    }

    /**
     * @test
     */
    public function setAddressComponentsSetsAddressComponents() {
        $object = new RadiusResult\AddressComponent();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setAddressComponents($objectStorage);

        $this->assertSame(
            $objectStorage,
            $this->subject->getAddressComponents()
        );
    }

    /**
     * @test
     */
    public function addAddressComponentAddsOneAddressComponent() {
        $objectStorage = new ObjectStorage();
        $this->subject->setAddressComponents($objectStorage);

        $object = new RadiusResult\AddressComponent();
        $this->subject->addAddressComponent($object);

        $objectStorage->attach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getAddressComponents()
        );
    }

    /**
     * @test
     */
    public function removeAddressComponentRemovesOneAddressComponent() {
        $object = new RadiusResult\AddressComponent();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setAddressComponents($objectStorage);

        $this->subject->removeAddressComponent($object);
        $objectStorage->detach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getAddressComponents()
        );
    }

    /**
     * @test
     */
    public function getformattedAddressInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getformattedAddress()
        );
    }

    /**
     * @test
     */
    public function setformattedAddressSetsformattedAddress() {
        $this->subject->setformattedAddress('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getformattedAddress()
        );
    }

    /**
     * @test
     */
    public function setformattedAddressWithIntegerResultsInString() {
        $this->subject->setformattedAddress(123);
        $this->assertSame('123', $this->subject->getformattedAddress());
    }

    /**
     * @test
     */
    public function setformattedAddressWithBooleanResultsInString() {
        $this->subject->setformattedAddress(true);
        $this->assertSame('1', $this->subject->getformattedAddress());
    }

    /**
     * @test
     */
    public function getGeometryInitiallyReturnsNull() {
        $this->assertNull($this->subject->getGeometry());
    }

    /**
     * @test
     */
    public function setGeometrySetsGeometry() {
        $instance = new RadiusResult\Geometry();
        $this->subject->setGeometry($instance);

        $this->assertSame(
            $instance,
            $this->subject->getGeometry()
        );
    }

    /**
     * @test
     */
    public function getTypesInitiallyReturnsEmptyArray() {
        $this->assertSame(
            [],
            $this->subject->getTypes()
        );
    }

    /**
     * @test
     */
    public function setTypesSetsTypes() {
        $array = [
            0 => 'TestValue'
        ];
        $this->subject->setTypes($array);

        $this->assertSame(
            $array,
            $this->subject->getTypes()
        );
    }

    /**
     * @test
     */
    public function getPoiCollectionsInitiallyReturnsEmptyArray() {
        $this->assertSame(
            [],
            $this->subject->getPoiCollections()
        );
    }

    /**
     * @test
     */
    public function setPoiCollectionsSetsPoiCollections() {
        $array = [
            0 => 'TestValue'
        ];
        $this->subject->setPoiCollections($array);

        $this->assertSame(
            $array,
            $this->subject->getPoiCollections()
        );
    }
}
