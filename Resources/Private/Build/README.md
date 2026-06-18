# Working with esbuild, npm, and DDEV

Follow these steps to build fresh JavaScript files for the maps2 extension.

## Step 1: Access the DDEV Container

If you are working locally with DDEV, ssh into the DDEV web container:

```bash
ddev ssh
```

## Step 2: Navigate to the Build Directory

Change your current working directory to the Build folder of the `maps2` extension:

```bash
cd [pathOfMaps2]/Resources/Private/Build
```

## Step 3: Install Dependencies

Execute the following command to install the required build tools (like `esbuild`) and frontend libraries:

```bash
npm install
```

## Step 4: Build and Compile JavaScript Files

Run the custom build script using Node.js. This will compile, bundle, and minify the JavaScript files and move them directly into the `Resources/Public/JavaScript/` folder:

```bash
node build.js
```

