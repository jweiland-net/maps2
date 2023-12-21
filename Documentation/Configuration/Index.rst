..  include:: /Includes.rst.txt


..  _configuration:

=============
Configuration
=============

Minimal Example
===============

..  hint::

    If you want to work with Google Maps in TYPO3 backend you or an administrator have to configure the
    Google Maps API keys in :ref:`Configure extension <extensionManager>` to get a working environment.

*   Include static template `Maps2 Default (maps2)`
*   include one of these static templates `Maps2 for Google Maps (maps2)` or
    `Maps2 for Open Street Map (maps2)`

Update these properties in TypoScript Constant Editor:

..  code-block:: typoscript

    plugin.tx_maps2 {
      persistence {
        # We prefer to set a Storage PID where the maps2 records are located
        storagePid = 4
      }
      settings {
        # If you're using Google Maps you have to set an API key to allow loading the map in frontend
        googleMapsJavaScriptApiKey = ABC123...
      }
    }

..  toctree::
    :maxdepth: 2

    Extension/Index
    Plugins/Index
    TypoScript/Index
