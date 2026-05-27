import fs from 'fs/promises';
import path from 'path';
import { glob } from 'glob';
import { getIconsCSS } from '@iconify/utils';

const TABLER_ICON_CLASS_REGEX = /\btabler-([a-z0-9]+(?:-[a-z0-9]+)*)\b/gi;

const ignoredIconNames = new Set(['filled']);

const legacyIconAliases = {
  save: 'device-floppy'
};

const iconSourceGlobs = [
  'app/**/*.php',
  'config/**/*.php',
  'resources/assets/js/**/*.js',
  'resources/assets/vendor/js/**/*.js',
  'resources/js/**/*.js',
  'resources/menu/**/*.json',
  'resources/views/**/*.blade.php',
  'routes/**/*.php'
];

const safetyIcons = [
  'adjustments-horizontal',
  'alert-circle',
  'alert-circle-filled',
  'alert-triangle',
  'alert-triangle-filled',
  'archive',
  'arrow-down',
  'arrow-left',
  'arrow-right',
  'arrow-up',
  'arrows-sort',
  'ban',
  'bell',
  'calendar',
  'calendar-check',
  'calendar-event',
  'check',
  'checks',
  'chevron-down',
  'chevron-left',
  'chevron-right',
  'chevron-up',
  'chevrons-left',
  'chevrons-right',
  'circle-check',
  'circle-check-filled',
  'circle-filled',
  'circle-x',
  'clipboard-check',
  'clipboard-list',
  'clock',
  'clock-hour-4',
  'copy',
  'device-desktop',
  'device-desktop-analytics',
  'device-floppy',
  'dots',
  'dots-vertical',
  'download',
  'edit',
  'external-link',
  'eye',
  'eye-off',
  'file',
  'file-code-2',
  'file-description',
  'file-export',
  'file-spreadsheet',
  'file-text',
  'file-type-doc',
  'file-type-pdf',
  'file-type-ppt',
  'file-type-xls',
  'file-upload',
  'filter',
  'help',
  'help-circle',
  'info-circle',
  'link',
  'loader-2',
  'lock',
  'login',
  'logout',
  'mail',
  'menu-2',
  'minus',
  'moon-stars',
  'pencil',
  'plus',
  'printer',
  'refresh',
  'rotate-2',
  'rotate-clockwise',
  'save',
  'search',
  'selector',
  'settings',
  'sort-ascending',
  'sort-descending',
  'sun',
  'trash',
  'upload',
  'user',
  'user-check',
  'user-off',
  'user-plus',
  'users',
  'users-group',
  'x'
];

async function findStaticTablerIcons() {
  const files = await glob(iconSourceGlobs, {
    cwd: process.cwd(),
    absolute: true,
    nodir: true,
    windowsPathsNoEscape: true
  });

  const icons = new Set();

  await Promise.all(
    files.map(async file => {
      const content = await fs.readFile(file, 'utf8');
      for (const match of content.matchAll(TABLER_ICON_CLASS_REGEX)) {
        const iconName = match[1].toLowerCase();

        if (!ignoredIconNames.has(iconName)) {
          icons.add(iconName);
        }
      }
    })
  );

  return icons;
}

function applyLegacyIconAliases(iconSet) {
  iconSet.aliases = iconSet.aliases ?? {};

  for (const [alias, parent] of Object.entries(legacyIconAliases)) {
    if (!iconSet.icons[alias] && !iconSet.aliases[alias] && iconSet.icons[parent]) {
      iconSet.aliases[alias] = { parent };
    }
  }
}

function resolveRequestedIcons(iconSet, requestedIcons) {
  const availableIcons = new Set([...Object.keys(iconSet.icons), ...Object.keys(iconSet.aliases ?? {})]);
  const resolvedIcons = [];
  const skippedIcons = [];

  for (const icon of requestedIcons) {
    if (availableIcons.has(icon)) {
      resolvedIcons.push(icon);
    } else {
      skippedIcons.push(icon);
    }
  }

  return {
    resolvedIcons: [...new Set(resolvedIcons)].sort(),
    skippedIcons: [...new Set(skippedIcons)].sort()
  };
}

export default function iconifyPlugin() {
  return {
    name: 'vite-iconify-plugin',
    apply: 'build', // Run only during build

    async buildStart() {
      console.log('Generating Iconify CSS file...');

      try {
        const iconSetPaths = [path.resolve(process.cwd(), 'node_modules/@iconify/json/json/tabler.json')];
        const staticIcons = await findStaticTablerIcons();
        const requestedIcons = new Set([...staticIcons, ...safetyIcons]);

        const iconSets = await Promise.all(
          iconSetPaths.map(async filePath => {
            const data = await fs.readFile(filePath, 'utf-8');
            return JSON.parse(data);
          })
        );

        const allIcons = iconSets
          .map(iconSet => {
            applyLegacyIconAliases(iconSet);

            const { resolvedIcons, skippedIcons } = resolveRequestedIcons(iconSet, requestedIcons);

            console.log(
              `Iconify Tabler icons: ${resolvedIcons.length} kept from ${requestedIcons.size} requested ` +
                `(${Object.keys(iconSet.icons).length} available).`
            );

            if (skippedIcons.length > 0) {
              throw new Error(
                `Unknown Tabler icons requested: ${skippedIcons.join(', ')}. ` +
                  'Add false positives to ignoredIconNames, legacy names to legacyIconAliases, ' +
                  'or intentional dynamic icons to safetyIcons.'
              );
            }

            return getIconsCSS(iconSet, resolvedIcons, {
              iconSelector: '.{prefix}-{name}',
              commonSelector: '.ti',
              format: 'expanded'
            });
          })
          .join('\n');

        const outputPath = path.resolve(process.cwd(), 'resources/assets/vendor/fonts/iconify/iconify.css');
        const dir = path.dirname(outputPath);
        await fs.mkdir(dir, { recursive: true });
        await fs.writeFile(outputPath, allIcons, 'utf8');

        console.log(`Iconify CSS generated at: ${outputPath}`);

        const additionalFiles = [
          {
            name: 'fontawesome',
            filesPath: path.resolve(process.cwd(), 'node_modules/@fortawesome/fontawesome-free/webfonts'),
            destPath: path.resolve(process.cwd(), 'resources/assets/vendor/fonts/fontawesome')
          },
          {
            name: 'flags',
            filesPath: path.resolve(process.cwd(), 'node_modules/flag-icons/flags'),
            destPath: path.resolve(process.cwd(), 'resources/assets/vendor/fonts/flags')
          }
        ];

        for (const file of additionalFiles) {
          await fs.mkdir(file.destPath, { recursive: true });
          const items = await fs.readdir(file.filesPath, { withFileTypes: true });
          for (const item of items) {
            const srcPath = path.join(file.filesPath, item.name);
            const destPath = path.join(file.destPath, item.name);
            if (item.isDirectory()) {
              await fs.mkdir(destPath, { recursive: true });
              const subItems = await fs.readdir(srcPath);
              for (const subItem of subItems) {
                await fs.copyFile(path.join(srcPath, subItem), path.join(destPath, subItem));
              }
            } else {
              await fs.copyFile(srcPath, destPath);
            }
          }
        }
      } catch (error) {
        console.error('Error generating Iconify CSS or copying additional files:', error);
        throw error;
      }
    }
  };
}
