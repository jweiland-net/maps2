.. include:: ../Includes.txt


.. _known-problems:

================
DB compatibility
================

In most cases EXT:maps2 uses the QueryBuilder to query data, but in case of Plugin `maps2_searchwithinradius`
we need to execute a native MySQL query without QueryBuilder to find the related POIs. In that special case
MySQL/MariaDB is mandatory.
