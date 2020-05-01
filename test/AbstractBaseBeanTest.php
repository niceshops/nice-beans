<?php
declare(strict_types=1);
/**
 * @see       https://github.com/niceshops/nice-beans for the canonical source repository
 * @license   https://github.com/niceshops/nice-beans/blob/master/LICENSE BSD 3-Clause License
 */

namespace NiceshopsDev\Bean;

use ArrayObject;
use Generator;
use NiceshopsDev\Bean\BeanList\BeanListInterface;
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


//    /**
//     * @group  integration
//     * @small
//     *
//     * @covers \NiceshopsDev\Bean\AbstractBaseBean::setData
//     * @uses \NiceshopsDev\Bean\AbstractBaseBean::normalizeDataValue_for_normalizedDataName
//     * @throws BeanException
//     * @todo implement BeanInterface at AbstractBaseBean to enable test "testSetData_with_structuredData_and_DataTypes"
//     */
//    public function testSetData_with_structuredData_and_DataTypes()
//    {
//        $this->markTestSkipped("implement BeanInterface at AbstractBaseBean to enable test");
//        $bean = new class extends AbstractBaseBean {
//
//
//            /**
//             *  constructor.
//             */
//            public function __construct()
//            {
//                parent::__construct();
//                $this->setDataType("database", AbstractBaseBean::DATA_TYPE_ARRAY);
//                $this->setDataType("database.table", AbstractBaseBean::DATA_TYPE_STRING);
//                $this->setDataType("database.column.identity", AbstractBaseBean::DATA_TYPE_STRING);
//                $this->setDataType("database.column", AbstractBaseBean::DATA_TYPE_ARRAY);
//            }
//        };
//
//        $bean->setData("database", []);
//        $this->assertSame(["column" => ["identity" => null], "table" => null], $bean->getData("database"));
//
//        $bean->setData("database.column", ["identity" => true]);
//        $this->assertSame(["column" => ["identity" => "1"], "table" => null], $bean->getData("database"));
//
//        $bean->setData("database.column", ["identity" => null]);
//        $this->assertSame(["column" => ["identity" => null], "table" => null], $bean->getData("database"));
//
//        $bean->setData("database.column.identity", 123);
//        $this->assertSame(["column" => ["identity" => "123"], "table" => null], $bean->getData("database"));
//    }
    
    
    /**
     * @return Generator
     */
    public function normalizeDataValue_for_normalizedDataNameDataProvider()
    {
        yield [[], []];
        yield [["foo" => "bar", "baz" => "bat", "bat" => "bam"], ["bat", "baz", "foo"]];
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @dataProvider normalizeDataValue_for_normalizedDataNameDataProvider
     *
     * @covers       \NiceshopsDev\Bean\AbstractBaseBean::normalizeDataValue_for_normalizedDataName
     *
     * @param array $arrDataName_with_DataTypeDefinition_DefaultValue_Map [ <DATA_NAME> => <DEFAULT_VALUE>, ... ]
     * @param array $arrDataName_with_DataTypeDefinition_sorted
     */
    public function testNormalizeDataValue_for_normalizedDataName(
        array $arrDataName_with_DataTypeDefinition_DefaultValue_Map,
        array $arrDataName_with_DataTypeDefinition_sorted
    ) {
        $normalizedDataName = "foo";
        $arrDataName_with_DataTypeDefinition = array_keys($arrDataName_with_DataTypeDefinition_DefaultValue_Map);
        $arrSetData_Param = [];
        $arrGetData_with_DefaultValue_Return = [];
        $arrGetData_with_DefaultValue_Param = [];
        
        foreach ($arrDataName_with_DataTypeDefinition_sorted as $dataName) {
            $defaultValue = $arrDataName_with_DataTypeDefinition_DefaultValue_Map[$dataName];
            $arrSetData_Param[] = [$dataName, $defaultValue];
            $arrGetData_with_DefaultValue_Param[] = [$dataName];
            $arrGetData_with_DefaultValue_Return[] = $defaultValue;
        }
        
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(
            ["getDataName_List_with_DataNamePrefix_and_DataTypeDefinition", "setData", "getData_with_DefaultValue"]
        )->getMockForAbstractClass();
        
        $this->object->expects($this->once())->method("getDataName_List_with_DataNamePrefix_and_DataTypeDefinition")->with(
            ...[$normalizedDataName, true]
        )->willReturn($arrDataName_with_DataTypeDefinition);
        
        if ($arrDataName_with_DataTypeDefinition) {
            $this->object->expects($this->exactly(count($arrDataName_with_DataTypeDefinition)))->method("getData_with_DefaultValue")->withConsecutive(
                ...$arrGetData_with_DefaultValue_Param
            )->willReturn(...$arrGetData_with_DefaultValue_Return);
            $this->object->expects($this->exactly(count($arrDataName_with_DataTypeDefinition)))->method("setData")->withConsecutive(...$arrSetData_Param);
        } else {
            $this->object->expects($this->never())->method("getData_with_DefaultValue");
            $this->object->expects($this->never())->method("setData");
        }
        
        $this->assertSame($this->object, $this->invokeMethod($this->object, "normalizeDataValue_for_normalizedDataName", $normalizedDataName));
    }
    
    
    /**
     * @return Generator
     */
    public function getData_with_DefaultValue_hasDataDataProvider()
    {
        yield ["", "", ""];
        yield ["", "bar", "bar"];
        yield ["foo", "bar", "bar"];
        yield ["foo", "", ""];
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @dataProvider getData_with_DefaultValue_hasDataDataProvider
     *
     * @covers       \NiceshopsDev\Bean\AbstractBaseBean::getData_with_DefaultValue
     *
     * @param string $name
     * @param        $value
     * @param        $expectedValue
     */
    public function testGetData_with_DefaultValue_hasData(string $name, $value, $expectedValue)
    {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(
            ["hasData", "getData", "getDefaultValue_for_DataType", "getDataType"]
        )->getMockForAbstractClass();
        
        $this->object->expects($this->once())->method("hasData")->with(...[$name])->willReturn(true);
        $this->object->expects($this->once())->method("getData")->with(...[$name])->willReturn($value);
        
        $this->object->expects($this->never())->method("getDefaultValue_for_DataType");
        $this->object->expects($this->never())->method("getDataType");
        
        $this->assertSame($expectedValue, $this->invokeMethod($this->object, "getData_with_DefaultValue", $name));
    }
    
    
    /**
     * @return Generator
     */
    public function getData_with_DefaultValue_hasDataTypeDataProvider()
    {
        yield ["", "", "", ""];
        yield ["foo", "", "", ""];
        yield ["", "bar", "", ""];
        yield ["", "", "baz", "baz"];
        yield ["foo", "bar", "", ""];
        yield ["foo", "", "baz", "baz"];
        yield ["", "bar", "baz", "baz"];
        yield ["foo", "bar", "baz", "baz"];
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @dataProvider getData_with_DefaultValue_hasDataTypeDataProvider
     *
     * @covers       \NiceshopsDev\Bean\AbstractBaseBean::getData_with_DefaultValue
     *
     * @param string $name
     * @param string $dataType
     * @param        $value
     * @param        $expectedValue
     */
    public function testGetData_with_DefaultValue_hasDataType(string $name, $dataType, $value, $expectedValue)
    {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(
            ["hasData", "getData", "getDefaultValue_for_DataType", "getDataType"]
        )->getMockForAbstractClass();
        
        $this->object->expects($this->once())->method("hasData")->with(...[$name])->willReturn(false);
        $this->object->expects($this->once())->method("getDataType")->with(...[$name])->willReturn($dataType);
        $this->object->expects($this->once())->method("getDefaultValue_for_DataType")->with(...[$dataType])->willReturn($value);
        
        $this->object->expects($this->never())->method("getData");
        
        $this->assertSame($expectedValue, $this->invokeMethod($this->object, "getData_with_DefaultValue", $name));
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @covers       \NiceshopsDev\Bean\AbstractBaseBean::getData_with_DefaultValue
     */
    public function testGetData_with_DefaultValue_noData_and_noDataType()
    {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(
            ["hasData", "getData", "getDefaultValue_for_DataType", "getDataType"]
        )->getMockForAbstractClass();
        
        $name = "foo";
        $expectedValue = null;
        
        $this->object->expects($this->once())->method("hasData")->with(...[$name])->willReturn(false);
        $this->object->expects($this->once())->method("getDataType")->with(...[$name])->willReturn(null);
        
        $this->object->expects($this->never())->method("getData");
        $this->object->expects($this->never())->method("getDefaultValue_for_DataType");
        
        $this->assertSame($expectedValue, $this->invokeMethod($this->object, "getData_with_DefaultValue", $name));
    }
    
    
    /**
     * @return Generator
     */
    public function getDefaultValue_for_DataTypeDataProvider()
    {
        yield ["", null];
        yield [AbstractBaseBean::DATA_TYPE_BOOL, null];
        yield [AbstractBaseBean::DATA_TYPE_INT, null];
        yield [AbstractBaseBean::DATA_TYPE_STRING, null];
        yield [AbstractBaseBean::DATA_TYPE_FLOAT, null];
        yield [AbstractBaseBean::DATA_TYPE_CALLABLE, null];
        yield [AbstractBaseBean::DATA_TYPE_ITERABLE, null];
        yield [AbstractBaseBean::DATA_TYPE_OBJECT, null];
        yield [AbstractBaseBean::DATA_TYPE_DATE, null];
        yield [AbstractBaseBean::DATA_TYPE_DATETIME_PHP, null];
        yield [AbstractBaseBean::DATA_TYPE_ARRAY, []];
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @dataProvider getDefaultValue_for_DataTypeDataProvider
     *
     * @covers       \NiceshopsDev\Bean\AbstractBaseBean::getDefaultValue_for_DataType
     *
     * @param string $dataType
     * @param        $expectedValue
     */
    public function testGetDefaultValue_for_DataType(string $dataType, $expectedValue)
    {
        $this->assertSame($expectedValue, $this->invokeMethod($this->object, "getDefaultValue_for_DataType", $dataType));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getDataType_List
     */
    public function testGetDataType_List()
    {
        $this->assertTrue(is_array($this->invokeMethod($this->object, "getDataType_List")));
    }
    
    
    /**
     * @return Generator
     */
    public function getDataName_List_with_DataNamePrefix_and_DataTypeDefinitionDataProvider()
    {
        yield [[], "", true, null, []];
        yield [
            ["foo" => "array", "foo.baz" => "array", "foo.baz.bat" => "bool", "foo.bar" => "string", "bar.baz" => "int"],
            "foo",
            true,
            null,
            ["foo.baz", "foo.baz.bat", "foo.bar"],
        ];
        yield [
            ["foo" => "array", "foo.baz" => "array", "foo.baz.bat" => "bool", "foo.bar" => "string", "bar.baz" => "int"],
            "foo.baz",
            true,
            null,
            ["foo.baz.bat"],
        ];
        yield [
            ["foo" => "array", "foo.baz" => "array", "foo.baz.bat" => "bool", "foo.bar" => "string", "bar.baz" => "int"],
            "foo.bar",
            true,
            null,
            [],
        ];
        yield [
            ["foo" => "array", "foo.baz" => "array", "foo.baz.bat" => "bool", "foo.bar" => "string", "bar.baz" => "int"],
            "bar",
            true,
            null,
            ["bar.baz"],
        ];
        yield [
            ["foo" => "array", "foo.baz" => "array", "foo.baz.bat" => "bool", "foo.bar" => "string", "bar.baz" => "int"],
            "baz",
            true,
            null,
            [],
        ];
        
        yield [
            ["foo" => "array", "foo.baz" => "array", "foo.baz.bat" => "bool", "foo.bar" => "string", "bar.baz" => "int"],
            "foo",
            false,
            "array",
            ["foo", "foo.baz", "foo.baz.bat", "foo.bar"],
        ];
        yield [
            ["foo" => "array", "foo.baz" => "array", "foo.baz.bat" => "bool", "foo.bar" => "string", "bar.baz" => "int"],
            "bar",
            false,
            "array",
            ["bar", "bar.baz"],
        ];
        yield [
            ["foo" => "array", "foo.baz" => "array", "foo.baz.bat" => "bool", "foo.bar" => "string", "bar.baz" => "int"],
            "bar",
            false,
            null,
            ["bar.baz"],
        ];
        yield [
            ["foo" => "array", "foo.baz" => "array", "foo.baz.bat" => "bool", "foo.bar" => "string", "bar.baz" => "int"],
            "baz",
            false,
            null,
            [],
        ];
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @dataProvider getDataName_List_with_DataNamePrefix_and_DataTypeDefinitionDataProvider
     *
     * @covers       \NiceshopsDev\Bean\AbstractBaseBean::getDataName_List_with_DataNamePrefix_and_DataTypeDefinition
     *
     * @param array       $arrDataType [ <DATA_NAME> => <DATA_TYPE>, ... ]
     * @param string      $normalizedDataNamePrefix
     * @param bool        $ignoreSelf
     * @param string|null $normalizedDataNamePrefixDataType
     * @param             $expectedValue
     */
    public function testGetDataName_List_with_DataNamePrefix_and_DataTypeDefinition(
        array $arrDataType,
        string $normalizedDataNamePrefix,
        bool $ignoreSelf,
        ?string $normalizedDataNamePrefixDataType,
        $expectedValue
    ) {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(
            ["getDataType_List", "getDataType"]
        )->getMockForAbstractClass();
        
        $this->object->expects($this->once())->method("getDataType_List")->willReturn($arrDataType);
        
        if (!$ignoreSelf) {
            $this->object->expects($this->once())->method("getDataType")->with(...[$normalizedDataNamePrefix])->willReturn($normalizedDataNamePrefixDataType);
        } else {
            $this->object->expects($this->never())->method("getDataType");
        }
        
        
        $this->assertSame(
            $expectedValue, $this->invokeMethod(
            $this->object, "getDataName_List_with_DataNamePrefix_and_DataTypeDefinition", $normalizedDataNamePrefix, $ignoreSelf
        )
        );
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getValueAtObjectKey
     */
    public function testGetValueAtObjectKey_BeanList_numericOffsetExists()
    {
        $beanList = $this->getMockBuilder(BeanListInterface::class)->setMethods(["offsetExists", "offsetGet"])->getMockForAbstractClass();
        
        $object = "bar";
        $key = 0;
        $found = true;
        
        $beanList->expects($this->once())->method("offsetExists")->with(...[$key])->willReturn(true);
        $beanList->expects($this->once())->method("offsetGet")->with(...[$key])->willReturn($object);
        
        $this->assertSame([$object, $found], $this->invokeMethod($this->object, "getValueAtObjectKey", [$beanList, $key]));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getValueAtObjectKey
     */
    public function testGetValueAtObjectKey_BeanList_numericOffsetDoesNotExist()
    {
        $beanList = $this->getMockBuilder(BeanListInterface::class)->setMethods(["offsetExists"])->getMockForAbstractClass();
        $key = 0;
        $found = false;
        
        $beanList->expects($this->once())->method("offsetExists")->with(...[$key])->willReturn(false);
        
        $this->assertSame([null, $found], $this->invokeMethod($this->object, "getValueAtObjectKey", [$beanList, $key]));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getValueAtObjectKey
     */
    public function testGetValueAtObjectKey_BeanInterface_hasData()
    {
        $bean = $this->getMockBuilder(BeanInterface::class)->setMethods(["hasData", "getData"])->getMockForAbstractClass();
        
        $object = "bar";
        $key = "foo";
        $found = true;
        
        $bean->expects($this->once())->method("hasData")->with(...[$key])->willReturn(true);
        $bean->expects($this->once())->method("getData")->with(...[$key])->willReturn($object);
        
        $this->assertSame([$object, $found], $this->invokeMethod($this->object, "getValueAtObjectKey", [$bean, $key]));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getValueAtObjectKey
     */
    public function testGetValueAtObjectKey_BeanInterface_doNotHasData()
    {
        $bean = $this->getMockBuilder(BeanInterface::class)->setMethods(["hasData", "getData"])->getMockForAbstractClass();
        
        $key = "foo";
        $found = false;
        
        $bean->expects($this->once())->method("hasData")->with(...[$key])->willReturn(false);
        
        $this->assertSame([null, $found], $this->invokeMethod($this->object, "getValueAtObjectKey", [$bean, $key]));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getValueAtObjectKey
     * @uses   \NiceshopsDev\NiceCore\Helper\Object\ObjectPropertyFinder
     */
    public function testGetValueAtObjectKey_utilize_ObjectPropertyFinder()
    {
        $object = ["foo" => "bar", "baz" => null];
        
        $this->assertSame(["bar", true], $this->invokeMethod($this->object, "getValueAtObjectKey", [$object, "foo"]));
        $this->assertSame([null, true], $this->invokeMethod($this->object, "getValueAtObjectKey", [$object, "baz"]));
        $this->assertSame([null, false], $this->invokeMethod($this->object, "getValueAtObjectKey", [$object, "bar"]));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getValueAtObjectKey
     * @uses   \NiceshopsDev\NiceCore\Helper\Object\ObjectPropertyFinder
     */
    public function testGetValueAtObjectKey_utilize_ObjectPropertyFinder_withInvalidObject()
    {
        $object = "foo";
        $this->assertSame([null, false], $this->invokeMethod($this->object, "getValueAtObjectKey", [$object, "foo"]));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getObjectKeys
     */
    public function testGetObjectKeys_fromArray()
    {
        $object = ["foo" => "bar", "baz" => null];
        $this->assertSame(["foo", "baz"], $this->invokeMethod($this->object, "getObjectKeys", [$object]));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getObjectKeys
     * @uses   \NiceshopsDev\NiceCore\Helper\Object\ObjectPropertyFinder
     */
    public function testGetObjectKeys_fromObject()
    {
        $object = (object)["foo" => "bar", "baz" => null];
        $this->assertSame(["foo", "baz"], $this->invokeMethod($this->object, "getObjectKeys", [$object]));
        
        $object = new ArrayObject(["foo" => "bar", "baz" => null]);
        $this->assertSame(["foo", "baz"], $this->invokeMethod($this->object, "getObjectKeys", [$object]));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getData
     */
    public function testGetData_NameNotFound()
    {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(["findData"])->getMockForAbstractClass();
        $name = "foo";
        $result = ["found" => false];
        
        $this->expectException(BeanException::class);
        $this->expectExceptionCode(BeanException::ERROR_CODE_DATA_NOT_FOUND);
        
        $this->object->expects($this->once())->method("findData")->with(...[$name])->willReturn($result);
        
        $this->object->getData($name);
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getData
     * @throws BeanException
     */
    public function testGetData_NameFoundButNoValue()
    {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(["findData"])->getMockForAbstractClass();
        $name = "foo";
        $result = ["found" => true];
        
        $this->object->expects($this->once())->method("findData")->with(...[$name])->willReturn($result);
        
        $this->assertNull($this->object->getData($name));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::getData
     * @throws BeanException
     */
    public function testGetData_ValueFound()
    {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(["findData"])->getMockForAbstractClass();
        $name = "foo";
        $result = ["found" => true, "value" => "bar"];
        
        $this->object->expects($this->once())->method("findData")->with(...[$name])->willReturn($result);
        
        $this->assertSame("bar", $this->object->getData($name));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::hasData
     * @throws BeanException
     */
    public function testHasData_isTrue()
    {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(["findData"])->getMockForAbstractClass();
        $name = "foo";
        $result = ["found" => true];
        
        $this->object->expects($this->once())->method("findData")->with(...[$name])->willReturn($result);
        
        $this->assertTrue($this->object->hasData($name));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::hasData
     * @throws BeanException
     */
    public function testHasData_isFalse()
    {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(["findData"])->getMockForAbstractClass();
        $name = "foo";
        $result = ["found" => false];
        
        $this->object->expects($this->once())->method("findData")->with(...[$name])->willReturn($result);
        
        $this->assertFalse($this->object->hasData($name));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::removeDataType
     */
    public function testRemoveDataType()
    {
        $arrDataType = ["foo" => "bar", "baz" => "bat"];
        $this->invokeSetProperty($this->object, "arrDataType", $arrDataType);
        
        //  remove not existing data type
        $this->assertSame($this->object, $this->invokeMethod($this->object, "removeDataType", "bat"));
        $this->assertSame($arrDataType, $this->invokeGetProperty($this->object, "arrDataType"));
        
        //  remove existing data type
        $this->assertSame($this->object, $this->invokeMethod($this->object, "removeDataType", "foo"));
        $this->assertSame(["baz" => "bat"], $this->invokeGetProperty($this->object, "arrDataType"));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::removeData
     */
    public function testRemoveData_DataNotFound()
    {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(
            ["normalizeDataName", "hasData"]
        )->getMockForAbstractClass();
        $name = " foo ";
        $nameNormalized = "foo";
        $hasData = false;
        
        $this->object->expects($this->once())->method("normalizeDataName")->with(...[$name])->willReturn($nameNormalized);
        $this->object->expects($this->once())->method("hasData")->with(...[$nameNormalized])->willReturn($hasData);
        $this->expectException(BeanException::class);
        $this->expectExceptionCode(BeanException::ERROR_CODE_DATA_NOT_FOUND);
        
        $this->object->removeData($name);
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::removeData
     * @throws BeanException
     */
    public function testRemoveData_DataFound()
    {
        $this->object = $this->getMockBuilder(AbstractBaseBean::class)->disableOriginalConstructor()->setMethods(
            ["normalizeDataName", "hasData", "removeDataType", "unsetOriginalDataName"]
        )->getMockForAbstractClass();
        $name = " foo ";
        $nameNormalized = "foo";
        $hasData = true;
        $arrData = ["foo" => "bar", "baz" => "bat"];
        
        $this->invokeSetProperty($this->object, "data", $arrData);
        $this->object->expects($this->once())->method("normalizeDataName")->with(...[$name])->willReturn($nameNormalized);
        $this->object->expects($this->once())->method("hasData")->with(...[$nameNormalized])->willReturn($hasData);
        $this->object->expects($this->once())->method("removeDataType")->with(...[$nameNormalized]);
        $this->object->expects($this->once())->method("unsetOriginalDataName")->with(...[$nameNormalized]);
        
        $this->assertSame("bar", $this->object->removeData($name));
        $this->assertSame(["baz" => "bat"], $this->invokeGetProperty($this->object, "data"));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsDev\Bean\AbstractBaseBean::resetData
     */
    public function testResetData()
    {
        $arrData = ["foo" => "bar", "baz" => "bat"];
        $this->invokeSetProperty($this->object, "data", $arrData);
        
        $this->assertSame($this->object, $this->object->resetData());
        $this->assertSame([], $this->invokeGetProperty($this->object, "data"));
    }
}
