<?php


namespace NiceshopsDev\Bean\BeanParser;



use NiceshopsDev\Bean\BeanException;
use NiceshopsDev\Bean\BeanInterface;

abstract class AbstractBeanParser implements BeanParserInterface
{
    /**
     * @param array         $data_Map
     * @param BeanInterface $bean
     *
     * @return LazyBeanParserInterface
     */
    public function parse(array $data_Map, BeanInterface $bean): LazyBeanParserInterface
    {
        return new class(
            function(?string $dataType, $value) {return $this->convertValueByDataType($dataType, $value);},
            function(string $name, $value, $originalVlaue) {return $this->parseValueByName($name, $value, $originalVlaue);},
            $data_Map, $bean
        ) implements LazyBeanParserInterface {
            /**
             * @var callable
             */
            private $convertValue;

            /**
             * @var callable
             */
            private $parseValue;

            /**
             * @var array
             */
            private $data_Map;

            /**
             * @var BeanInterface
             */
            private $bean;

            /**
             *  constructor.
             *
             * @param callable      $convertValue
             * @param callable      $parseValue
             * @param array         $data_Map
             * @param BeanInterface $bean
             */
            public function __construct(callable $convertValue, callable $parseValue, array $data_Map, BeanInterface $bean)
            {
                $this->convertValue = $convertValue;
                $this->parseValue = $parseValue;
                $this->data_Map = $data_Map;
                $this->bean = $bean;
            }
            /**
             * @param string $dataType
             * @param        $value
             *
             * @return mixed
             */
            private function convertValueByDataType(?string $dataType, $value)
            {
                return ($this->convertValue)($dataType, $value);
            }

            /**
             * @param string $name
             * @param        $value
             *
             * @param        $originalValue
             *
             * @return mixed
             */
            private function parseValueByName(string $name, $value, $originalValue)
            {
                return ($this->parseValue)($name, $value, $originalValue);
            }

            /**
             * @param bool $returnNew
             *
             * @return BeanInterface
             */
            public function toBean(bool $returnNew = false): BeanInterface
            {
                $bean = $this->bean;
                if ($returnNew) {
                    $bean = new $bean();
                }
                foreach ($this->data_Map as $name => $value) {
                    $bean->setData($name, $this->getValue($name));
                }

                return $bean;
            }

            /**
             * @param string $name
             *
             * @return mixed
             */
            public function getValue(string $name)
            {
                $dataMap = $this->data_Map;
                try {
                    $result = $this->convertValueByDataType($this->bean->getDataType($name), $dataMap[$name]);
                    $result = $this->parseValueByName($name, $result, $dataMap[$name]);
                } catch (\Exception $exception) {
                    throw new BeanException("Unable to parse $name.", 0, $exception);
                }
                return $result;
            }
        };
    }


    /**
     * @param string $dataType
     * @param        $value
     *
     * @return mixed
     */
    abstract protected function convertValueByDataType(?string $dataType, $value);

    /**
     * @param string $name
     * @param        $value
     * @param        $originalValue
     *
     * @return mixed
     */
    abstract protected function parseValueByName(string $name, $value, $originalValue);

}
