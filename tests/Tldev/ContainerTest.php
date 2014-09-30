<?php

namespace Tldev;

class ContainerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \Tldev\Exception\InvalidParameter
     */
    public function testThrowsExceptionForNonStringKey()
    {
        $container = new Container();
        $container->set(1, 2);
    }

    /**
     * @expectedException \Tldev\Exception\InvalidParameter
     */
    public function testThrowsExceptionForEmptyStringKey()
    {
        $container = new Container();
        $container->set('', 1);
    }

    /**
     * @expectedException \Tldev\Exception\RuntimeException
     */
    public function testThrowsExceptionForCircularDependency()
    {
        $container = new Container();
        $container->set('factory', function () use ($container) {
            return $container->get('factory');
        });
        $container->get('factory');
    }

    /**
     * @expectedException \Tldev\Exception\DoesNotExist
     */
    public function testThrowsExceptionForNonExistentKey()
    {
        $container = new Container();
        $container->get('test');
    }

    public function testSetAndGetGeneric()
    {
        $container = new Container();
        $container->set('a', 1);
        $container->set('b', new \DateTime('now'));
        $container->set('c', 12.23);
        $container->set('d', 'string');
        $container->set('e', false);
        $container->set('f', null);

        $this->assertEquals(1, $container->get('a'));
        $this->assertInstanceOf('DateTime', $container->get('b'));
        $this->assertEquals(12.23, $container->get('c'));
        $this->assertEquals('string', $container->get('d'));
        $this->assertFalse($container->get('e'));
        $this->assertNull($container->get('f'));
    }

    public function testSetAndGetFactory()
    {
        $container = new Container();
        $container->set('factory', function () {
            return 2 * 3;
        });

        $this->assertEquals(6, $container->get('factory'));
    }

    public function testSetAndGetFactoryWithParams()
    {
        $container = new Container();
        $container->set('subtract', function ($num1, $num2) {
            return $num1 - $num2;
        });

        $this->assertEquals(1, $container->get('subtract', array(2, 1)));
        $this->assertEquals(-5, $container->get('subtract', array(0, 5)));
    }


}