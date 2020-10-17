<?php


namespace NiceshopsDev\Bean\BeanFormatter;



use NiceshopsDev\Bean\BeanException;
use NiceshopsDev\Bean\BeanInterface;

abstract class AbstractBeanFormatter implements BeanFormatterInterface
{
    /**
     * @param BeanInterface $bean
     *
     * @return LazyBeanFormatterInterface
     */
    public function format(BeanInterface $bean): LazyBeanFormatterInterface
    {
        return new class(
            function(?string $dataType, $value) {return $this->convertValueByDataType($dataType, $value);},
            function(string $name, $value, $originalValue) {return $this->formatValueByName($name, $value, $originalValue);},
            $bean
        ) implements LazyBeanFormatterInterface {
            /**
             * @var callable
             */
            private $convertValue;

            /**
             * @var callable
             */
            private $formatValue;

            /**
             * @var BeanInterface
             */
            private $bean;

            /**
             *  constructor.
             *
             * @param BeanInterface          $bean
             * @param callable $convertValue
             */
            public function __construct(callable $convertValue, callable $formatValue, BeanInterface $bean)
            {
                $this->bean = $bean;
                $this->convertValue = $convertValue;
                $this->formatValue = $formatValue;
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
            private function formatValueByName(string $name, $value, $originalValue)
            {
                return ($this->formatValue)($name, $value, $originalValue);
            }

            /**
             * @return array
             */
            public function toArray(): array
            {
                $data_Map = [];
                foreach ($this->bean as $name => $value) {
                    try {
                        $result = $this->convertValueByDataType($this->bean->getDataType($name), $value);
                        $result = $this->formatValueByName($name, $result, $value);
                    } catch (\Exception $exception) {
                        throw new BeanException("Unable to format $name.", 0, $exception);
                    }

                    $data_Map[$name] = $result;
                }
                return $data_Map;
            }

            /**
             * @param string $name
             *
             * @return mixed
             */
            public function getValue(string $name)
            {
                $value = $this->bean->getData($name);
                $result = $this->convertValueByDataType($this->bean->getDataType($name), $value);
                return $this->formatValueByName($name, $result, $value);
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
     *
     * @param        $originalValue
     *
     * @return mixed
     */
    abstract protected function formatValueByName(string $name, $value, $originalValue);

}
