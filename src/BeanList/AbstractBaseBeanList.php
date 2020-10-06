<?php
declare(strict_types=1);

namespace NiceshopsDev\Bean\BeanList;

use ArrayIterator;
use Exception;
use NiceshopsDev\Bean\BeanInterface;
use Traversable;

/**
 * Class AbstractBeanList
 * @package Niceshops\Library\Core
 */
abstract class AbstractBaseBeanList implements BeanListInterface
{

    /**
     * @var bool
     */
    protected $throwErrors = false;


    /**
     * @var BeanInterface[]
     */
    protected $arrBean = array();


    /**
     * List of class names any beans has to be derived from.
     *
     * @var array
     */
    protected $arrBeanClass = array(BeanInterface::class);


    /**
     * \Niceshops\Library\Core\Bean\BeanList\AbstractBeanList constructor.
     *
     * @param array|null $arrBeanClass
     *
     * @throws BeanListException
     */
    public function __construct(array $arrBeanClass = null)
    {
        if ($arrBeanClass) {
            $this->setBeanClasses($arrBeanClass);
        }
    }


    /**
     * @param array $arrBeanClass
     *
     * @return $this
     * @throws BeanListException
     */
    protected function setBeanClasses(array $arrBeanClass)
    {
        $arrBeanClass = array_values(
            array_filter(
                array_map("trim", $arrBeanClass), function ($val) {
                if (!is_string($val) || !class_exists($val) && !interface_exists($val)) {
                    return false;
                }

                if ($val != BeanInterface::class && !in_array(BeanInterface::class, class_implements($val))) {
                    return false;
                }

                return true;
            }
            )
        );

        if (!$arrBeanClass) {
            $this->throwError("Invalid list of bean classes defined!");
        } else {
            $this->arrBeanClass = $arrBeanClass;
        }

        return $this;
    }


    /**
     * @return array
     */
    protected function getBeanClasses()
    {
        return $this->arrBeanClass;
    }


    /**
     * @param string $msg
     * @param int $code
     * @param Exception|null $previous
     *
     * @return $this                                if $this->throwErrors is FALSE
     * @throws BeanListException       if $this->throwErrors is TRUE
     */
    protected function throwError($msg = '', $code = 0, Exception $previous = null)
    {
        if ($this->throwErrors) {
            throw new BeanListException($msg, $code, $previous);
        }

        return $this;
    }


    /**
     * Checks if the bean extends or implements any of the allowed bean classes.
     *
     * @param BeanInterface $bean
     *
     * @return bool
     * @throws BeanListException       if $this->throwErrors is TRUE and bean is invalid
     */
    protected function validateBean(BeanInterface $bean)
    {
        $valid = false;
        foreach ($this->getBeanClasses() as $beanClass) {
            if ($bean instanceof $beanClass) {
                $valid = true;
                break;
            }
        }

        if (!$valid) {
            $this->throwError(
                "Bean from class '" . get_class($bean) . "' does not extends or implements any of the required classes '" . implode(
                    ", ", $this->getBeanClasses()
                ) . "'!"
            );
        }

        return $valid;
    }


    /**
     * @param BeanInterface $bean
     *
     * @return $this
     * @throws BeanListException       if $this->throwErrors is TRUE and bean is invalid
     */
    public function addBean(BeanInterface $bean)
    {
        return $this->offsetSet($this->count(), $bean);
    }


    /**
     * @param BeanInterface $bean
     *
     * @return bool         TRUE if bean successfully removed, otherwise FALSE
     * @throws BeanListException
     */
    public function removeBean(BeanInterface $bean)
    {
        $removed = false;

        $index = $this->indexOfBean($bean);
        if ($index >= 0) {
            if ($this->offsetUnset($index)) {
                $removed = true;
            }
        }

        if (!$removed) {
            $arrBeanStr = array();
            $i = 0;
            foreach ($bean as $key => $val) {
                if ($i > 3 || !is_scalar($val)) {
                    continue;
                }
                $arrBeanStr[] = "$key=$val";
                ++$i;
            }
            $this->throwError("Could not remove Bean '" . implode(", ", $arrBeanStr) . "'!");
        }

        return $removed;
    }


