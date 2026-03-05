<?php
/* 
|--------------------------------------------------------------------------
| Console Engine Class														
|--------------------------------------------------------------------------
|
| Path: console/main.php
| Handles all cli/console requests
|
*/

namespace Console;
use Console\ConsoleHelpers;

class ConsoleEngine extends ConsoleHelpers
{
	
	/*
	|--------------------------------------------------------------------------
	| Command Line Arguments
	|--------------------------------------------------------------------------
	|
	| The command line arguments passed to the script is parsed and stored
	| in the following variables:
	|
	*/
	
	private $value;
	private $option;
	private $command;
	private $platform;
	private $fresh;

	/*
	|--------------------------------------------------------------------------
	| Application Directories
	|--------------------------------------------------------------------------
	|
	| The application directories are stored in the following variables:
	|
	*/

	private $builder_dir;
	private $root_config;
	private $build_config;
	private $package_config;
	
	/*
	|--------------------------------------------------------------------------
	| Console Engine Constructor
	|--------------------------------------------------------------------------
	|
	| The console engine constructor is responsible for parsing the command line
	| arguments and setting up the application directories.
	|
	*/

	public function __construct()
	{
		parent::__construct();

		$this->parseArguments();
		$this->setupDirectories();
	}

	/*
	|--------------------------------------------------------------------------
	| Console Engine Run
	|--------------------------------------------------------------------------
	|
	| The console engine run method is responsible for running the command
	| requested by the user.
	| @return void
	|
	*/

	public function run() : void {
		switch ($this->command):

			case 'init': $this->init(); break;
			case 'make': $this->make(); break;
			case 'serve': $this->serve(); break;
			case 'prepare': $this->prepare(); break;
			case 'version': $this->showVersion(); break;

			case 'build': $this->write('This command is under development'); break;
			default: $this->showHelp(); break;

		endswitch;
	}

	/*
	|--------------------------------------------------------------------------
	| Application initialization function
	|--------------------------------------------------------------------------
	|
	| The application initialization function is responsible for createing and
	| setting up the application directories and files along with their 
	| credentials.
	| @return void
	|
	*/

	public function init() : void {

		// Check for errors
		if (!$this->checkForErrors()) { exit(1); }
		
		$this->write(
			$this->color_green . 'PHPulse Notice:' . $this->color_reset,
			'  Initializing the application...',
			''
		);

		// Prepare the application configurations
		$this->startApplicationConfigurations();
		
	}

	/*
	|--------------------------------------------------------------------------
	| Prepare Application for Building
	|--------------------------------------------------------------------------
	|
	| The prepare application for building function is responsible for preparing
	| the application for building, this includes copying the application files, 
	| installing the dependencies and migrating the PHP binaries.
	| @return void
	|
	*/

	public function prepare() : void {

		// Check for errors
		if (!$this->checkForErrors()) { exit(1); }

		$this->write(
			$this->color_green . 'PHPulse Notice:' . $this->color_reset,
			'  Preparing the application for building...',
			''
		);

		// If fresh flag is set, clean the builder directory
		if ($this->fresh) {
			$this->write(
				$this->color_yellow . 'PHPulse Notice:' . $this->color_reset,
				'  Fresh flag detected, cleaning builder directory...',
				''
			);
			$this->cleanBuilderDirectory();
		}

		// Migrate the PHP binaries to the builder directory
		$this->migratePHPBinaries();

		// Compile the application files to builder directory
		$this->compileApplicationFiles();

		// Always overwrite config.json in builder directory
		$this->copyConfigToBuilder();

		$this->write(
			$this->color_green . 'PHPulse Success:' . $this->color_reset,
			'  Application prepared successfully!',
			''
		);

	}

	/*
	|--------------------------------------------------------------------------
	| Serve the Application
	|--------------------------------------------------------------------------
	|
	| The serve the application function is responsible for serving the
	| application on real time in the builder directory.
	| @return void
	|
	*/
	
