<?php declare(strict_types=1);

namespace Xentral\LaravelTesting;

class Utils
{
    /**
     * @param  string<class-string>  $className
     * @param  string<class-string>  $attributeName
     */
    public static function getAttribute(string $className, string $attributeName): mixed
    {
        $ref = new \ReflectionClass($className);
        foreach ($ref->getAttributes($attributeName) as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }
}
