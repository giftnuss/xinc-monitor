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
    protected $files;

    protected function setUp()
    {
        $this->files = array();
    }

    protected function tearDown()
    {
        foreach($this->files as $file) {
            if(unlink($file) === FALSE) {
                throw new \Exception("Failure removing temporary file $file.");
            }
        }
    }

    protected function getTempfile()
    {
        $tempfile = tempnam(__DIR__ . '/data','tempfile-');
        $this->files[] = $tempfile;
        return $tempfile;
    }

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

    public function testSetPathException()
    {
        $this->setExpectedException('\Xinc\Monitor\Exception');
        $low = new File(__DIR__ . '/data/bla.txt');
        $low->setPath(__DIR__ . '/data/soup.txt');
    }

    public function testSetPathFileExists()
    {
        $low = new File();
        $low->setPath(__DIR__ . '/data/bla.txt');
        $this->assertEquals(__DIR__ . '/data/bla.txt',$low->getPath());
        $this->assertNull($low->exists());
        $this->assertNull($low->isChanged());
        $this->assertNull($low->modificationTime());
        $low->initialize();
        $this->assertTrue($low->exists());
        $this->assertFalse($low->isChanged());
        $this->assertNotNull($low->modificationTime());
    }

    public function testSetPathFileNotExists()
    {
        $low = new File();
        $low->setPath(__DIR__ . '/data/XXX-unknown.txt');
        $this->assertEquals(__DIR__ . '/data/XXX-unknown.txt',$low->getPath());
        $this->assertNull($low->exists());
        $this->assertNull($low->isChanged());
        $this->assertNull($low->modificationTime());
        $low->initialize();
        $this->assertFalse($low->exists());
        $this->assertFalse($low->isChanged());
        $this->assertNull($low->modificationTime());
    }

    public function testCheckNotChanged()
    {
        $low = new File(__DIR__ . '/data/bla.txt');
        $this->assertTrue($low->exists());
        $this->assertFalse($low->isChanged());
        $this->assertNotNull($low->modificationTime());
        clearstatcache();
        $low->check();
        $this->assertFalse($low->isChanged());
    }

    public function testCheckChanged()
    {
        $low = new File($this->getTempfile());
        $this->assertTrue($low->exists());
        $this->assertFalse($low->isChanged());
        $this->assertNotNull($low->modificationTime());
        file_put_contents($low->getPath(),'bla');
        clearstatcache();
        $low->check();
        $this->assertTrue($low->isChanged());

    }
}
