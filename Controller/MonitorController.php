<?php

namespace MBence\LivePHPBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class MonitorController extends ContainerAware
{
    /** list of directories to check for changes relative to the app root dir (/app) */
    protected $dirs = array('.', '../web', '../src');
    /** ignore these files or directories */
    protected $ignore = array('logs', 'cache');
    /** default time limit in seconds */
    protected $timeLimit = 125;
    /** the time to die */
    protected $deadLine;
    /** enable / disable logging */
    protected $logging = false;
    
    protected $appDir;
    protected $response;

    public function indexAction($start_time)
    {
        $start = (int) ($start_time / 1000);
        
        $this->response = new Response();
        $this->appDir = $this->container->get('kernel')->getRootDir() . '/';

        $this->getConfig();
        $this->setHeaders();
        $this->setDeadLine();                
        $this->main($start);

        return $this->response;
    }
   
    /**
     * Read the configuration from config.yml
     */
    protected function getConfig()
    {
        if ($this->container->hasParameter('livephp.dirs')) {
            $dirs = $this->container->getParameter('livephp.dirs');
            if (!empty($dirs)) {
                $this->dirs = $this->container->getParameter('livephp.dirs');
            }
        }
        if ($this->container->hasParameter('livephp.ignore')) {
            $ignore = $this->container->getParameter('livephp.ignore');
            if (!empty($ignore)) {
                $this->ignore = $ignore;
            }
        }
        if ($this->container->hasParameter('livephp.timelimit')) {
            $timeLimit = $this->container->getParameter('livephp.timelimit');
            if (!empty($timeLimit)) {
                $this->timeLimit = $timeLimit;
            }
        }
    }

    /**
     * Set the no-cache headers
     */
    protected function setHeaders()
    {
        $this->response->headers->set('Cache-Control', 'no-cache, must-revalidate');
        $this->response->headers->set('Expires', '-1');
    }
        
    /**
     * Sets the time limit if possible
     */
    protected function setDeadLine()
    {
        // try to set the time limit
        set_time_limit($this->timeLimit); 
        // lets check what the actual limit is
        $limit = ini_get('max_execution_time');

        if (empty($limit) || $limit < 1) {
            // in case of unsuccesful ini_get, (or unlimited execution), we fall back to the default 30 sec
            $limit = 30;
        }
        // we stop the loop 5 sec befor the time limit, just for sure
        $this->deadLine = time() + $limit - 5;
    }

    /**
     * Main function
     * @param int $start start date in unix timestamp
     */
    protected function main($start)
    {
        // clear file state cache
        clearstatcache();
        // long polling loop
        do {
            // look for the changes every second until the execution time allows it.
            foreach ($this->dirs as $root) {
                if ($this->checkDir(realpath($this->appDir . $root), $start)) {
                    // if we find modified files in any of the directories, we can skip the rest
                    $this->response->setContent('1');
                    
                    return true;
                }
            }

            sleep(1);
        }
        while (time() < $this->deadLine);
    }

    /**
     * A fast (and non-recursive) function to check for modified files in a directory structure
     *
     * @param string $root directory path
     * @param int $start (unix timestamp) to find newer files of
     * @return bool true if modified file found, false otherwise
     */
    protected function checkDir($root, $start)
    {
        $stack[] = $root;
        // walk through the stack
        while (!empty($stack)) {
            $dir = array_shift($stack);
            $files = glob($dir . '/*');
            // make sure that we have an array (glob can return false in some cases)
            if (!empty($files) && is_array($files)) {
                foreach ($files as $file) {
                    if (empty($this->ignore) || !in_array(basename($file), $this->ignore)) {
                        if (is_dir($file)) {
                            // we add the directories to the stack to check them later
                            $stack[] = $file;
                        }
                        elseif (is_file($file)) {
                            // and check the modification times of the files
                            $mtime = filemtime($file);
                            if ($mtime && $start < $mtime) {
                                if ($this->logging) {
                                    $logger = $this->container->get('logger');
                                    $logger->info('LivePHP: file change detected: ' . $file);
                                }
                                // return true at the first positive match
                                return true;
                            }
                        }
                    }
                } // end foreach
            }
        } // end while

        return false;
    }

} // end MonitorController