<?php
declare(strict_types=1);
/**
 * @see       https://github.com/niceshops/nice-beans for the canonical source repository
 * @license   https://github.com/niceshops/nice-beans/blob/master/LICENSE BSD 3-Clause License
 */

namespace NiceshopsDev\Bean;

use NiceshopsDev\Bean\PHPUnit\DefaultTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AbstractBaseBeanTest
 * @package NiceshopsDev\Bean
 */
class AbstractBaseBeanTest extends DefaultTestCase
{
    
    
    /**
     * @var AbstractBaseBean|MockObject
     */
    protected $object;
    
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     */
    protected function setUp()
    {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->getMockForAbstractClass();
    }
    
    
    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
    
    
    /**
     * @group integration
     * @small
     */
    public function testTestClassExists()
    {
        $this->assertTrue(class_exists(AbstractBaseBean::class), "Class Exists");
        $this->assertTrue(is_a($this->object, AbstractBaseBean::class), "Mock Object is set");
    }
    
    
    /**
     * @return array
     */
    public function normalizeDataNameDataProvider()
    {
        return [
            ["foo", "foo", null],
            ["Foo", "foo", null],
            ["FOO", "foo", null],
            [" foo ", "foo", null],
            [" FOO ", "foo", null],
            [" Foo ", "foo", null],
            ["123", "123", null],
            [123, "123", null],
            ["FooBarBaz", "foobarbaz", null],
            ["foo_bar_baz", "foo_bar_baz", null],
            ["foo-bar-baz", "foo-bar-baz", null],
            ["__foo__", "__foo__", null],
            ["öäüßÖÄÜ", "öäüßÖÄÜ", null],
            ["", "", BeanException::class],
        ];
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @dataProvider normalizeDataNameDataProvider
     *
     * @covers       \NiceshopsDev\Bean\AbstractBaseBean::normalizeDataName()
     *
     * @param        $name
     * @param string $expectedValue
     * @param string $expectedException
     */
    public function testNormalizeDataName(string $name, string $expectedValue, string $expectedException = null)
    {
        if ($expectedException) {
            $this->expectException($expectedException);
        }
        
        $this->assertSame($expectedValue, $this->invokeMethod($this->object, "normalizeDataName", $name));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::setOriginalDataName
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getOriginalDataName
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::unsetOriginalDataName
     */
    public function testSetGetUnsetOriginalDataName()
    {
        
        //  if no original name is set the method "getOriginalDataName" just return the passed name
        $this->assertSame("Foo", $this->invokeMethod($this->object, "getOriginalDataName", ["Foo"]));
        $this->assertSame("foo", $this->invokeMethod($this->object, "getOriginalDataName", ["foo"]));
        
        //  set original name
        $this->invokeMethod($this->object, "setOriginalDataName", ["Foo", "foo"]);
        $this->assertSame("Foo", $this->invokeMethod($this->object, "getOriginalDataName", ["Foo"]));
        $this->assertSame("Foo", $this->invokeMethod($this->object, "getOriginalDataName", ["foo"]));
        
        //  try to unset none existing original name
        $this->invokeMethod($this->object, "unsetOriginalDataName", ["Foo"]);
        $this->assertSame("Foo", $this->invokeMethod($this->object, "getOriginalDataName", ["Foo"]));
        $this->assertSame("Foo", $this->invokeMethod($this->object, "getOriginalDataName", ["foo"]));
        
        //  unset existing original name
        $this->invokeMethod($this->object, "unsetOriginalDataName", ["foo"]);
        $this->assertSame("Foo", $this->invokeMethod($this->object, "getOriginalDataName", ["Foo"]));
        $this->assertSame("foo", $this->invokeMethod($this->object, "getOriginalDataName", ["foo"]));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getDataType
     */
    public function testGetDataType_isString()
    {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(
            ["normalizeDataName"]
        )->getMockForAbstractClass();
        $name = " foo ";
        $key = "foo";
        $this->invokeSetProperty($this->object, "arrDataType", [$key => "bar"]);
        
        $this->object->expects($this->once())->method("normalizeDataName")->with(...[$name])->willReturn($key);
        
        $this->assertSame("bar", $this->invokeMethod($this->object, "getDataType", $name));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getDataType
     */
    public function testGetDataType_isCallable()
    {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(
            ["normalizeDataName"]
        )->getMockForAbstractClass();
        $name = " foo ";
        $key = "foo";
        $callable = function () {
        };
        $this->invokeSetProperty($this->object, "arrDataType", [$key => $callable]);
        
        $this->object->expects($this->once())->method("normalizeDataName")->with(...[$name])->willReturn($key);
        
        $this->assertSame(AbstractBaseBean::DATA_TYPE_CALLABLE, $this->invokeMethod($this->object, "getDataType", $name));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getDataType
     */
    public function testGetDataType_isNull()
    {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(
            ["normalizeDataName"]
        )->getMockForAbstractClass();
        $name = " foo ";
        $key = "foo";
        $this->invokeSetProperty($this->object, "arrDataType", []);
        
        $this->object->expects($this->once())->method("normalizeDataName")->with(...[$name])->willReturn($key);
        
        $this->assertNull($this->invokeMethod($this->object, "getDataType", $name));
    }
}