    /**
     * @param BeanInterface $bean
     *
     * @return bool
     */
    public function hasBean(BeanInterface $bean)
    {
        return $this->indexOfBean($bean) >= 0;
    }


    /**
     * @param BeanInterface $bean
     *
     * @return int                  index of found bean or -1 if bean couldn't be found
     */
    public function indexOfBean(BeanInterface $bean)
    {
        $index = -1;

        foreach ($this->arrBean as $key => $val) {
            if ($val === $bean) {
                $index = $key;
                break;
            }
        }

        return $index;
    }


    /**
     * @param BeanInterface[]|Traversable $beans
     *
     * @return $this
     * @throws BeanListException
     */
    public function addBeans($beans)
    {
        if (!is_array($beans) && (!$beans instanceof Traversable)) {
            $this->throwError("Invalid beans defined (parameter has be an array of beans or a 'AbstractBeanList' instance!");

            return $this;
        }


        foreach ($beans as $bean) {
            $this->addBean($bean);
        }

        return $this;
    }


    /**
     * @return BeanInterface[]
     */
    public function getBeans(): array
    {
        return $this->arrBean;
    }


    /**
     * @param array|Traversable $beans
     *
     * @return $this
     * @throws BeanListException
     */
    public function setBeans($beans)
    {
        if (!is_array($beans) && (!$beans instanceof Traversable)) {
            $this->throwError("Invalid beans defined (parameter has be an array of beans or a 'AbstractBeanList' instance!");

            return $this;
        }


        $arrBeans = array();
        foreach ($beans as $bean) {
            if ($this->validateBean($bean)) {
                $arrBeans[] = $bean;
            }
        }

        $this->resetBeans()->addBeans($arrBeans);

        return $this;
    }


    /**
     * @return $this
     */
    public function resetBeans()
    {
        $this->arrBean = array();

        return $this;
    }


