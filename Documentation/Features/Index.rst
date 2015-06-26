.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _features:

Features
========

Besides the feature you can configure some additional things in the extension configuration
in the extension manager.

**Enable login by email**

With t3users it is not only possible to login with the username but also with the email. You just
have to activate it in the path in enableLoginByEmail.

**Extend the FE user TCA**

The TCA can be extended with additional fields. This option adds new fields like first_name,
last_name and birthday to feusers.
You just have to activate it in the path in extendTCA.

**use before last login**

This option adds a new field (beforelastlogin) to feusers. It is filled with the
lastlogin timestamp before the lastlogin field was updated at any login!
You just have to activate it in the path in useBeforelastLogin.


.. toctree::
	:maxdepth: 5
	:titlesonly:
	:glob:

	Registration
	Login
	Editing
	ResetPassword
	ListFeUser
	ShowFeUser
	RenewSession
	BackendModule
	Logging
