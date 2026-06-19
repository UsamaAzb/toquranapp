import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import html from '@rollup/plugin-html';
import tailwindcss from '@tailwindcss/vite';
import { glob } from 'glob';
import path from 'path';
import iconsPlugin from './vite.icons.plugin.js';

/**
 * Get Files from a directory
 * @param {string} query
 * @returns array
 */
function GetFilesArray(query) {
  return glob.sync(query);
}

const excludedBuildInputPatterns = [
  // Unused Vuexy demo assets. Keep source files for reference, but do not ship them.
  /resources[\/\\]assets[\/\\]js[\/\\]app-ecommerce-category-list\.js$/,
  /resources[\/\\]assets[\/\\]js[\/\\]app-ecommerce-product-add\.js$/,
  /resources[\/\\]assets[\/\\]js[\/\\]app-email\.js$/,
  /resources[\/\\]assets[\/\\]js[\/\\]app-kanban\.js$/,
  /resources[\/\\]assets[\/\\]js[\/\\]forms-editors\.js$/,
  /resources[\/\\]assets[\/\\]vendor[\/\\]scss[\/\\]pages[\/\\]app-kanban\.scss$/,
  /resources[\/\\]assets[\/\\]vendor[\/\\]libs[\/\\]jkanban[\/\\]/,
  /resources[\/\\]assets[\/\\]vendor[\/\\]libs[\/\\]quill[\/\\]/
];

function withoutExcludedBuildInputs(files) {
  return files.filter(file => !excludedBuildInputPatterns.some(pattern => pattern.test(file)));
}

// Page JS Files
const pageJsFiles = withoutExcludedBuildInputs(GetFilesArray('resources/assets/js/*.js'));

// Processing Vendor JS Files
const vendorJsFiles = GetFilesArray('resources/assets/vendor/js/*.js');

// Processing Libs JS Files
const LibsJsFiles = withoutExcludedBuildInputs(GetFilesArray('resources/assets/vendor/libs/**/*.js'));

// Processing Libs Scss & Css Files
const LibsScssFiles = withoutExcludedBuildInputs(GetFilesArray('resources/assets/vendor/libs/**/!(_)*.scss'));
const LibsCssFiles = withoutExcludedBuildInputs(GetFilesArray('resources/assets/vendor/libs/**/*.css'));

// Processing Core, Themes & Pages Scss Files
const CoreScssFiles = withoutExcludedBuildInputs(GetFilesArray('resources/assets/vendor/scss/**/!(_)*.scss'));

// Processing Fonts Scss & JS Files
const FontsScssFiles = GetFilesArray('resources/assets/vendor/fonts/!(_)*.scss');
const FontsJsFiles = GetFilesArray('resources/assets/vendor/fonts/**/!(_)*.js');
const FontsCssFiles = GetFilesArray('resources/assets/vendor/fonts/**/!(_)*.css');

// Processing Window Assignment for Libs like jKanban, pdfMake
function libsWindowAssignment() {
  return {
    name: 'libsWindowAssignment',

    transform(src, id) {
      if (id.includes('jkanban.js')) {
        return src.replace('this.jKanban', 'window.jKanban');
      } else if (id.includes('vfs_fonts')) {
        return src.replaceAll('this.pdfMake', 'window.pdfMake');
      }
    }
  };
}

export default defineConfig({
  plugins: [
    tailwindcss(),
    laravel({
      input: [
        'resources/css/app.css',
        'resources/css/app-zone.css',
        'resources/css/responsive-form-controls.css',
        'resources/css/vocabulary-games.css',
        'resources/assets/css/demo.css',
        'resources/js/app.js',
        'resources/js/responsive-form-controls.js',
        ...pageJsFiles,
        ...vendorJsFiles,
        ...LibsJsFiles,
        'resources/js/laravel-user-management.js', // Processing Laravel User Management CRUD JS File
        ...CoreScssFiles,
        ...LibsScssFiles,
        ...LibsCssFiles,
        ...FontsScssFiles,
        ...FontsJsFiles,
        ...FontsCssFiles
      ],
      refresh: true
    }),
    html(),
    libsWindowAssignment(),
    iconsPlugin()
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources')
    }
  },
  json: {
    stringify: true // Helps with JSON import compatibility
  },
  build: {
    commonjsOptions: {
      include: [/node_modules/] // Helps with importing CommonJS modules
    }
  }
});
