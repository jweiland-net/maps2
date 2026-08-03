import { build, transform } from 'esbuild';
import { mkdirSync, readFileSync, writeFileSync } from 'fs';
import { join } from 'path';

const outDir = '../../Public/JavaScript';

mkdirSync(outDir, { recursive: true });

// Plain global scripts, minified in place. No bundling since none of them use import/require.
async function minifySingleFile(entry, outName) {
  await build({
    entryPoints: [entry],
    outfile: join(outDir, outName),
    minify: true,
    sourcemap: true,
    logLevel: 'info',
  });
}

// Vendor scripts are concatenated as plain text first (same behaviour as the former
// gulp-concat step) because they rely on sequential global execution, not ESM.
async function concatAndMinify(entries, outName) {
  const combined = entries.map((entry) => readFileSync(entry, 'utf8')).join('\n');
  const result = await transform(combined, {
    minify: true,
    sourcemap: true,
    sourcefile: outName,
  });

  writeFileSync(join(outDir, outName), `${result.code}//# sourceMappingURL=${outName}.map\n`);
  writeFileSync(join(outDir, `${outName}.map`), result.map);
  console.log(`  ${outDir}/${outName}`);
}

await concatAndMinify(
  [
    'node_modules/leaflet/dist/leaflet.js',
    'node_modules/leaflet.path.drag/src/Path.Drag.js',
    'node_modules/leaflet-editable/src/Leaflet.Editable.js',
  ],
  'leaflet.min.js',
);

await minifySingleFile('JavaScript/Classes.js', 'Classes.js');
await minifySingleFile('JavaScript/GoogleMapsModule.js', 'GoogleMapsModule.min.js');
await minifySingleFile('JavaScript/OpenStreetMapModule.js', 'OpenStreetMapModule.min.js');
await minifySingleFile('JavaScript/GoogleMaps2.js', 'GoogleMaps2.min.js');
await minifySingleFile('JavaScript/OpenStreetMap2.js', 'OpenStreetMap2.min.js');
