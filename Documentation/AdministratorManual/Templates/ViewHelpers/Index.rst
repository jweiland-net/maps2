.. include:: ../../../Includes.txt

ViewHelpers of EXT:maps2
========================

ViewHelpers are used to add logic inside the view.
There're basic things like if/else conditions, loops and so on. The system extension fluid has the most important
ViewHelpers already included.

To be able to use a ViewHelper in your template, you need to follow always the same structure which is:

.. code-block:: html

   <f:foo>bar</f:foo>

This would call the ViewHelper foo of the namespace **f** which stands for fluid.
If you want to use ViewHelpers from other extensions you need to add the namespace
declaration at the beginning of the template. Add or update following lines in your template, partial or layout:

.. code-block:: html

   <html
     xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
     xmlns:maps2="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
     data-namespace-typo3-fluid="true"
   >

Now you can use a ViewHelper of maps2 with a code like:

.. code-block:: html

   <maps2:trimExplode><!-- some comment --></maps2:trimExplode>

If you want to know what a ViewHelper does, it is very easy to find the related PHP class by looking at the
namespace and the name of the ViewHelper. Having e.g. ``JWeiland\Maps2\ViewHelpers`` and ``convertToJson`` you will
find the class at ``maps2\Classes\ViewHelpers\ConvertToJsonViewHelper.php``.

The most awesome thing is that you can use ViewHelpers of any extension in any other template by just adding
another namespace declaration like:

.. code-block:: html

   <html
     xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
     xmlns:maps2="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
     xmlns:e="http://typo3.org/ns/JWeiland/Events2/ViewHelpers"
     data-namespace-typo3-fluid="true"
   >

and call the ViewHelper like

.. code-block:: html

   <e:NameOfTheViewHelper />

All ViewHelpers
---------------

.. toctree::
   :maxdepth: 2
   :titlesonly:
   :glob:

   ConvertToJsonViewHelper
   TrimExplodeViewHelper

   Cache/GetCacheViewHelper
   Cache/HasCacheViewHelper
   Cache/SetCacheViewHelper

   Widget/PoiCollectionViewHelper
   Widget/EditPoiViewHelper