	private function serve() : void {
		$this->write(
			$this->color_green . 'PHPulse Notice:' . $this->color_reset,
			'  Serving the application on real time...',
			''
		);

		// check for Errors
		if (!$this->checkForErrors()) { exit(1); }

		// compile the application files to builder directory
		$this->compileApplicationFiles();

		// Always overwrite config.json in builder directory
		$this->copyConfigToBuilder();

		// `npm start` in the builder directory
		$this->write(
			$this->color_green . 'PHPulse Notice:' . $this->color_reset,
			'  Serving the application Window...',
			''
		);

		exec('cd ' . $this->builder_dir . ' && npm start');

	}

	/*
	|--------------------------------------------------------------------------
	| Builder Function
	|--------------------------------------------------------------------------
	| The build function deals with the builder, handling over tasks like
	| dependency installation, update and soo forth
	| @return void
	|
	*/

	private function make() : void {
		$this->write(
			$this->color_green . 'PHPulse Notice:' . $this->color_reset,
			'  Preparing necessary Requirements for app building...',
			''
		);

		// check for Errors
		if (!$this->checkForErrors()) { exit(1); }

		// compile the application files to builder directory
		$this->compileApplicationFiles();

		// Always overwrite config.json in builder directory
		$this->copyConfigToBuilder();

		// Determine which npm script to run based on platform
		$buildScript = 'build';
		$platformLabel = 'current platform';
		
		if ($this->platform) {
			switch(strtolower($this->platform)) {
				case 'win':
				case 'windows':
					$buildScript = 'build:win';
					$platformLabel = 'Windows';
					break;
				case 'mac':
				case 'macos':
					$buildScript = 'build:mac';
					$platformLabel = 'macOS';
					break;
				case 'linux':
					$buildScript = 'build:linux';
					$platformLabel = 'Linux';
					break;
				case 'all':
					$buildScript = 'build:all';
					$platformLabel = 'all platforms';
					break;
				default:
					$this->write(
						$this->color_yellow . 'PHPulse Warning:' . $this->color_reset,
						'  Invalid platform: ' . $this->platform,
						'  Valid options: win, mac, linux, all',
						'  Defaulting to current platform',
						''
					);
			}
		}

		// `npm run build:*` in the builder directory
		$this->write(
			$this->color_green . 'PHPulse Notice:' . $this->color_reset,
			'  Building your application for ' . $platformLabel . '...',
			$this->color_yellow . '  This process may take awhile' . $this->color_reset,
			''
		);

		$response = exec('cd ' . $this->builder_dir . ' && npm run ' . $buildScript);

		$this->write(
			'  ' . $response,
			''
		);

	}

	/*
	|--------------------------------------------------------------------------
	| Prepare Application Configurations
	|--------------------------------------------------------------------------
	|
	| The prepare application configurations function is responsible for
	| preparing the application configurations.
	| @return void
	|
	*/