    /**
     * @return int
     */
    public function count()
    {
        return count($this->arrBean);
    }


    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->arrBean);
    }


    /**
     * @param int $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->getBeans());
    }


    /**
     * @param int $offset
     *
     * @return BeanInterface
     * @throws BeanListException
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->getBeans()[$offset];
        } else {
            $this->throwError("Bean at offset '$offset' not found!");

            return null;
        }
    }


    /**
     * @param int $offset
     * @param BeanInterface $value
     *
     * @return $this
     * @throws BeanListException
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $offset = $this->count();
        } else {
            if (!is_numeric($offset)) {
                $this->throwError("Invalid offset '$offset' defined!");

                return $this;
            } else {
                if ($offset > $this->count() && !$this->offsetExists($offset)) {
                    $this->throwError("Offset '$offset' not found!");

                    return $this;
                }
            }
        }

        if ($this->validateBean($value)) {
            $index = $this->indexOfBean($value);
            if ($index >= 0 && $index !== intval($offset)) {
                if ($this->offsetUnset($index)) {
                    --$offset;
                }
            }
            $this->arrBean[intval($offset)] = $value;
        }

        return $this;
    }


    /**
     * @param int $offset
     *
     * @return null|BeanInterface       NULL if offset couldn't be found or the removed bean
     * @throws BeanListException
     */
    public function offsetUnset($offset)
    {
        if (!$this->offsetExists($offset)) {
            $this->throwError("Offset '$offset' not found!");

            return null;
        } else {
            $arr = array_splice($this->arrBean, $offset, 1);

            return array_pop($arr);
        }
    }


    /**
     * Same behaviour like @param int $offset
     * @param null $length
     * @param int $stepWidth
     *
     * @return AbstractBaseBeanList
     * @throws BeanListException
     * @see http://php.net/manual/en/function.array-slice.php
     *
     * @see array_slice
     */
    public function slice($offset = 0, $length = null, $stepWidth = 1)
    {
        $beanList = AbstractBaseBeanList::createFromArray(array_slice($this->arrBean, $offset, $length));
        $beanList->setBeanClasses($this->getBeanClasses());
        return $beanList;
    }


    /**
     * @param callable $callback
     *
     * @return $this
     * @example bean/abstract-bean-list/each.php 2
     *
     */
    public function each(callable $callback)
    {
        foreach ($this as $key => $val) {
            call_user_func($callback, $val, $key, $this);
        }
        return $this;
    }


    /**
     * @param callable $callback
     *
     * @return bool
     * @example bean/abstract-bean-list/every.php 2
     *
     * Tests whether all beans pass the test implemented by the provided function.
     *
     */
    public function every(callable $callback)
    {
        $flag = true;

        foreach ($this as $key => $val) {
            if (!call_user_func($callback, $val, $key, $this)) {
                $flag = false;
                break;
            }
        }

        return $flag;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this) === 0;
    }


    /**
     * @param callable $callback
     * @param bool $returnBeanList if TRUE a new bean list with the found beans or NULL will be returned
     *
     * @return bool|AbstractBaseBeanList
     * @throws BeanListException
     * @example bean/abstract-bean-list/some.php 2
     *
     * tests whether some beans passes the test implemented by the provided function
     *
     */
    public function some(callable $callback, $returnBeanList = false)
    {
        $flag = false;
        $beanList = null;
        if ($returnBeanList) {
            $className = get_class($this);
            $beanList = new $className();
            /**
             * @var AbstractBaseBeanList $beanList
             */
            $beanList->setBeanClasses($this->getBeanClasses());
        }

        foreach ($this as $key => $val) {
            if (call_user_func($callback, $val, $key, $this)) {
                if ($beanList) {
                    $beanList[] = $val;
                } else {
                    $flag = true;
                    break;
                }
            }
        }

        return $returnBeanList ? $beanList : $flag;
    }


    /**
     * alias for \Niceshops\Library\Core\Bean\BeanList\AbstractBeanList::some($callback, true);
     *
     * @param callable $callback
     *
     * @return AbstractBaseBeanList
     * @throws BeanListException
     * @see \Niceshops\Library\Core\Bean\BeanList\AbstractBeanList::some
     *
     */
    public function filter(callable $callback)
    {
        return $this->some($callback, true);
    }


    /**
     * @param callable $callback
     * @param bool $returnBean if TRUE the found bean or NULL will be returned
     *
     * @return bool|BeanInterface
     * @example bean/abstract-bean-list/exclusive.php 2
     *
     * tests only one bean passes the test implemented by the provided function (XOR)
     *
     */
    public function exclusive(callable $callback, $returnBean = false)
    {
        $found = 0;
        $foundBean = null;

        foreach ($this as $key => $val) {
            if (call_user_func($callback, $val, $key, $this)) {
                $found++;

                if (!$foundBean) {
                    $foundBean = $val;
                }

                if ($found > 1) {
                    $foundBean = null;
                    break;
                }
            }
        }

        return $returnBean ? $foundBean : $found == 1;
    }


    /**
     * @param callable $callback
     *
     * @return array
     * @example bean/abstract-bean-list/map.php 2
     *
     * creates a new array with the results of calling a provided function on every bean
     *
     * TODO: add second paramter "returnBeanList = false". If TRUE, each callback has to return an array or bean and a bean list will be returned instead of an
     * array
     *
     */
    public function map(callable $callback)
    {
        $arrData = array();
        foreach ($this as $key => $val) {
            $arrData[] = call_user_func($callback, $val, $key, $this);
        }

        return $arrData;
    }


    /**
     * Reverse the bean order.
     *
     * @return $this
     */
    public function reverse()
    {
        $this->arrBean = array_reverse($this->arrBean);

        return $this;
    }


    /**
     * Alias for @param BeanInterface $bean
     *
     * @return AbstractBaseBeanList
     * @throws BeanListException
     * @see addBean and @see addBeans
     *
     */
    public function push(BeanInterface $bean)
    {
        return $this->addBeans(func_get_args());
    }


    /**
     * @param BeanInterface $bean
     *
     * @return $this
     * @throws BeanListException
     */
    public function unshift(BeanInterface $bean)
    {
        $args = array(&$this->arrBean);
        foreach (func_get_args() as $arg) {
            if ($this->validateBean($arg)) {
                $args[] = $arg;
            }
        }

        if (count($args) > 1) {
            call_user_func_array("array_unshift", $args);
        }

        return $this;
    }


    /**
     * @return BeanInterface
     */
    public function shift()
    {
        return array_shift($this->arrBean);
    }


    /**
     * @return BeanInterface
     */
    public function pop()
    {
        return array_pop($this->arrBean);
    }


    /**
     * Same behaviour like @param callable $callback
     *
     * @return $this
     * @see usort.
     *
     * @see http://php.net/manual/en/function.usort.php
     *
     */
    public function sort(callable $callback)
    {
        usort($this->arrBean, $callback);

        return $this;
    }


    /**
     * @param string $key1 a bean data key (NOTE: key has to be defined at any bean inside the bean list)
     * @param int $order1 SORT_ASC, SORT_DESC
     * @param int $flags1 SORT_REGULAR, SORT_NUMERIC, SORT_STRING, SORT_LOCALE_STRING, SORT_NATURAL, SORT_FLAG_CASE
     *
     * @return bool
     * @throws BeanListException   if $this->throwErrors = TRUE and invalid arguments passed
     * @see     http://php.net/manual/en/function.array-multisort.php
     *
     * @example bean/abstract-bean-list/sort-by-data.php 2
     *
     * Sorts the bean list by defined data keys,
     * sort order (SORT_ASC, SORT_DESC)
     * and sort flags (SORT_REGULAR, SORT_NUMERIC, SORT_STRING, SORT_LOCALE_STRING, SORT_NATURAL, SORT_FLAG_CASE)
     *
     * example:
     *
     *  //  sort first by "count" numerical and descending and then by "title" as string and ascending
     *  $this->sortByData("count", SORT_DESC, SORT_NUMERIC, "title", SORT_STRING);
     *
     * @see     array_multisort()
     */
    public function sortByData($key1, $order1 = SORT_ASC, $flags1 = SORT_REGULAR)
    {
        $args = array();
        $argFlagCount = 2;
        foreach (func_get_args() as $key => $arg) {
            if ($argFlagCount >= 2 && (!is_string($arg) || !strlen($arg))) {
                $this->throwError("Invalid key defined (has to be an none empty string)!");

                return false;
            }

            if (is_string($arg)) {
                $argFlagCount = 0;
                if (!array_reduce(
                    $this->hasData($arg), function ($flag, $val) {
                    if ($flag && !$val) {
                        $flag = false;
                    }

                    return $flag;
                }, true
                )) {
                    $this->throwError("Key '$arg' not found at any bean at the bean list!");

                    return false;
                }

                $args[] = $this->getData($arg);
            } else {
                $args[] = $arg;
                ++$argFlagCount;
            }
        }
        $args[] =& $this->arrBean;

        return call_user_func_array("array_multisort", $args);
    }


    /**
     * Sort bean list ascending by the defined bean data key.
     *
     * @param string $key a bean data key (NOTE: key has to be defined at any bean inside the bean list)
     * @param int $flags SORT_REGULAR, SORT_NUMERIC, SORT_STRING, SORT_LOCALE_STRING, SORT_NATURAL, SORT_FLAG_CASE
     *
     * @return bool
     * @throws BeanListException
     */
    public function sortAscendingByKey($key, $flags = SORT_REGULAR)
    {
        return $this->sortByData($key, SORT_ASC, $flags);
    }


    /**
     * Sort bean list descending by the defined bean data key.
     *
     * @param string $key a bean data key (NOTE: key has to be defined at any bean inside the bean list)
     * @param int $flags SORT_REGULAR, SORT_NUMERIC, SORT_STRING, SORT_LOCALE_STRING, SORT_NATURAL, SORT_FLAG_CASE
     *
     * @return bool
     * @throws BeanListException
     */
    public function sortDescendingByKey($key, $flags = SORT_REGULAR)
    {
        return $this->sortByData($key, SORT_DESC, $flags);
    }


    /**
     * @param string $name
     * @param mixed $value
     *
     * @return mixed
     */
    function setData($name, $value)
    {
        /**
         * @var $bean BeanInterface
         */
        foreach ($this as $key => $bean) {
            $bean->setData($name, $value);
        }

        return $this;
    }


    /**
     * @param string $name
     *
     * @return array
     */
    function getData($name)
    {
        $arrData = array();

        /**
         * @var $bean BeanInterface
         */
        foreach ($this as $key => $bean) {
            $arrData[] = $bean->getData($name);
        }

        return $arrData;
    }


    /**
     * @param string $name
     *
     * @return array
     */
    function getDataType($name)
    {
        $arrDataType = array();

        /**
         * @var $bean BeanInterface
         */
        foreach ($this as $key => $bean) {
            $arrDataType[] = $bean->getDataType($name);
        }

        return $arrDataType;
    }


    /**
     * @param string $name
     *
     * @return mixed
     */
    function hasData($name)
    {
        $arrData = array();

        /**
         * @var $bean BeanInterface
         */
        foreach ($this as $key => $bean) {
            $arrData[] = $bean->hasData($name);
        }

        return $arrData;
    }


    /**
     * @param string $name
     *
     * @return mixed
     */
    function removeData($name)
    {
        $arrData = array();

        /**
         * @var $bean BeanInterface
         */
        foreach ($this as $key => $bean) {
            $arrData[] = $bean->removeData($name);
        }

        return $arrData;
    }


    /**
     * @return mixed
     */
    function resetData()
    {
        /**
         * @var $bean BeanInterface
         */
        foreach ($this as $key => $bean) {
            $arrData[] = $bean->resetData();
        }

        return $this;
    }


    /**
     * @param array $arrData
     * @param array|null $arrNames
     * @param array|null $arrDataTypes
     *
     * @return $this
     */
    function setFromArray(array $arrData, array $arrNames = null, array $arrDataTypes = null)
    {
        foreach ($this as $key => $bean) {
            $bean->setFromArray($arrData, $arrNames, $arrDataTypes);
        }

        return $this;
    }


    /**
     * @return array[]
     */
    function toArray()
    {
        $arrData = array();

        /**
         * @var $bean BeanInterface
         */
        foreach ($this as $key => $bean) {
            $arrData[] = $bean->toArray();
        }

        return $arrData;
    }


    /**
     * @param array [array|\Niceshops\Library\Core\Bean\BeanInterface[]] $arrData
     * @param $beanClass
     *
     * @return static
     * @throws BeanListException
     */
    static public function createFromArray(array $arrData, $beanClass = BeanInterface::class)
    {
        if (!in_array(BeanInterface::class, class_implements($beanClass))) {
            throw new BeanListException("Class '$beanClass' doesn't implement the 'BeanInterface' interface!");
        }

        $beanList = new static();

        foreach ($arrData as $data) {
            if (!($data instanceof $beanClass)) {
                $beanList->addBean(call_user_func(array($beanClass, "createFromArray"), $data));
            } else {
                $beanList->addBean($data);
            }
        }

        $beanList->setBeanClasses(array($beanClass));

        return $beanList;
    }

    /**
     * @param string $dataName
     *
     * @return array
     * @throws BeanListException
     */
    public function countValues_for_DataName(string $dataName): array
    {
        $dataName = trim($dataName);
        if (strlen($dataName) === 0) {
            throw new BeanListException('Empty data key given');
        }

        return array_count_values(array_column(iterator_to_array($this->getIterator()), $dataName));
    }
}
