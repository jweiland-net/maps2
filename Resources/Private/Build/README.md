## Working with GULP, NPM, and DDEV

Follow these steps to build fresh JS files:

- *Step 1: Access the DDEV Container*

  If you're working locally with DDEV, you need to jump into the DDEV container
  using the following command:

  ```
  ddev ssh
  ```

- *Step 2: Navigate to the 'maps2' folder*

  Use the 'cd' command to change the current working directory to the `maps2`
  folder:

  ```
  cd [pathOfMaps2]/Resources/Private/Build
  ```

- *Step 3: Install Necessary Tools*

  Execute the following command to install necessary tools like `gulp`
  and `typescript`:

  ```
  npm install
  ```

- *Step 4: Build/Compile JS Files & Move them to the Appropriate Directory*

  Use the 'gulp' command to build/compile and move the resulting JS files into
  the `Resources/Public/JavaScript` folder:

  ```
  ./node_modules/.bin/gulp
  ```
