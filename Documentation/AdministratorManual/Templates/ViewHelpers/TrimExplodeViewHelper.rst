..  include:: /Includes.rst.txt


=====================
TrimExplodeViewHelper
=====================

This is a ViewHelper to convert a comma separated value into an array.
All values will be trimmed.

General properties
==================

..  confval-menu::
    :name: confval-trim-explode
    :display: table
    :required:
    :type:
    :default:

..  confval:: delimiter
    :name: trim-explode-delimiter
    :required: false
    :type: string
    :default: ,

    Delimiter

Examples
========

Basic example
-------------

..  code-block:: html

    <f:for each="{poiCollection.address -> maps2:trimExplode()}" as="address" iteration="iterator">
      ..do something with {address}
    </f:for>
