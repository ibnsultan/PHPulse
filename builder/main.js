const { app, BrowserWindow } = require('electron');
const { spawn } = require('child_process');
const path = require('path');
const os = require('os');
const net = require('net');

// get config.json
const appConfig = require( path.join(__dirname, 'config.json') );

let phpServerProcess;
let serverPort;

const isPortAvailable = (port) => {
	return new Promise((resolve) => {
		const server = net.createServer();
		
		server.once('error', (err) => {
			if (err.code === 'EADDRINUSE') {
				resolve(false);
			} else {
				resolve(false);
			}
		});
		
		server.once('listening', () => {
			server.close();
			resolve(true);
		});
		
		server.listen(port, 'localhost');
	});
};

const findAvailablePort = async (startPort = 48000) => {
	let port = startPort;
	while (port < 49000) {
		if (await isPortAvailable(port)) {
			return port;
		}
		port++;
	}
	throw new Error('No available ports found in range 48000-49000');
};

const startPHPServer = async () => {
	
	const phpScriptPath = path.join(__dirname, appConfig.entry_point);
	
	if( os.platform() === 'win32' ) {
		phpBinPath = path.join(__dirname, 'php', 'php.exe');
	} else {

		
		/*
		|------------------------------------------------------------------
		| TODO: Add support for other platforms
		|------------------------------------------------------------------
		| Check for other platforms php bin, if not found use the 
		| installation command to install php on the given platform
		|
		*/

		// in the meantime, use the default php bin
		phpBinPath = path.join(__dirname, 'usr', 'bin', 'php');
	}

	// Find an available port dynamically
	serverPort = await findAvailablePort(48000);
	console.log(`Using port: ${serverPort}`);

	// Build spawn arguments
	const spawnArgs = ['-S', `localhost:${serverPort}`, '-t', phpScriptPath];
	
	// Only add entry file if it's defined and not empty
	if (appConfig.entry_file && appConfig.entry_file.trim() !== '') {
		const phpEntryPoint = path.join(__dirname, appConfig.entry_file);
		spawnArgs.push(phpEntryPoint);
	}

	phpServerProcess = spawn(phpBinPath, spawnArgs);

	phpServerProcess.stdout.on('data', (data) => {
		console.log(`PHP Server: ${data}`);
	});

	phpServerProcess.stderr.on('data', (data) => {
		console.error(`PHP Server Error: ${data}`);
	});

	phpServerProcess.on('close', (code) => {
		console.log(`PHP Server exited with code ${code}`);
	});
};


const createSplashScreen = () => {
	const splash = new BrowserWindow({
		width: 300,
		height: 200,
		frame: false,
		alwaysOnTop: true,
		transparent: true,
		webPreferences: {
			nodeIntegration: true
		}
	});

	splash.loadFile('assets/splash.html');

	return splash;
};

const createWindow = () => {
	const win = new BrowserWindow({
		width: 800,
		height: 600,
		show: false,
		webPreferences: {
			nodeIntegration: true
		}
	});

	win.once('ready-to-show', () => {
		// Trigger animation when content has finished loading
		win.webContents.executeJavaScript(`
			document.body.style.opacity = 1;
			document.body.style.transition = 'opacity 1.5s ease-in-out';
		`);

		// Show the main window
		win.show();

		// Destroy the splash screen
		splash.destroy();
	});

	// Load the PHP server URL
	win.loadURL(`http://localhost:${serverPort}`);

	// Open the DevTools.
	if (appConfig.dev_console) {
			win.webContents.openDevTools();
	}
};

let splash;

app.whenReady().then(async () => {
	splash = createSplashScreen();

	// Add a delay before starting the PHP server and showing the main window
	setTimeout(async () => {
		await startPHPServer();
		createWindow();
	}, 3000); // Adjust the delay duration as needed

	app.on('activate', () => {
		if (BrowserWindow.getAllWindows().length === 0) {
			createWindow();
		}
	});
});

app.on('window-all-closed', () => {
	if (process.platform !== 'darwin') {
		app.quit();
	}
});

app.on('before-quit', () => {
	if (phpServerProcess) {
		phpServerProcess.kill();
	}
});
