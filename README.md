<div style="width:100%;text-align:center">
  <img src="https://raw.githubusercontent.com/ibnsultan/PHPulse/main/builder/assets/build/logo.png" alt="PHPulse Logo" width="60" height="60" />
</div>

# PHPulse

PHPulse is an experimental PHP framework designed specifically for constructing desktop applications. 

While currently in its proof-of-concept stage, it's important to note that using PHPulse for production development is not recommended at this time.

## 🛠️ Supported Platforms

- ✅ Windows
- ☑️ Linux (Limited Support)
  some component are not yet complete ref (TODO. builder/main.js ln:12)
- ❌ Mac OS (Very Limited Support)
  some component are not yet complete ref (TODO. builder/main.js ln:12)

## 🧰 Tools Utilized

- PHP
- Electron
- NodeJS 18 LTS

## 🚦 Project Overview

PHPulse combines the power of PHP and Electron to create desktop applications efficiently. With the right configuration any app can be swiftly packaged

The initial skeleton comes with a basic router to handle and designate http request to their respective allocations `public/router.php` which also the default entry of the application

**Folder Structure**

```
📁 ProjectName
|-- 🛠 builder
|   |-- 🖼 assets
|   |   |-- 🏗 build
|   |-- 📜 main.js
|   |-- 📄 config.json
|   |-- 📦 package.json
|
|-- ⚙️ console
|   |-- 🐘 engine.php
|   |-- 🤖 helper.php
|
|-- 🌐 public
|
|-- 📦 vendor
|
|-- 📂 other-project-folder (php)
|-- 📄 other-project-files (php)
|
|-- 📄 .gitignore
|-- 📦 composer.json
|-- 📄 config.json
|-- 🚀 pulsar

```

## 😎 Getting Started

- Dowload the latest release from the release section
- Install electron packager

  ```bash
  npm install -g electron-packager
  ```
- Initialize your application

  ```bash
  php pulsar init
  ```

  An interactive cli form will be displayed to configure your application details
- To test and debug ur app run

  ```bash
  php pulsar serve
  ```
- To build the app run

  ```bash
  php pulsar make
  ```

## ✨ Using a custom project

To use a custom/existing project or with frameworks like laravel, make sure you first import the composer packages used i.e `symfony/filesystem` , in your project then copy everything else to your project except the `public` folder, along with the composer folders to avoid your files being overwritten

Make sure to inialize your project `php pulsar init` and also change your `config.json:  entry_point` and `config.json: entry_file`

NOTE: Leave the `app` prefix in the `entry_point` and `entry_file` as it is.

## 🚧 Roadmap

As the project evolves, it's important to note that the use `electron-packager` will be deprecated and more conventional electron packaging solutions like `electron-builder` or `forge` as it substitutes

- [ ] Migrating from packager to builder or forge
- [ ] Compressing php source to phar
- [ ] Custom inbuilt database
- [ ] PHP Code obfusications

## Contributing

All contributions, issues and feature requests are welcome! Feel free to check [issues page](https://github.com/ibnsultan/PHPulse/issues).
