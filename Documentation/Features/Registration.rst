.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _registration:

Registration
============

The registration can simply be selected in the flexform but requires some TypoScript configuration.
For now there is sadly no tab in the flexform to configure all the things. But you can use the
constants editor.

TypoScript Constants
--------------------

**plugin.tx_t3users.siteName**

The name of the site. This is used for example in the template markers for the confirmation mail or
as the fromName in the e-mail.

The alternativ TypoScript setup path is *plugin.tx_t3users_main.siteName*

**plugin.tx_t3users.userPages**

This is where registered users are stored.

The alternativ TypoScript setup path is *plugin.tx_t3users_main.feuserPages*

**plugin.tx_t3users.userGroupUponRegistration**

FE group UID of newly created FE users from the registration. Leave empty if you don't want
a group to be set.

The alternativ TypoScript setup path is *plugin.tx_t3users_main.userGroupUponRegistration*

**plugin.tx_t3users.userGroupAfterConfirmation**

FE group UID of confirmed FE users from the registration. The FE groups set with userGroupUponRegistration
are removed if this set.

The alternativ TypoScript setup path is *plugin.tx_t3users_main.userGroupAfterConfirmation*

**plugin.tx_t3users.confirmPage**

PID of page for feuser confirmation from mail link. The plugin with the action for the confirmation
should be inserted on that page.

The alternativ TypoScript setup path is *plugin.tx_t3users_main.showregistration.links.mailconfirm.pid*

In *plugin.tx_t3users_main.showregistration.links.mailconfirm* you can configure the complete link
with the rn_base features.

**plugin.tx_t3users.email**

The administration email address. This email address will be the sender email and also
receive administration notifications.

The alternativ TypoScript setup path is *plugin.tx_t3users_main.showregistration.email.from*

**plugin.tx_t3users.adminReviewMail**

If this is configured the user is not activated upon confirmation. Instead a new
confirmation mail is send to this e-mail which is usually a admin. So there should be a first confirmation
page where this option is set. On this confirmation page there has to be another second confirmation page configured
in *plugin.tx_t3users.confirmPage* where the adminReviewMail then is not configured. On this second
confirmation page *plugin.tx_t3users.notifyUserAboutConfirmation* should be configured so the user is
notified about the final confirmation through the admin.

The same mail template like for the normal confirmation is used. You can overwrite this on the desired
page by adding ad additional locallang file by configuring *plugin.tx_t3users_main.locallangFilename.0 = path-to-file*
In this file you can overwrite the label registration_confirmation_mail which is used for the
confirmation mail.

The alternativ TypoScript setup path is *plugin.tx_t3users_main.showregistration.adminReviewMail*

**plugin.tx_t3users.notifyUserAboutConfirmation**

Should the user be notified about his confirmation? Don't set this on a page where adminReviewMail
is configured. Either on the normal confirmation page when no adminReviewMail is not set or on the
second confirmation page when adminReviewMail is set. (look above)

The alternativ TypoScript setup path is *plugin.tx_t3users_main.showregistration.notifyUserAboutConfirmation*

**plugin.tx_t3users.registrationTemplate**

The template for the registration action. You can find an example below.

The alternativ TypoScript setup path is *plugin.tx_t3users_main.showregistration.registrationTemplate*

TypoScript Setup
----------------

**plugin.tx_t3users.showregistration.ameos**

Formerly the form for the registration was created with the ameos_formidable extension. As this
extension is no longer maintained we provide the possiblity to use another extension. This should be
`mkforms <http://typo3.org/extensions/repository/view/mkforms>`_. In this case you have to insert
*tx_mkforms_forms_Base* in *plugin.tx_t3users.showregistration.ameos*.

**plugin.tx_t3users.showregistration.email.cc**

It can be configured to send another mail to the configured address while sending the confirmation mail.
The used email template can be found in the locallang in the label *registration_confirmation_mail_cc* which is
empty by default. If it is empty the template in the label *registration_confirmation_mail* is used instead.

So if you want to use this you usually have to provide an addtional locallang file like *plugin.tx_t3users_main.locallangFilename.0 = path-to-file*
(you can add as many files as you want like plugin.tx_t3users_main.locallangFilename.1, plugin.tx_t3users_main.locallangFilename.2...).

An example for the label *registration_confirmation_mail_cc* can look like this:

.. code-block:: html

	A user registered
	Username: ###FEUSER_USERNAME###
	UID: ###FEUSER_UID###

