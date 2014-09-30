<?php


namespace Tldev;


use Tldev\Exception\RuntimeException;
use Tldev\Exception\DoesNotExist;
use Tldev\Exception\InvalidParameter;

class Container
{

    const TYPE_FACTORY = 1;
    const TYPE_GENERIC = 2;

    protected $type = array();

    /**
     * @var callable[]
     */
    protected $factory = array();

    /**
     * @var mixed $generic
     */
    protected $generic = array();

    /**
     * @var string[]
     */
    protected $fetching_key = array();

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->validateKey($key);

        if (!is_string($value) && is_callable($value)) {
            $this->setFactory($key, $value);
        } else {
            $this->setGeneric($key, $value);
        }
    }

    /**
     * @param $key
     * @param array|null $arguments
     * @throws Exception\DoesNotExist
     * @throws Exception\RuntimeException
     * @return mixed
     */
    public function get($key, array $arguments = null)
    {
        $this->validateKey($key);

        if (!$this->keyExists($key)) {
            throw new DoesNotExist(sprintf('Key:%s does not exist', $key));
        }

        if (array_key_exists($key, $this->fetching_key)) {
            throw new RuntimeException(sprintf('Infinite recursion detected for key %s', $key));
        }

        $this->fetching_key[$key] = true;

        switch ($this->type[$key]) {
            case self::TYPE_FACTORY:
                if (!isset($arguments)) {
                    $arguments = array();
                }
                $return = $this->getFactory($key, $arguments);
                break;
            default:
                $return = $this->getGeneric($key);
                break;
        }

        unset($this->fetching_key[$key]);
        return $return;
    }

    /**
     * @param $key
     * @param callable $callable
     */
    protected function setFactory($key, Callable $callable)
    {
        $this->factory[$key] = function ($arguments) use ($callable) {
            return call_user_func_array($callable, $arguments);
        };
        $this->type[$key] = self::TYPE_FACTORY;
    }

    /**
     * @param string $key
     * @param array $arguments
     * @return mixed
     */
    protected function getFactory($key, array $arguments = null)
    {
        return $this->factory[$key]($arguments);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function setGeneric($key, $value)
    {
        $this->generic[$key] = $value;
        $this->type[$key] = self::TYPE_GENERIC;
    }

    protected function getGeneric($key)
    {
        return $this->generic[$key];
    }

    /**
     * @param $key
     * @return bool
     */
    protected function keyExists($key)
    {
        return array_key_exists($key, $this->factory) || array_key_exists($key, $this->generic);
    }

    /**
     * @param string $key
     * @throws Exception\InvalidParameter
     */
    protected function validateKey($key)
    {
        if (!is_string($key)) {
            throw new InvalidParameter(sprintf('Key must be as string, %s passed.', gettype($key)));
        }

        if ($key === '') {
            throw new InvalidParameter('Empty string is an invalid key');
        }
    }
}