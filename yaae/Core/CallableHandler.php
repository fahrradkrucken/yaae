<?php

namespace YAAE\Core;


class CallableHandler
{
    public static function tryHandleCallableWithArguments($callable, $arguments)
    {
        if (is_callable($callable)) {
            return call_user_func_array($callable, $arguments);
        }
        if (is_string($callable)) {
            if (strpos($callable, '@') !== false) {
                $callableParts = explode('@', $callable);
                $callableClassName = $callableParts[0];
                $callableClassMethodName = $callableParts[1];
                if (class_exists($callableClassName)) {
                    $callableClass = new $callableClassName();
                    if (method_exists($callableClass, $callableClassMethodName)) {
                        return call_user_func_array([$callableClass, $callableClassMethodName], $arguments);
                    }
                }
            } elseif (class_exists($callable)) {
                $callableClass = new $callable();
                return call_user_func_array($callable, $arguments);
            }
        }
        return false;
    }
}