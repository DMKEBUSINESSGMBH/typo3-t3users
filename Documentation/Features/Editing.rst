.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _editing:

Editing
=======

The editing of the logged in feuser can simply be selected in the flexform. You can either provide a form
to directly let the user edit his data or alternatively send a confirmation mail before saving the
edited data.


Flexform Options
----------------

**Mode**

Either *normal* which means the user can save his data directly. Or checking the double-opt-in
confirmation link using the data to update provided from the link. If you check use double-opt-in
below you should provide an action on the page for the double-opt-in check with the mode set to
checking the double-opt-in.

alternative TypoScript path: plugin.tx_t3users_main.feuseredit.mode (flexform value has precedence)

Either *normal* or *check*

**don't ignore non TCA field**

If checked non TCA fields like deleted or hidden are accepted.

alternative TypoScript path: plugin.tx_t3users_main.feuseredit.enableNonTcaColumns (flexform value has precedence)

**through double-opt-in**

Should the data not be saved directly but send with a confirmation mail? This can be useful
if you want to let the user change his email in a safe way.

alternative TypoScript path: plugin.tx_t3users_main.feuseredit.doubleoptin (flexform value has precedence)

**page where the double-opt-in is checked**

On this page you should provide an action for the double-opt-in check with the mode set to
checking the double-opt-in. If the data should be updated through double-opt-in and this is not
configured the current page is used.

alternative TypoScript path: plugin.tx_t3users_main.feuseredit.doubleoptin.pid (flexform value has precedence)

or constant: plugin.tx_t3users.doubleOptInPage

**XML form file**

The `mkforms <http://typo3.org/extensions/repository/view/mkforms>`_ XML file used for the editing form.

alternative TypoScript path: plugin.tx_t3users_main.feuseredit.formxml (flexform value has precedence)

**view template**

HTML template for the action. You can find an example below.

alternative TypoScript path: plugin.tx_t3users_main.feusereditTemplate (flexform value has precedence)

or constant: plugin.tx_t3users.feusereditTemplate

**redirect**

The page where to redirect after successfully sending the form.

alternative TypoScript path: plugin.tx_t3users_main.feuseredit.redirect.pid (flexform value has precedence)

or constant: plugin.tx_t3users.feusereditRedirect

Double-Opt-In Mail
------------------

To use the double-opt-in mail you need to have `mkmailer <http://typo3.org/extensions/repository/view/mkmailer>`_
installed and configured.

You then have to create the mkmailer template with the key *t3users_confirmdatachange*.

The template could have the following HTML part. You can output all the user data by appending the marker
"###FEUSER_...###" with the desired database field.

.. code-block:: HTML

   Dear ###FEUSER_USERNAME###,
   please click ###MAILCONFIRM_LINK###here###MAILCONFIRM_LINK### to update your data.

   or copy this link to your browser: ###MAILCONFIRM_LINKURL###

Form XML
--------

The XML file is configured through TypoScript in the path *plugin.tx_t3users_main.feuseredit.formxml*
or in the plugin and has to be a `mkforms <http://typo3.org/extensions/repository/view/mkforms>`_
XML file.
The default one (EXT:t3users/forms/feuser_edit.xml) looks like this:

.. code-block:: xml

   <?xml version="1.0" encoding="UTF-8"?>
   <formidable version="0.7.0">
     <meta>
       <name>Editing form for feusers</name>
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
                  <!-- this is the action class so tx_t3users_actions_EditFeUser -->
                  <extension>this</extension>
                  <method>handleBeforeUpdateDB</method>
                 </userobj>
              </beforeinsertion>
              <afterinsertion>
                 <userobj>
                  <!-- this is the action class so tx_t3users_actions_EditFeUser -->
                  <extension>this</extension>
                  <method>handleUpdateDB</method>
                 </userobj>
              </afterinsertion>
            </process>
         </datahandler:DB>
      </control>

      <elements>
         <renderlet:BOX name="EDITION-BOX">
            <childs>
               <renderlet:PASSWORD name="password123">
                  <label>LLL:EXT:t3users/locallang.php:label_form_password</label>
               </renderlet:PASSWORD>

               <renderlet:PASSWORD name="password123_confirm">
                  <label>LLL:EXT:t3users/locallang.php:label_form_password_confirm</label>
                  <renderonly>true</renderonly>
                  <confirm>EDITION-BOX__password123</confirm>
                  <validators>
                     <validator:STANDARD>
                        <sameas>
                           <value>EDITION-BOX__password123</value>
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

               <renderlet:SUBMIT name="btnsubmit" label="Speichern"/>
            </childs>
         </renderlet:BOX>
      </elements>
   </formidable>

View Template
-------------

This is an example template which renders only the form from above:

.. code-block:: html

   ###FEUSER_EDIT###
      ###FORM###
   ###FEUSER_EDIT###
