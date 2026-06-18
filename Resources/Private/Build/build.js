const esbuild = require('esbuild');
const path = require('path');

// Base options shared across all build tasks
const baseOptions = {
  minify: true,
  sourcemap: true,
  target: 'es2020',
  outdir: '../../Public/JavaScript',
};

async function runBuild() {
  try {
    // 1. Leaflet Bundle via stdin (enforces strict concatenation order)
    await esbuild.build({
      ...baseOptions,
      bundle: true,
      // Simulate a JS entry point that loads the three scripts sequentially
      stdin: {
        contents: `
          require('leaflet/dist/leaflet.js');
          require('leaflet.path.drag/src/Path.Drag.js');
          require('leaflet-editable/src/Leaflet.Editable.js');
        `,
        resolveDir: __dirname, // Tells esbuild where to locate node_modules
      },
      outfile: path.join(baseOptions.outdir, 'leaflet.min.js'),
      // Remove outdir here since outfile is explicitly set
      outdir: undefined,
    });

    // 2. TYPO3 JavaScript Modules (Native ESM, NO bundling!)
    await esbuild.build({
      ...baseOptions,
      bundle: false,
      format: 'esm', // Preserves native "import/export" statements for the browser
      entryPoints: {
        'Classes': 'JavaScript/Classes.js',
        'GoogleMapsModule.min': 'JavaScript/GoogleMapsModule.js',
        'OpenStreetMapModule.min': 'JavaScript/OpenStreetMapModule.js',
        'GoogleMaps2.min': 'JavaScript/GoogleMaps2.js',
        'OpenStreetMap2.min': 'JavaScript/OpenStreetMap2.js',
      },
    });

    console.log('🎉 Build completed successfully!');
  } catch (err) {
    console.error('❌ Build failed:', err);
    process.exit(1);
  }
}

runBuild();
