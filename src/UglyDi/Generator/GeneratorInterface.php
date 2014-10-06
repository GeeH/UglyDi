<?php
/**
 * Created by Gary Hockin.
 * Date: 29/09/2014
 * @GeeH
 */
namespace UglyDi\Generator;

interface GeneratorInterface
{
    /**
     * @param $className  Name of class to generate interface for
     * @param \ReflectionParameter[] $parameters  Reflection parameters needed to create instance of class
     * @param array $userArguments  Configuration arguments needed to fulfill dependencies
     * @return mixed
     */
    public function generateFactory($className, array $parameters, array $userArguments);

    /**
     * @param $className
     * @param $userArguments
     * @return mixed
     */
    public function exists($className, $userArguments);

    /**
     * @param $className
     * @param $userArguments
     * @return mixed
     */
    public function getFileName($className, $userArguments);


}