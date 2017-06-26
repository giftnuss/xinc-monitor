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
    protected $dirs = array();

    protected function setUp()
    {
        $this->dirs = array();
    }

    protected function tearDown()
    {
        foreach($this->dirs as $dir) {
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir),
                RecursiveIteratorIterator::CHILD_FIRST);
            foreach($it as $item) {
                if($item->getFilename() !== '.' && $item->getFilename() !== '..') {
                    if($item->isDir()) {
                        rmdir($item->getPathname());
                    }
                    else {
                        unlink($item->getPathname());
                    }
                }
            }
            if(rmdir($dir) == FALSE) {
                throw new \Exception("Removing testdir $dir failed.");
            }
        }
    }

    protected function makeTestdir()
    {
        $name = 'test-' . substr(md5(microtime()),0,5);
        $dir = __DIR__ . "/data/$name";
        if(mkdir($dir)===FALSE) {
            throw new \Exception("Making $dir failed");
        }
        $this->dirs[] = $dir;
        return $dir;
    }

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

    public function testNotDirException()
    {
        $this->setExpectedException('\Xinc\Monitor\Exception');
        $low = new Dir();
        $low->setPath(__DIR__ . '/data/soup.txt');
    }

    public function testSetPath()
    {
        $low = new Dir();
        $low->setPath(__DIR__ . '/data');
        $this->assertSame(__DIR__.'/data',$low->getPath());
    }

    public function testStartUnchanged()
    {
        $testdir = $this->makeTestdir();
        $low = new Dir($testdir);
        $this->assertFalse($low->isChanged());
    }


    public function testCreateFile()
    {
        $testdir = $this->makeTestdir();
        $low = new Dir($testdir);
        touch($testdir . '/one.txt');
        $low->check();
        $this->assertTrue($low->isChanged());
    }

    public function testChangeFile()
    {
        $testdir = $this->makeTestdir();
        $testfile = $testdir . '/one.txt';
        touch($testfile);
        $low = new Dir($testdir);
        file_put_contents($testfile,'bla');
        clearstatcache();
        $low->check();
        $this->assertTrue($low->isChanged());
    }

    public function testDeleteFile()
    {
        $testdir = $this->makeTestdir();
        $testfile = $testdir . '/one.txt';
        touch($testfile);
        $low = new Dir($testdir);
        unlink($testfile);
        $low->check();
        $this->assertTrue($low->isChanged());
    }
    
    public function testRecursiveDir()
    {
		$del = __DIR__ . '/data/recursive/two/del.txt';
		touch($del);
		$low = new Dir(__DIR__.'/data/recursive');
		$update = $low->getPath() . '/two/bla2.txt';
		touch($update);
		clearstatcache();
		$low->check();
		$this->assertTrue($low->isChanged(),"touch existing file");
		
		$low->check();
		$this->assertFalse($low->isChanged());
		
		$update2 = $low->getPath() . '/one/new.txt';
		$this->assertTrue(touch($update2),"touch new file");
		clearstatcache();
		$low->check();
		$this->assertTrue($low->isChanged(),"create new file");
		
		$low->check();
		$this->assertFalse($low->isChanged());
		
		$this->assertTrue(unlink($del),"delete file");
		clearstatcache();
		$low->check();
		$this->assertTrue($low->isChanged(),"file deleted");
		
		$low->check();
		$this->assertFalse($low->isChanged());
	}
}
