import { cp, rm, stat } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';

const sourceDir = path.resolve('public/build');
const targetDir = path.resolve('build');

async function exists(targetPath) {
  try {
    await stat(targetPath);
    return true;
  } catch {
    return false;
  }
}

if (process.env.SKIP_LEGACY_BUILD_SYNC === '1') {
  console.log('[legacy-build-sync] Skipped. Remove this temporary compatibility layer once production serves public/build directly.');
  process.exit(0);
}

if (!(await exists(sourceDir))) {
  console.error('[legacy-build-sync] public/build was not found. Run the Vite build first.');
  process.exit(1);
}

await rm(targetDir, { recursive: true, force: true });
await cp(sourceDir, targetDir, { recursive: true, force: true });

console.log('[legacy-build-sync] Mirrored public/build -> build for current production compatibility. Remove after server cleanup.');
