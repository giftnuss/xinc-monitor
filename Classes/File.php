<?php
/*
 * @copyright 2015 Xinc Development Team, https://github.com/xinc-develop/
 * @license Permission is hereby granted, free of charge, to any person
 *          obtaining a copy of this software and associated documentation
 *          files (the "Software"), to deal in the Software without restriction,
 *          including without limitation the rights to use, copy, modify, merge,
 *          publish, distribute, sublicense, and/or sell copies of the Software,
 *          and to permit persons to whom the Software is furnished to do so,
 *          subject to the following conditions:
 *          \\
 *          The above copyright notice and this permission notice shall be included
 *          in all copies or substantial portions of the Software.
 *          \\
 *          THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 *          OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *          FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *          AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *          LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *          OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *          SOFTWARE.
 */
namespace Xinc\Monitor;

class File
{
    protected $path;
    private $exists;
    private $mtime;
    protected $isChanged;

    public function __construct($path = null)
    {
        if($path !== null) {
            $this->setPath($path);
            $this->initialize();
        }
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        if($this->path === null) {
            $this->path = $path;
            return $this;
        }
        throw new Xinc\Monitor\Exception("Path attribute is not changeable.");
    }

    public function initialize()
    {
        $this->isChanged = false;
        if(file_exists($this->getPath()) === FALSE) {
            $this->exists = false;
            $this->mtime = null;
        }
        else {
            $stat = stat($this->getPath());
            if($stat === false) {
                throw new Xinc\Monitor\Exception(
                    "Problem stat '{$this->getPath()}'.");
            }
            $this->exists = true;
            $this->mtime = $stat['mtime'];
        }
    }

    /**
     * @returns boolean Is file changed
     */
    public function check()
    {
        if(!$this->exists()) {
            if(file_exists($this->getPath())) {
                $this->exists = true;
                $this->isChanged = true;
            }
            else {
                return false;
            }
        }
        $stat = stat($this->getPath());
        if($stat === false) {
            throw new Xinc\Monitor\Exception(
                "Problem stat '{$this->getPath()}'.");
        }
        if($this->mtime != $stat['mtime']) {
            $this->isChanged = true;
            $this->mtime = $stat['mtime'];
        }
        else {
            $this->isChanged = false;
        }
        return $this->isChanged;
    }

    public function exists()
    {
        return $this->exists;
    }

    public function modificationTime()
    {
        return $this->mtime;
    }

    public function isChanged()
    {
        return $this->isChanged;
    }
}
