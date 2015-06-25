.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _renewSession:

Renew Session
=============

Sometimes it's neccessary to prevent an automatic logout as long as a user is on a page. For that
purpose there is the action Renew Session. You can choose it in the flexform of the plugin.

This action does nothing more than executing an ajax call to the current site, which means basically
a site refresh in the background.

The intervall for the refresh can be configured in the tab renew session in the flexform or through
TypoScript. Please note that the flexform config as precedence. Default is 300 seconds (5 minutes).

.. code-block:: ts

	plugin.tx_t3users_main.renewSession.intervallInSeconds = 300
