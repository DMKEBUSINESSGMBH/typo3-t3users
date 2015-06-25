.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _listFeUser:

List FE Users
=============

This action can be selected in the flexform of plugin providing a list of FE users. It is a
rn_base plugin which means you can use all features like link configuration etc. of rn_base.

The template can be configured in the flexform in the tab user information or through TypoScript in the constants:

.. code-block:: ts

	plugin.tx_t3users.feuserlistTemplate = ...

or in the setup:

.. code-block:: ts

	plugin.tx_t3users_main.feuserlistTemplate = ...


Example Template
----------------

The extension ships with a default template (EXT:t3users/templates/feuserlist.html). Check the
subpart *###FEUSER_LIST###*. It looks like this:

.. code-block:: html

	###FEUSER_LIST###
			###FEUSERS###
				<h2>List of FE-Users</h2>
				<ul>
						###FEUSER###
							<li>###FEUSER_DETAILSLINK######FEUSER_NAME######FEUSER_DETAILSLINK###</li>
						###FEUSER###
						###FEUSEREMPTYLIST###
							<li>no users found.</li>
						###FEUSEREMPTYLIST###
				</ul>
				<!-- ###PAGEBROWSER### -->
						<div class="t3users-pagebrowser">
							###PAGEBROWSER_CURRENT_PAGE###
							Page ###PAGEBROWSER_CURRENT_PAGE_NUMBER###
							###PAGEBROWSER_CURRENT_PAGE###

							###PAGEBROWSER_NORMAL_PAGE###
							###PAGEBROWSER_NORMAL_PAGE_LINK###Page ###PAGEBROWSER_NORMAL_PAGE_NUMBER### ###PAGEBROWSER_NORMAL_PAGE_LINK###
							###PAGEBROWSER_NORMAL_PAGE###

							###PAGEBROWSER_PREV_PAGE###
							&nbsp;###PAGEBROWSER_PREV_PAGE_LINK###<###PAGEBROWSER_PREV_PAGE_LINK###&nbsp;
							###PAGEBROWSER_PREV_PAGE###

							###PAGEBROWSER_NEXT_PAGE###
							&nbsp;###PAGEBROWSER_NEXT_PAGE_LINK###>###PAGEBROWSER_NEXT_PAGE_LINK###&nbsp;
							###PAGEBROWSER_NEXT_PAGE###

							###PAGEBROWSER_FIRST_PAGE###
							###PAGEBROWSER_FIRST_PAGE_LINK### |< ###PAGEBROWSER_FIRST_PAGE_LINK###
							###PAGEBROWSER_FIRST_PAGE###

							###PAGEBROWSER_LAST_PAGE###
							###PAGEBROWSER_LAST_PAGE_LINK### >| ###PAGEBROWSER_LAST_PAGE_LINK###
							###PAGEBROWSER_LAST_PAGE###
						</div>
				<!-- ###PAGEBROWSER### -->
			###FEUSERS###
	###FEUSER_LIST###

Markers for User starting with *FEUSER_*. Append any database column name in uppercase letters.
Examples:

* FEUSER_USERNAME
* FEUSER_EMAIL
* FEUSER_PHONE

Pagebrowser
-----------

We use a rn_base pagebrowser. The TypoScript configuration can be global:

.. code-block:: ts

	lib.t3users.pagebrowser {
			limit = 2
			maxPages = 7
			pagefloat = CENTER
			link.useKeepVars = 1
	}

		### or directly here where the above config is copied in by default
		lib.t3users.feuser.pagebrowser ...

Or it can be configured directly for the plugin.

.. code-block:: ts

	plugin.tx_t3users_main.feuserlist.feuser.pagebrowser...

Links
-----

Links, like ###FEUSER_DETAILSLINK######FEUSER_NAME######FEUSER_DETAILSLINK### in the template above
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
	plugin.tx_t3users_main.feuserlist.feuser.links ...

If you just want to edit the pids you can configure them in TypoScript constants editor.
Or you use the flexform in the tab user information.

Static User List
----------------

You can configure a static list of users to be shown in the flexform in the tab user information or with TypoScript:

.. code-block:: ts

	plugin.tx_t3users_main.feuserlist.staticUsers = 1,2,3...


Freetext Search
---------------

You need to add a form in the template like:

.. code-block:: html

	###FEUSER_LIST###
			<form action="###PLUGIN_DCFORMACTION###">
				<input type="text" name="t3users[searchfeuser]" value="###PLUGIN_DCINPUTVALUE###"/>
				<input type="submit" name="t3users[newsearch]" />
			</form>
			...

The *###PLUGIN_DC...* markers can be configured in TypoScript like this:

.. code-block:: ts

	plugin.tx_t3users_main.feuserlist.plugin {
			dcformaction = TEXT
			dcformaction.value = /userlist

			### retrieve the value from $_GET parameters
			dcinputvalue...
	}
