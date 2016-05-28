<?php
/*
 * @author Sebastian Knapp
 * @version 2.5
 */

use Xinc\Monitor\Dir;

/**
 * Test Class for the Xinc Monitor File
 */
class DirTest extends \PHPUnit_Framework_TestCase
{
    public function testUnknownPathException()
    {
        $this->setExpectedException('\Xinc\Monitor\Exception');
        $low = new Dir(__DIR__ . '/data/XXX-unknown');
    }

    public function testSetPathException()
    {
        $this->setExpectedException('\Xinc\Monitor\Exception');
        $low = new Dir(__DIR__ . '/data/');
        $low->setPath(__DIR__ . '/data/soup.txt');
    }


}
