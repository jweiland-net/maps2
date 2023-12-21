..  include:: /Includes.rst.txt


==============================
RequestUriForOverlayViewHelper
==============================

This ViewHelper creates an URI with a special parameter which allows the map to be visible for the user.


Examples
========

Basic example
-------------

..  code-block:: html

    <a href="{m:requestUriForOverlay()}">
      Link to current page. Map will be shown somewhere on that page.
    </a>

Scroll to content element
-------------------------

If you add `ttContentUid` to ViewHelper it will add a link section (#174)
to the end of the URI. If all of your content elements contain an `id` attribute like `c174`
the target page will scroll to this specific content element directly.

..  code-block:: html

    <a href="{m:requestUriForOverlay(ttContentUid: ttContentUid)}">
      Link to current page and scroll to content element with map.
    </a>