	private function startApplicationConfigurations() : void {
		// get the root config file and package config file contents
		$root_config = json_decode(file_get_contents($this->root_config));
		$package_config = json_decode(file_get_contents($this->package_config));


		// prompt the user for the application configurations
		$appName = $this->prompt('  Application Name: ', $root_config->productName);
		$root_config->appId = $this->prompt('  Application ID: ', $root_config->appId);
		$root_config->version = $this->prompt('  Application Version: ', $root_config->version);
		$root_config->description = $this->prompt('  Application Description: ', $root_config->description);
		$root_config->entry_point = $this->prompt('  Application Entry Point: ', $root_config->entry_point);
		$root_config->entry_file = $this->prompt('  Application Entry File: ', $root_config->entry_file);

		$root_config->name = str_replace(' ', '', strtolower($appName));	// replace spaces with nothing
		$root_config->productName = $appName;

		// rewrite the root config file and the build config file
		file_put_contents($this->root_config, json_encode($root_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		file_put_contents($this->build_config, json_encode($root_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

		// replace name, descritption, version, build.appId, build.productName
		$package_config->name = strtolower($appName);
		$package_config->description = $root_config->description;
		$package_config->version = $root_config->version;
		$package_config->build->appId = $root_config->appId;
		$package_config->build->productName = $appName;

		// rewrite the package config file
		file_put_contents($this->package_config, json_encode($package_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

	}

	/*
	|--------------------------------------------------------------------------
	| Migrate PHP binaries to the builder directory
	|--------------------------------------------------------------------------
	|
	| The migrate PHP binaries to the builder directory function is responsible
	| for migrating the PHP binaries to the builder directory.
	| If isWindows is true, then will copy the system PHP binaries to the
	| builder directory.
	| @return void
	|
	*/

	private function migratePHPBinaries() : void {
		if ($this->isWindows) {
			$this->write(
				$this->color_green . 'PHPulse Notice:' . $this->color_reset,
				'  Copying PHP binaries to the builder directory...',
			);

			// check if directory php exist, if not create in builder
			if (!is_dir($this->builder_dir . '/php')) { mkdir($this->builder_dir . '/php'); }

			$phpBinary = PHP_BINARY;
			$phpBinary = str_replace('\\', '/', $phpBinary);
			$phpBinary = explode('/', $phpBinary);
			
			// get the parent directory of the php binary
			$phpBinary = array_slice($phpBinary, 0, -1);
			$phpBinary = implode('/', $phpBinary);

			$this->write(
				'  Migrating PHP binaries',
				'    from: ' . $this->color_yellow . $phpBinary . $this->color_reset,
				'    to: ' . $this->color_blue . $this->builder_dir . '/php' . $this->color_reset,
				''
			);

			// copy the php binaries to the builder directory
			$this->makeCopy($phpBinary , $this->builder_dir . '/php');

		}
	}

	/*
	|--------------------------------------------------------------------------
	| Compile the application files to Builder Directory
	|--------------------------------------------------------------------------
	|
	| The compile the application files to builder directory function is
	| responsible for collecting and copying the application files to the
	| builder directory.
	| @return void
	|
	*/

	private function compileApplicationFiles() : void {
		$this->write(
			$this->color_green . 'PHPulse Notice:' . $this->color_reset,
			'  Compiling the application files to the builder directory...',
			''
		);

		// get the ignore list from the root config file (ignore.list)
		$root_config = json_decode(file_get_contents($this->root_config));
		$ignore_list = $root_config->ignore->list;

		// Add vendor to ignore list temporarily - we'll handle it separately
		$ignore_list_with_vendor = array_merge($ignore_list, ['vendor']);
		
		// copy the backend directory to the builder directory

		try {
			$backend_dir = getcwd() . '/backend';
			if (!is_dir($backend_dir)) {
				$this->write(
					$this->color_red . 'PHPulse Error:' . $this->color_reset,
					'  Terminating, Backend directory does not exist.',
					'  Expected: ' . $backend_dir,
					''
				); exit(1);
			}

			// Copy everything except vendor
			$this->makeCopy($backend_dir, $this->builder_dir . '/backend', $ignore_list_with_vendor);

			// Handle vendor directory intelligently
			$this->syncVendorDirectory($backend_dir);

		} catch (\Throwable $th) {
			$this->write(
				$this->color_red . 'PHPulse Error:' . $this->color_reset,
				'  Terminating, Failed to copy the application files to the builder directory.',
				'  ' . $th->getMessage(),
				''
			); exit(1);
		}
		
	}


	/*
	|--------------------------------------------------------------------------
	| Application Help function
	|--------------------------------------------------------------------------
	|
	| The application help function is responsible for displaying the help
	| information for the application.
	| @return void
	|
	*/

	private function showHelp() : void {
		$this->write(
			$this->color_green . 'PHPulse Pulsar Usage:' . $this->color_reset,
			'  command [options] [arguments]',
			'',
			$this->color_green . 'Available commands:' . $this->color_reset,
			'  help                 Displays help for a command',
			'  init                 Initialize the application',
			'  prepare              Prepare the application (update files)',
			'  make		        Build the application',
			'  serve                Serve the application on real time',
			'  version              Display the current version of the framework',
			'',
			$this->color_green . 'Options:' . $this->color_reset,
			'  --platform=win       Build for Windows',
			'  --platform=mac       Build for macOS',
			'  --platform=linux     Build for Linux',
			'  --platform=all       Build for all platforms',
			'  --fresh              Clean builder directory before preparing',
			'',
			$this->color_green . 'Examples:' . $this->color_reset,
			'  php pulsar prepare',
			'  php pulsar prepare --fresh',
			'  php pulsar make --platform=win',
			'  php pulsar make --platform=mac',
			'  php pulsar make --platform=all',
			''
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Application Version function
	|--------------------------------------------------------------------------
	|
	| The application version function is responsible for displaying the version
	| information for the application.
	| @return void
	|
	*/

	private function showVersion() : void {
		$this->write(
			$this->color_green . 'PHPulse Version:' . $this->color_reset,
			'  1.0.0',
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Parse Command Line Arguments
	|--------------------------------------------------------------------------
	|
	| The parse command line arguments function is responsible for parsing the
	| command line arguments passed to the script.
	| @return void
	|
	*/

	private function parseArguments() : void
	{
		// Skip the script name (usually the first argument)
		$arguments = array_slice($_SERVER['argv'], 1);

		$this->value = $arguments[1] ?? null;

		if (count($arguments) >= 1) {
			$arguments = explode(':', $arguments[0]);

			$this->command = $arguments[0];
			$this->option = $arguments[1] ?? null;
			
			// Parse platform from additional arguments
			$this->platform = null;
			$this->fresh = false;
			foreach ($_SERVER['argv'] as $arg) {
				if (strpos($arg, '--platform=') === 0) {
					$this->platform = substr($arg, 11);
				}
				if ($arg === '--fresh') {
					$this->fresh = true;
				}
			}
		} else {
			$this->showHelp();
			exit(1);
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Setup Application Directories
	|--------------------------------------------------------------------------
	|
	| The setup application directories function is responsible for setting up
	| the application directories.
	| @return void
	|
	*/

	private function setupDirectories() : void
	{
		$this->builder_dir = getcwd() . '/builder';
		$this->root_config = getcwd() . '/config.json';

		$this->build_config = $this->builder_dir . '/config.json';
		$this->package_config = $this->builder_dir . '/package.json';
	}

	/*
	|--------------------------------------------------------------------------
	| Builder Directory Existance & Permissions
	|--------------------------------------------------------------------------
	|
	| The builder directory existance & permissions function is responsible for
	| checking the existance and permissions of the builder directory.
	| @return bool
	|
	*/

	private function checkBuilderDirectory() : bool
	{
		if (!is_dir($this->builder_dir) and !is_writable($this->builder_dir)) {
			$this->write(
				$this->color_red . 'PHPulse Error:' . $this->color_reset,
				'  Terminating, The builder directory does not exist or is not writable.',
				''
			); return false;
		}

		return true;
	}

	/*
	|--------------------------------------------------------------------------
	| Root Config File Existance & Permissions
	|--------------------------------------------------------------------------
	|
	| The root config file existance & permissions function is responsible for
	| checking the existance and permissions of the root config file.
	| @return bool
	|
	*/

	private function checkConfigurationFiles() : bool
	{

		// Check if the root config file exists and is writable
		if (!is_file($this->root_config) or !is_writable($this->root_config)) {
			$this->write(
				$this->color_red . 'PHPulse Error:' . $this->color_reset,
				'  Terminating the root config file does not exists or is not writable.',
				'  root config file: ' . $this->root_config,
				''
			); return false;
		}

		// Check if the package config file exists and is writable
		if (!is_file($this->package_config) or !is_writable($this->package_config)) {
			$this->write(
				$this->color_red . 'PHPulse Error:' . $this->color_reset,
				'  Terminating the package config file does not exists or is not writable.',
				'  package config file: ' . $this->package_config,
				''
			); return false;
		}

		return true;
	}
	
	/*
	|--------------------------------------------------------------------------
	| Sync Vendor Directory
	|--------------------------------------------------------------------------
	|
	| Intelligently sync the vendor directory by checking composer.lock hash.
	| Only copy vendor if the hash has changed to save time.
	| @param string $backend_dir
	| @return void
	|
	*/

	private function syncVendorDirectory(string $backend_dir) : void
	{
		$source_vendor = $backend_dir . '/vendor';
		$builder_vendor = $this->builder_dir . '/backend/vendor';
		$composer_lock = $backend_dir . '/composer.lock';
		$hash_file = $this->builder_dir . '/.vendor_hash';

		// Check if vendor directory exists in source
		if (!is_dir($source_vendor)) {
			$this->write(
				$this->color_yellow . 'PHPulse Notice:' . $this->color_reset,
				'  No vendor directory found in backend, skipping vendor sync.',
				''
			);
			return;
		}

		// Check if composer.lock exists
		if (!is_file($composer_lock)) {
			$this->write(
				$this->color_yellow . 'PHPulse Warning:' . $this->color_reset,
				'  No composer.lock found, copying vendor directory...',
				''
			);
			$this->makeCopy($source_vendor, $builder_vendor);
			return;
		}

		// Calculate hash of composer.lock
		$current_hash = md5_file($composer_lock);
		$stored_hash = file_exists($hash_file) ? trim(file_get_contents($hash_file)) : null;

		// Check if vendor needs updating
		if ($current_hash === $stored_hash && is_dir($builder_vendor)) {
			$this->write(
				$this->color_green . 'PHPulse Notice:' . $this->color_reset,
				'  Vendor directory is up to date (composer.lock unchanged), skipping copy.',
				''
			);
			return;
		}

		// Hash differs or builder vendor doesn't exist - need to update
		$this->write(
			$this->color_green . 'PHPulse Notice:' . $this->color_reset,
			'  Composer.lock has changed, updating vendor directory...',
			$this->color_yellow . '  This may take a moment...' . $this->color_reset,
			''
		);

		// Remove old vendor directory if it exists
		if (is_dir($builder_vendor)) {
			$this->fs->remove($builder_vendor);
		}

		// Copy new vendor directory
		$this->makeCopy($source_vendor, $builder_vendor);

		// Store the new hash
		file_put_contents($hash_file, $current_hash);

		$this->write(
			$this->color_green . 'PHPulse Notice:' . $this->color_reset,
			'  Vendor directory updated successfully.',
			''
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Copy Config to Builder
	|--------------------------------------------------------------------------
	|
	| The copy config to builder function is responsible for copying the root
	| config.json file to the builder directory, overwriting any existing file.
	| @return void
	|
	*/

	private function copyConfigToBuilder() : void
	{
		$this->write(
			$this->color_green . 'PHPulse Notice:' . $this->color_reset,
			'  Copying config.json to builder directory...',
			''
		);

		copy($this->root_config, $this->build_config);
	}

	/*
	|--------------------------------------------------------------------------
	| Clean Builder Directory
	|--------------------------------------------------------------------------
	|
	| The clean builder directory function is responsible for removing all
	| contents in the builder directory except core files needed for building.
	| @return void
	|
	*/

	private function cleanBuilderDirectory() : void
	{
		$exclude = [
			'node_modules',
			'package.json',
			'package-lock.json',
			'main.js',
			'assets'
		];

		$items = scandir($this->builder_dir);
		
		foreach ($items as $item) {
			if ($item === '.' || $item === '..' || in_array($item, $exclude)) {
				continue;
			}

			$path = $this->builder_dir . '/' . $item;
			
			if (is_dir($path)) {
				$this->fs->remove($path);
			} elseif (is_file($path)) {
				unlink($path);
			}
		}

		$this->write(
			$this->color_green . 'PHPulse Notice:' . $this->color_reset,
			'  Builder directory cleaned.',
			''
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Check for Errors
	|--------------------------------------------------------------------------
	|
	| The check for errors function is responsible for checking if there are any
	| unmet requirements or errors in the application.
	| @return bool
	|
	*/

	private function checkForErrors() : bool
	{
		if (!$this->checkBuilderDirectory()) { return false; }
		if (!$this->checkConfigurationFiles()) { return false; }

		// windows os, php not exist or empty
		if($this->isWindows) {
			if (!is_dir($this->builder_dir . '/php') or empty($this->builder_dir . '/php')) {
				$this->write(
					$this->color_yellow . 'PHPulse Warning:' . $this->color_reset,
					'  Seems you have not migrated the PHP binaries to the builder directory.',
					'  Migrating Now',
					''
				);

				$this->migratePHPBinaries();
			}
		}

		return true;
	}
}