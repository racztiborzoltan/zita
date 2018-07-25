<?php
declare(strict_types=1);

namespace Zita\Tests;

use PHPUnit\Framework\TestCase;
use Zita\XsltPhpFunctionContainer;

final class XsltPhpFunctionContainerTest extends TestCase
{

    public function setup()
    {
        $container = new \League\Container\Container();

        $container->add('test_callback_1', function(){
            return 'test_callback_return_1';
        });

        $container->add('test_value_1', 'test_value_1');

        $container->add('test_object_1', function(){
            return new class() {
                public function test_method() {
                    return 'test_object_1__test_method_return';
                }
                public function test_method_with_arguments_1() {
                    return 'test_object_1__test_method_with_arguments_1_return' . (empty(func_get_args()) ? '' : '__' . '__' . implode(',', func_get_args())) ;
                }
            };
        });

        $container->add('test_object_with_invoke_1', function(){
            return new class() {
                public function test_method() {
                    return 'test_object_with_invoke_1__test_method_return';
                }
                public function __invoke() {
                    return 'test_object_with_invoke_1_return' . (empty(func_get_args()) ? '' : '__' . '__' . implode(',', func_get_args())) ;
                }
            };
        });

        XsltPhpFunctionContainer::setContainer($container);
    }

    public function testValidCalls()
    {
        $this->assertEquals('test_callback_return_1', XsltPhpFunctionContainer::test_callback_1());
        $this->assertEquals('test_value_1', XsltPhpFunctionContainer::test_value_1());
        $this->assertEquals('test_object_1__test_method_return', XsltPhpFunctionContainer::test_object_1('test_method'));
        $this->assertEquals('test_object_1__test_method_with_arguments_1_return', XsltPhpFunctionContainer::test_object_1('test_method_with_arguments_1'));
        $this->assertEquals('test_object_1__test_method_with_arguments_1_return____arg_1,arg_2', XsltPhpFunctionContainer::test_object_1('test_method_with_arguments_1', 'arg_1', 'arg_2'));

        // call object with __invoke() method:
        $this->assertEquals('test_object_with_invoke_1_return', XsltPhpFunctionContainer::test_object_with_invoke_1());
        $this->assertEquals('test_object_with_invoke_1_return____arg_1,arg_2', XsltPhpFunctionContainer::test_object_with_invoke_1('arg_1', 'arg_2'));
    }

    public function testInvalidCall()
    {
        // set throwable handler:
        XsltPhpFunctionContainer::setThrowableHandler(function(\Throwable $exception){
            if ($exception instanceof \Psr\Container\ContainerExceptionInterface) {
                return 'default_value_container';
            }
        });
        $this->assertEquals('default_value_container', XsltPhpFunctionContainer::INVALID_NAME());
    }

    public function testInvaidMethodCall()
    {
        // set throwable handler:
        XsltPhpFunctionContainer::setThrowableHandler(function(\Throwable $exception){
            return 'default_value';
        });
        $this->assertEquals('default_value', XsltPhpFunctionContainer::test_object_1('INVALID_METHOD'));
    }

    public function testInvalidCallWithEmptyThrowableHandler()
    {
        // set empty callback, that return null always:
        XsltPhpFunctionContainer::setThrowableHandler(function(){});
        $this->assertNull(XsltPhpFunctionContainer::INVALID_NAME());
    }

    public function testInvalidCallExpectedContainerExceptionInterface()
    {
        // remove throwable handler:
        XsltPhpFunctionContainer::setThrowableHandler(null);

        $this->expectException(\InvalidArgumentException::class);
        XsltPhpFunctionContainer::INVALID_NAME();
    }
}
