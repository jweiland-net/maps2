..  include:: /Includes.rst.txt


..  _viewhelper-is-request-to-map-provider-allowed:

=======================================
IsRequestToMapProviderAllowedViewHelper
=======================================

Use this ViewHelper to check user consent, if requests to map providers like
Google Maps or OpenStreetMap are allowed or not.


..  _viewhelper-is-request-to-map-provider-allowed-example:

Examples
========

..  _viewhelper-is-request-to-map-provider-allowed-basic-example:

Basic example
-------------

..  code-block:: html

    <f:if condition="{m:isRequestToMapProviderAllowed()}">
      <f:then>
        ...do something to show the map or what ever you want...
      </f:then>
      <f:else>
        ...show overlay or add a message what user should do to see the map...
      </f:else>
    </f:if>
