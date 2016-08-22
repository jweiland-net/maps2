.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../_IncludedDirectives.rst

Updating
--------
If you update EXT:maps2 to a newer version, please read this section carefully!

Update to Version 2.0.0
^^^^^^^^^^^^^^^^^^^^^^^

Version 2.0.0 needs a Google Maps JavaScript ApiKey which has to be inserted in
maps2 configuration of Extensionmanager (for BE usage) and in constants section
of your TypoScript-Template (for FE usage). That's why you have to insert the
static template **Maps2 (maps2)** in your TypoScript Template now.

Furthermore we have updated the FlexForm of maps2 and removed the option for
SwitchableControllerActions. With version 2.1.2 we have added an Update-Wizard
in Extensionmanager which can do that job for you. In prior versions you have to
remove that setting of each plugin in tt_content record field pi_flexform on your own.

.. important::

   It does not help to open and save the record in backend!

Update to Version 2.4.1
^^^^^^^^^^^^^^^^^^^^^^^

With version 2.4.1 we have solved a camelcase problem of the cache table.
It was renamed from cf_maps2_cachedHtml to cf_maps2_cachedhtml. Please delete
the old tables cf_maps2_cachedHtml and cf_maps2_cachedHtml_tags, deactivate maps2
in extension manager and activate it again.

On LIVE-Systems you can rename these tables directly on database with PhpMyAdmin
or Adminer. No need to de- and activate the extension.
