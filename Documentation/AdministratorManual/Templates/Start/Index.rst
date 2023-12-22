..  include:: /Includes.rst.txt


============================
Changing & editing templates
============================

EXT:maps2 is using fluid as template engine. If you are know how to manage fluid templates, you can skip this section.


Changing paths of the template
==============================

You should never edit the original templates of an extension as those changes will vanish if you upgrade the extension.
As any extbase based extension, you can find the templates in the directory ``Resources/Private/``.

If you want to change a template, copy the desired files to the directory where you store the templates. This should
be a directory in your SitePackage extension. Multiple fallbacks can be defined which makes it far easier to customize
the templates.

..  code-block:: typoscript

    plugin.tx_maps2 {
      view {
        templateRootPaths >
        templateRootPaths {
          0 = EXT:maps2/Resources/Private/Templates/
          1 = EXT:site_package/Resources/Private/Extensions/Maps2/Templates/
        }
        partialRootPaths >
        partialRootPaths {
          0 = EXT:maps2/Resources/Private/Partials/
          1 = EXT:site_package/Resources/Private/Extensions/Maps2/Partials/
        }
        layoutRootPaths >
        layoutRootPaths {
          0 = EXT:maps2/Resources/Private/Layouts/
          1 = EXT:site_package/Resources/Private/Extensions/Maps2/Templates/Layouts/
        }
      }
    }

Change the templates using TypoScript constants
-----------------------------------------------

You can use the following TypoScript in the  **constants** to change
the paths

..  code-block:: typoscript

    plugin.tx_maps2 {
      view {
        templateRootPath = EXT:site_package/Resources/Private/Extensions/Maps2/Templates/
        partialRootPath = EXT:site_package/Resources/Private/Extensions/Maps2/Partials/
        layoutRootPath = EXT:site_package/Resources/Private/Extensions/Maps2/Layouts/
      }
    }
