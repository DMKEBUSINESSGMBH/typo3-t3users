.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _showFeUser:

Show FE Users
=============

This action can be selected in the flexform of plugin providing a detail view of a FE user. It is a
rn_base plugin which means you can use all features like link configuration etc. of rn_base.

The template can be configured in the flexform in the tab user information or through TypoScript in the constants:

.. code-block:: ts

	plugin.tx_t3users.feuserdetailsTemplate = ...

or in the setup:

.. code-block:: ts

	plugin.tx_t3users_main.feuserdetailsTemplate = ...

Example Template
----------------

The extension ships with a default template (EXT:t3users/templates/feuserlist.html). Check the
subpart *###FEUSER_DETAIL###*. It looks like this:

.. code-block:: html

	###FEUSER_DETAILS###
		<h2>###FEUSER_NAME###</h2>
		###FEUSER_IMAGE###
		<dl>
			<dt>Username:</dt> <dd>###FEUSER_USERNAME###</dd>
			<dt>Email:</dt> <dd>###FEUSER_EMAIL###</dd>
			<dt>Web:</dt> <dd>###FEUSER_WWW###</dd>
		</dl>
		###FEUSER_FEGROUPS###
			This user is member of these Groups:
			<ul>
				###FEUSER_FEGROUP###
						<li>###FEUSER_FEGROUP_TITLE###
				###FEUSER_FEGROUP###
			</ul>
		###FEUSER_FEGROUPS###
	###FEUSER_DETAILS###

Markers for User starting with *FEUSER_*. Append any database column name in uppercase letters.
Examples:

* FEUSER_USERNAME
* FEUSER_EMAIL
* FEUSER_PHONE

Show current logged in user
---------------------------

Can be configured in the flexform in the tab user information or through TypoScript:

.. code-block:: ts

	plugin.tx_t3users_main.feuserdetails.currentUser = 1

Static User
-----------

Can be configured in the flexform in the tab user information or through TypoScript:

.. code-block:: ts

	plugin.tx_t3users_main.feuserdetails.staticUser = 123


User not found message
----------------------

You can configured the message in TypoScript in the path plugin.tx_t3users_main.feuserdetails.nouser
or with following locallang key: feuserdetails.nouser

A custom locallang file can be added like this in TypoScript configuration which overwrites the contained keys:

.. code-block:: ts

	plugin.tx_t3users_main.locallangFilename {
			0 = path to custom locallang
	}

Links
-----

Links, like ###FEUSER_LISTLINK###back to list###FEUSER_LISTLINK###
can be configured fully through TypoScript. It's a rn_base feature and the TypoScript Configuration path
is as follows (check rn_base for all configuration possibilities):

.. code-block:: ts

	 ### default global
	 lib.t3users.feuser {
				 links {
						details.pid = {$plugin.tx_t3users.feuserdetailsPage}
						list.pid = {$plugin.tx_t3users.feuserlistPage}
				 }
	 }

	 ### or directly in the plugin
	 plugin.tx_t3users_main.feuserdetails.feuser.links ...

If you just want to edit the pids you can configure them in TypoScript constants editor.
Or you use the flexform in the tab user information.
