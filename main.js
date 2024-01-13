const { app, BrowserWindow } = require('electron');
const { spawn } = require('child_process');
const path = require('path');

// php application config
const appConfig = {
  "port": 48183,
  "dev_console": true,              // set to false to hide the developer console
  "php_path": "php/php.exe",        // relative to app root
  "entry_point": "app/public"       // relative to app root
};

let phpServerProcess;

const startPHPServer = () => {
  const phpScriptPath = path.join(__dirname, appConfig.entry_point);
  phpServerProcess = spawn(appConfig.php_path, ['-S', `localhost:${appConfig.port}`, '-t', appConfig.entry_point]);

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

  splash.loadFile('splash.html');

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
  win.loadURL(`http://localhost:${appConfig.port}`);

  // Open the DevTools.
    if (appConfig.dev_console) {
        win.webContents.openDevTools();
    }
};

let splash;

app.whenReady().then(() => {
  splash = createSplashScreen();

  // Add a delay before starting the PHP server and showing the main window
  setTimeout(() => {
    startPHPServer();
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
