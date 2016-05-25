<?php
/*
 * @author Sebastian Knapp
 * @version 2.5
 */


use Xinc\Monitor\File;

/**
 * Test Class for the Xinc Monitor File
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    public function testNotExists()
    {
        $notexists = new File('XXX-unknown.txt');
        $this->assertFalse($notexists->exists());
        $this->assertFalse($notexists->isChanged());
        $this->assertNull($notexists->modificationTime());
        $notexists->check();
        $this->assertFalse($notexists->exists());
        $this->assertFalse($notexists->isChanged());
        $this->assertNull($notexists->modificationTime());
    }

    public function testNotExistsAndTouched()
    {
        $notexists = new File(__DIR__ . '/data/1.txt');
        $this->assertFalse($notexists->exists(),"Ups, rm {$notexists->getPath()} and try again.");
        $this->assertFalse($notexists->isChanged());
        $this->assertNull($notexists->modificationTime());
        $this->assertTrue(touch($notexists->getPath()),'touch test file');
        $notexists->check();
        $this->assertTrue($notexists->exists());
        $this->assertTrue($notexists->isChanged());
        $this->assertNotNull($notexists->modificationTime());
        // cleanup
        $this->assertTrue(unlink($notexists->getPath()));
    }
}
