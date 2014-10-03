<?php
return function (UglyDi\UglyDi $di) {
    $driver = array (
  'driver' => 'Mysqli',
  'database' => 'zend_db_example',
  'username' => 'developer',
  'password' => 'developer-password',
);
    $platform = NULL;
    $queryResultPrototype = $di->get('Zend\Db\ResultSet\ResultSet');
    $profiler = NULL;
    return new Zend\Db\Adapter\Adapter($driver, $platform, $queryResultPrototype, $profiler);
};
