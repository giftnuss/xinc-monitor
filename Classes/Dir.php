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


use Xinc\Monitor\Directory\FilterIterator as Filter;
use Xinc\Monitor\MonitoredInterface as Monitored;

class Dir implements Monitored
{
    protected $path;
    protected $ls;
    protected $ignore;
    protected $isChanged;

    public function __construct($path = null)
    {
        $this->ls = array();
        $this->ignore = array();
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
            if(is_dir($path)) {
                $this->path = $path;
                return $this;
            }
            throw new \Xinc\Monitor\Exception("Directory path '$path' not found.");
        }
        throw new \Xinc\Monitor\Exception("Path attribute is not changeable.");
    }

    public function getIterator()
    {
        $i = new Filter(new \DirectoryIterator($this->getPath()));
        $i->rewind();
        return $i;
    }

    public function initialize()
    {
        $this->ls = array();
        $this->isChanged = false;
        $iter = $this->getIterator();

        while($iter->valid()) {
            $this->setupEntry($iter);
            $iter->next();
        }
    }

    protected function setupEntry($iter)
    {
        if($iter->isFile()) {
            $this->ls[$iter->getPathname()] = new File($iter->getPathname());
        }
        elseif($iter->isDir()) {
            $this->ls[$iter->getPathname()] = new Dir($iter->getPathname());
        }
    }

    /**
     * @returns boolean Is directory changed
     */
    public function check()
    {
        $this->isChanged = false;
        $check = array();
        $iter = $this->getIterator();
        while($iter->valid()) {
            $check[] = $iter->getPathname();
            if(isset($this->ls[$iter->getPathname()])) {
                if($this->ls[$iter->getPathname()]->check()) {
                    $this->isChanged = true;
                }
            }
            else {
                $this->setupEntry($iter);
                $this->isChanged = true;
            }
            $iter->next();
        }
        $deleted = array_diff(array_keys($this->ls),$check);
        if(count($deleted) > 0) {
            $this->isChanged = true;
            foreach($deleted as $entry) {
                unset($this->ls[$entry]);
            }
        }
        return $this->isChanged;
    }

    public function isChanged()
    {
        return $this->isChanged;
    }
}
