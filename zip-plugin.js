const fs = require('fs-extra');
const archiver = require('archiver');
const path = require('path');
//const { parse } = require( 'comment-parser' );
const { arch } = require('os');

const getHeaders = (file) => {
  const fileContent = fs.readFileSync(file, 'utf8');

  // Match WordPress-style headers
  const regex = /\s*(.+):\s*(.+)/g;
  let match;
  const headers = {};
  
  while ((match = regex.exec(fileContent)) !== null) {
    headers[match[1].trim()] = match[2].trim();
  }
  
  return headers;
}

// Configuration
const pluginFolder = path.resolve(__dirname, ''); // Path to your plugin folder
const outputFolder = path.resolve(__dirname, 'dist'); // Where the ZIP file will be created

const headers = getHeaders( 'plugin.txt' );
const zipFileName = `${headers.ZipName}-${headers.Version}.zip`; // Name of the ZIP file
const customRootFolder = headers.ZipName; // Custom root folder inside the ZIP

// Directory and file patterns to exclude
const exclusions = [
  'node_modules',      // Exclude node_modules
  'dist',              // exclude dist folder that would include our zip file
  'plugin.txt',   // exclude header file
  'readme.MD',
  '.git',              // Exclude git folder
  '**/tests',          // Exclude any folder named 'tests'
  '*.log',             // Exclude log files
  '*.env',             // Exclude .env files
  'temp/**',           // Exclude everything inside 'temp' directories
  '**/*.scss',
  'zip-plugin.js',
  'package.json',
  'package-lock.json',
  '*/**/package.json',
  '*/**/package-lock.json',
  '*/**/rollup.config.mjs',
  '*/**/tsconfig.json',
  '*/**/node_modules',
  'js/src',
  'js/__old',
  '.gitignore',
  'composer.json',
  'composer.lock',
  'prepros.config',
  'dist-js',           // Exclude old dist-js folder
  'scss-*',            // Exclude scss folders
  'svelte-assess',     // Exclude Svelte src folders
  'pdf-reports',       // Exclude pdf reports
  'js/backend',
];

// Function to check if a file or folder matches exclusion patterns
const isExcluded = (relativePath) => {
  const micromatch = require('micromatch');
  return micromatch.isMatch(relativePath, exclusions);
};

async function createZip() {
  try {
    // Ensure output folder exists
    await fs.ensureDir(outputFolder);

    const outputPath = path.join(outputFolder, zipFileName);
    const output = fs.createWriteStream(outputPath);
    const archive = archiver('zip', { zlib: { level: 9 } });

    output.on('close', () => {
      console.log(`ZIP file created: ${outputPath}`);
      console.log(`${archive.pointer()} total bytes`);
    });

    archive.on('error', (err) => {
      throw err;
    });

    archive.pipe(output);

    // Add files to the archive, excluding specified patterns
    const addFiles = async (baseDir) => {
      const items = await fs.readdir(baseDir);

      for (const item of items) {
        const itemPath = path.join(baseDir, item);
        const relativePath = path.relative(pluginFolder, itemPath);

        if (isExcluded(relativePath)) {
          console.log(`Excluded: ${relativePath}`);
          continue;
        }

        const archivePath = path.join(customRootFolder, relativePath);

        if ((await fs.lstat(itemPath)).isDirectory()) {
          await addFiles(itemPath);
        } else {
          archive.file(itemPath, { name: archivePath });
        }
      }
    };

    await addFiles(pluginFolder);
    await archive.finalize();
  } catch (error) {
    console.error('Error creating ZIP:', error);
  }
}

createZip();
