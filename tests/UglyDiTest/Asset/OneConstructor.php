<?php
/**
 * Created by Gary Hockin.
 * Date: 18/09/2014
 * @GeeH
 */

namespace UglyDiTest\Asset;


class OneConstructor
{
    public $noConstructor;

    function __construct(NoConstructor $noConstructor)
    {
        $this->noConstructor = $noConstructor;
    }

} 