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
|-- 🌐 backend (where your backend resides)
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
|-- 📦 vendor
|
|-- 📄 .gitignore
|-- 📦 composer.json
|-- 📄 config.json
|-- 🚀 pulsar

```

## 😎 Getting Started

- Dowload the latest release from the release section
- Install the dependencies

  ```bash
  composer install
  cd builder && npm install
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

Make sure to inialize your project `php pulsar init` and also change your `config.json:  entry_point` and `config.json: entry_file` based on your project requirements and structure

Ps: MVC projects like laravel don't need an entry file as the framework will handle the routing and request handling, you may need to leave the `entry_file` empty and just set the entry point to your public folder i.e `backend/public`

## 🚧 Roadmap

As the project evolves, it's important to note that the use `electron-packager` will be deprecated and more conventional electron packaging solutions like `electron-builder` or `forge` as it substitutes

- [ ] Migrating from packager to builder or forge
- [ ] Compressing php source to phar
- [ ] PHP Code obfusications

## Contributing

All contributions, issues and feature requests are welcome! Feel free to check [issues page](https://github.com/ibnsultan/PHPulse/issues).
