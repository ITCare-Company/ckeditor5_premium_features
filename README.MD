## Structure

### `ckeditor5-module` directory

Official Drupal module: https://git.drupalcode.org/project/ckeditor5

`ckeditor5-module` directory contains the official CKEditor 5 module for Drupal with some modifications. The editor version has been bumped to `v32.0.0` (see `package.json`), which is the latest release (state: 24.02.2022). 

The following plugins has been added to the module:
- Export to PDF,
- Comments,
- Cloud Services (a dependency required by Export to PDF)

The following DLLs has been added to the module:
- export-pdf.js
- comments.js
- cloud-services.js
- ckeditor5-collaboration-dll.js

Modified files:
- `ckeditor5.ckeditor5.yml`,
- `ckeditor5.libraries.yml`,
- `scripts/build.js` (added paths to new plugins to build DLLs)

#### DLLs location

DLLs in CKEditor 5 module are built via `scripts/build.js` script, which takes DLLs from `npm` packages and puts them in `js/build` directory. All DLLs available in the module could be found in this directory.

### `dll` directory

This directory contains ready-to-use DLLs for the following features:
- Export to PDF,
- Export to Word,
- Font