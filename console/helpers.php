<?php

/*
|--------------------------------------------------------------------------
| Helpers for Console Application
|--------------------------------------------------------------------------
| Path: console/helpers.php
| Here we can define any helper functions we need for our console
| application.
|
*/

namespace Console;
use Symfony\Component\Filesystem\Filesystem;

class ConsoleHelpers
{ 
    
    public $fs;

    /*
    |--------------------------------------------------------------------------
    | PreDefined Constants Variables
    |--------------------------------------------------------------------------
    |
    | Here we can define any global variables we need for our console
    | application.
    |
    */

    public $OS = PHP_OS;
    public $isMac = false;
    public $isLinux = false;
    public $isWindows = false;
    
	/*
	|--------------------------------------------------------------------------
	| Console Colors codes
	|--------------------------------------------------------------------------
	|
	| The console colors are stored in the following variables:
	|--------------------------------------------------------------------------
	*/

	public $color_red = "\033[31m";
	public $color_blue = "\033[34m";
	public $color_green = "\033[32m";
	public $color_yellow = "\033[33m";
	public $color_reset = "\033[0m";

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    | Performs any initialization needed for the class
    |
    */

    public function __construct()
    {
        $this->osCheck();
        $this->fs = new Filesystem();
    }

    /*
    |--------------------------------------------------------------------------
    | Write to Console
    |--------------------------------------------------------------------------
    | Takes in a series of arguments and writes them to the console
    | @param array $messages
    | @return void
    |
    */

    public function write(...$messages) : void
    {
        foreach ($messages as $msg) {
            echo $msg.PHP_EOL;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Input
    |--------------------------------------------------------------------------
    | Prompts the user for input and returns the input
    | @example: What is your name? [John Doe]
    | @param string $message, string $default
    | @return string
    |
    */

    public function prompt(string $message, string $default = '') : string
    {
        echo $message . ' [' . $default . '] : ';
        $input = trim(fgets(STDIN));
        return $input ?: $default;
    }

    /*
    |--------------------------------------------------------------------------
    | OS Check
    |--------------------------------------------------------------------------
    | Checks the OS and sets the appropriate variables
    | @return void
    |
    */

    public function osCheck() : void
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->isWindows = true;
        } elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'DAR') {
            $this->isMac = true;
        } else {
            $this->isLinux = true;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Copy files and or directories
    |--------------------------------------------------------------------------
    | Copies files and or directories from one location to another
    | @param string $source, string $destination, array|objects ignore
    | @return void
    |
    */

    public function makeCopy(string $source, string $destination, $ignore=null) : void
    {
        if($ignore == null):
            $this->fs->mirror($source, $destination);

            else:

                $directory = $source;
                $source = opendir($source);
                
                // make destination directory if it doesn't exis
                if(!$this->fs->exists($destination)) { $this->fs->mkdir($destination); }
                
                while(false !== ($file = readdir($source))) {
                    if($file != '.' && $file != '..' && !in_array($file, $ignore)) {
                        
                        // check if file or directory
                        if(is_dir($directory . '/' . $file)) {
                            $this->makeCopy($directory . '/' . $file, $destination . '/' . $file, $ignore);
                        } else {
                            $this->fs->copy($directory . '/' . $file, $destination . '/' . $file);
                        }

                    }
                }
                
        endif;
    }


}