**plugin.tx_t3users.showregistration.feuser**

This configuration is used for all markers (see template below) starting with *###FEUSER_...* to configure
single field. This way for example you can configure the www field to be wrapped with a link. You can
use full TypoScript configuration options. This is an example from the default TypoScript configuration:

.. code-block:: ts

	plugin.tx_t3users.showregistration.feuser.www {
		ifEmpty = -
		typolink.parameter.field = www
		typolink.extTarget = _new
	}

Alternative global path: *lib.t3users.feuser*

Form XML
--------

The XML file is configured through TypoScript in the path *plugin.tx_t3users.showregistration.formxml* and
is usually a `mkforms <http://typo3.org/extensions/repository/view/mkforms>`_ XML file.
The default one (EXT:t3users/forms/registration.xml) looks like this:

.. code-block:: xml

	<?xml version="1.0" encoding="UTF-8"?>
	<formidable version="0.7.0">
		<meta>
			<name>Registration form for feusers</name>
			<form formid="registration"/>
			<debug>false</debug>
				<displaylabels>true</displaylabels>
		</meta>
		<control>
			<datahandler:DB>
				<tablename>fe_users</tablename>
				<keyname>uid</keyname>
				<process>
					<beforeinsertion>
						<userobj>
							<!-- this is the action class so tx_t3users_actions_ShowRegistration -->
							<extension>this</extension>
							<method>handleBeforeUpdateDB</method>
						</userobj>
					</beforeinsertion>
					<afterinsertion>
						<userobj>
							<!-- this is the action class so tx_t3users_actions_ShowRegistration -->
							<extension>this</extension>
							<method>handleUpdateDB</method>
						</userobj>
					</afterinsertion>
				</process>
			</datahandler:DB>

			<!--
				you can configure a separate template. if not the XML is rendered without a template.
				you can find an example in the template below. Just look at the subpart ###REGISTRATIONFORM###
			-->
			<!-- renderer:TEMPLATE>
				<template subpart="###REGISTRATIONFORM###">
					<errortag>formerrors</errortag>
					<path>EXT:...html</path>
				</template>
			</renderer:TEMPLATE-->
		</control>

		<elements>
			<renderlet:BOX name="EDITION-BOX">
				<childs>
					<renderlet:TEXT name="username" label="LLL:EXT:t3users/locallang.php:label_form_username">
						<validators>
							<validator:STANDARD>
								<required>
									<value>true</value>
									<message>LLL:EXT:t3users/locallang.php:msg_form_username_required</message>
								</required>
							</validator:STANDARD>
							<validator:DB>
								<!-- deleted="TRUE" -> exclude deleted datasets -->
								<unique>
									<value>true</value>
									<message>LLL:EXT:t3users/locallang.php:msg_form_username_unique</message>
									<deleted>TRUE</deleted>
								</unique>
							</validator:DB>
						</validators>
					</renderlet:TEXT>

					<renderlet:PASSWORD name="password">
						<label>LLL:EXT:t3users/locallang.php:label_form_password</label>
					</renderlet:PASSWORD>

					<renderlet:PASSWORD name="password_confirm">
						<label>LLL:EXT:t3users/locallang.php:label_form_password_confirm</label>
						<renderonly>true</renderonly>
						<confirm>EDITION-BOX__password</confirm>
						<validators>
							<validator:STANDARD>
								<required>
									<value>true</value>
									<message>LLL:EXT:t3users/locallang.php:msg_form_password_required</message>
								</required>
								<sameas>
									<value>EDITION-BOX__password</value>
									<message>LLL:EXT:t3users/locallang.php:msg_form_password_sameas</message>
								</sameas>
							</validator:STANDARD>
						</validators>
					</renderlet:PASSWORD>

					<renderlet:TEXT name="first_name" label="First name" />
					<renderlet:TEXT name="last_name" label="last_name" />

					<renderlet:TEXT name="email" label="Email">
						<validators>
							<validator:STANDARD>
									<required message="Please provide an e-mail adress."/>
									<email message="The e-mail adress is not valid."/>
							</validator:STANDARD>
							<validator:DB>
								<!-- deleted="TRUE" -> exclude deleted datasets -->
								<unique
									message="The e-mail is already in use."
									deleted="TRUE"
								/>
							</validator:DB>
						</validators>
					</renderlet:TEXT>

					<renderlet:TEXT name="address" label="Strasse"/>
					<renderlet:TEXT name="zip" label="PLZ"/>
					<renderlet:TEXT name="city" label="Ort"/>
					<renderlet:SUBMIT name="btnsubmit" label="Register"/>
				</childs>
			</renderlet:BOX>
		</elements>
	</formidable>

