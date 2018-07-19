.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.rst.txt

.. _installation:

Installation
============

The extension needs to be installed like any other extension of TYPO3 CMS:

#. Visit maps2 at `Github <https://github.com/jweiland-net/maps2>`_

#. You will find a Download-Button where you can select between Download as Zip or Link for cloning this project.

#. Get the extension

   #. **Get it via Zip:** Switch to the Extensionmanager and upload maps2

   #. **Get it via Git:** If Git is available on your system, switch into
      the typo3conf/ext/ directory and clone it from Github:

      .. code-block:: bash

         git clone https://github.com/jweiland-net/maps2.git

   #. **Get it via Composer:**

      .. code-block:: bash

         composer require jweiland/maps2

#. The Extension Manager offers some basic configuration which is
   explained :ref:`here <extensionManager>`.

Preparation: Include static TypoScript
--------------------------------------

The extension ships some TypoScript code which needs to be included.

#. Switch to the root page of your site.

#. Switch to the **Template module** and select *Info/Modify*.

#. Press the link **Edit the whole template record** and switch to the tab *Includes*.

#. Select **Maps2 (maps2)** at the field *Include static (from extensions):*

.. figure:: /Images/AdministratorManual/maps2-include-TypoScript.png
   :width: 500px
   :alt: How to include the static template for maps2

Preparation: Update values in Constant Editor
---------------------------------------------

#. Switch to the **Template module** and select *Constant Editor*.

#. Add Google Maps JavaScript API Key. Maybe you already have set an API Key in Extensionmanager. But that one is
   for Backend usage only. This setting here is for Frontend output.
