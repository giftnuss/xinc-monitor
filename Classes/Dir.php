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


use Xinc\Monitor\Directory\RecursiveIterator as Filter;
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
        $i = new Filter(new \RecursiveDirectoryIterator($this->getPath()));
        $i = new \RecursiveIteratorIterator( $i );
        // PHP5.4 oddity ??? - foreach works without, but valid is false?
        $i->rewind();
        return $i;
    }

    public function initialize()
    {
        $this->ls = array();
        $this->isChanged = false;
        $iter = $this->getIterator();

        while($iter->valid()) {
            if($iter->isFile()) {
                $this->ls[$iter->getSubPathname()] = $iter->current()->getMTime();
            }
            $iter->next();
        }
    }

    /**
     * @returns boolean Is file changed
     */
    public function check()
    {
        $check = array();
        $iter = $this->getIterator();
        while($iter->valid()) {
            if($iter->isFile()) {
                $check[$iter->getSubPathname()] = $iter->current()->getMTime();
            }
            $iter->next();
        }
        $new = array_diff_assoc($check,$this->ls);
        $changed = array_diff_assoc($this->ls,$check);

        if(count($new) > 0 || count($changed) > 0) {
            $this->isChanged = true;
        }
        else {
            $this->isChanged = false;
        }
        return $this->isChanged;
    }

    public function isChanged()
    {
        return $this->isChanged;
    }
}