Form Template
-------------

The template file is configured through TypoScript in the constants path *plugin.tx_t3users.registrationTemplate*
or in the setup path *plugin.tx_t3users.registrationTemplate*

It can have at least two main subparts for the registration and the registration confirmation. Those
main subparts again can have several subparts which should be self-explanatory.
The default one (EXT:t3users/templates/registration.xml) looks like this. You can also
use label markers like "###LABEL_MYLABEL### from the locallang file. Note that the marker has to start
with "###LABEL_...'

.. code-block:: html

	<h2>Template for the Registration</h2>
	###REGISTRATION###

		###PART_REGISTER###
			###FORM###
		###PART_REGISTER###

		###PART_REGISTERFINISHED###
			<p>The data was saved. A confirmation was sent.</p>
		###PART_REGISTERFINISHED###

		###PART_CONFIRMED###
			<p>The account was confirmed successful.</p>
		###PART_CONFIRMED###

		###PART_CONFIRMFAILED###
			<p>The activiation has failed.</p>
		###PART_CONFIRMFAILED###

		###PART_ADMINREVIEWMAILSENT###
			<p>The registration will be review by an admin. You will be notified on success.</p>
		###PART_ADMINREVIEWMAILSENT###

		###PART_ADMINREVIEWMAILSENTALREADY###
			<p>The registration is being already reviewed by an admin. You will be notified on success.</p>
		###PART_ADMINREVIEWMAILSENTALREADY###

	###REGISTRATION###


	<h2>Template for the registration confirmation</h2>
	###REGISTRATIONCONFIRM###

		###PART_CONFIRMED###
			<p>The account for ###FEUSER_USERNAME### was activated successfully.</p>
			###FEUSER_FEGROUPS###
				<h4>You are member of this groups:</h4>
				<ul>
					###FEUSER_FEGROUP###
							<li>###FEUSER_FEGROUP_TITLE###
					###FEUSER_FEGROUP###
				</ul>
			###FEUSER_FEGROUPS###
		###PART_CONFIRMED###

		###PART_CONFIRMFAILED###
			<p>The activiation has failed.</p>
		###PART_CONFIRMFAILED###

	###REGISTRATIONCONFIRM###


	<h2>Template for the registration form which should be configured in the XML file (look above)</h2>
	###REGISTRATIONFORM###
		<!-- ###EDITION-BOX### start-->
			<div style="display: {formerrors.cssdisplay};">
				<p>Please confirm your data:</p>
				{formerrors}
			</div>
			<div>
				<h2>User data</h2>
				<dl>
					<dt>{username.label}</dt> <dd>{username.input}</dd>
					<dt>{first_name.label}</dt> <dd>{first_name.input}</dd>
					<dt>{password.label}</dt> <dd>{password.input}</dd>
					<dt>{password_confirm.label}</dt> <dd>{password_confirm.input}</dd>
					<dt>{email.label}</dt> <dd>{email.input}</dd>
					<dt>{birthday.label}</dt> <dd>{birthday.input}</dd>
					<dt>{image.label}</dt> <dd>{image.input} {imagethumb}</dd>
				</dl>
			</div>

			<div>
				<p>{logo.label}: {logo.input}</p>
				<p>{imagethumb}</p>
			</div>

			{btnsubmit}
		<!-- ###EDITION-BOX### end-->
	###REGISTRATIONFORM###


Confirmation Mail Template
--------------------------

The default confirmation mail template can be found in the locallang file in the label
*registration_confirmation_mail*. You can overwrite this by adding ad additional locallang
file by configuring *plugin.tx_t3users_main.locallangFilename.0 = path-to-file*

The default template looks like this where the first line is the subject as we use the method
sendNotifyEmail() from the content object:

.. code-block:: html

	Confirm your registration
	Thank you for your application to become a member of ###SITENAME###.
	Everyone is welcome to browse around our website but you will need to confirm your membership to have
	unrestricted access to all areas.
	To confirm your membership please click on this link: ###MAILCONFIRM_LINKURL###

Confirmation Action
-------------------

This action should be on the page where the confirmation link from the mail above points to.
