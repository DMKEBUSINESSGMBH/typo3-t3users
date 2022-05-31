Show FE Users
=============

This action can be selected in the flexform of plugin providing a detail view of a FE user. It is a rn\_base plugin which means you can use all features like link configuration etc. of rn\_base.

The template can be configured in the flexform in the tab user information or through TypoScript in the constants:

~~~~ {.sourceCode .ts}
plugin.tx_t3users.feuserdetailsTemplate = ...
~~~~

or in the setup:

~~~~ {.sourceCode .ts}
plugin.tx_t3users_main.feuserdetailsTemplate = ...
~~~~

Example Template
----------------

The extension ships with a default template (EXT:t3users/Resources/Private/Templates/feuserlist.html). Check the subpart *\#\#\#FEUSER\_DETAIL\#\#\#*. It looks like this:

~~~~ {.sourceCode .html}
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
~~~~

Markers for User starting with *FEUSER\_*. Append any database column name in uppercase letters. Examples:

-   FEUSER\_USERNAME
-   FEUSER\_EMAIL
-   FEUSER\_PHONE

Show current logged in user
---------------------------

Can be configured in the flexform in the tab user information or through TypoScript:

~~~~ {.sourceCode .ts}
plugin.tx_t3users_main.feuserdetails.currentUser = 1
~~~~

Static User
-----------

Can be configured in the flexform in the tab user information or through TypoScript:

~~~~ {.sourceCode .ts}
plugin.tx_t3users_main.feuserdetails.staticUser = 123
~~~~

User not found message
----------------------

You can configured the message in TypoScript in the path plugin.tx\_t3users\_main.feuserdetails.nouser or with following locallang key: feuserdetails.nouser

A custom locallang file can be added like this in TypoScript configuration which overwrites the contained keys:

~~~~ {.sourceCode .ts}
plugin.tx_t3users_main.locallangFilename {
        0 = path to custom locallang
}
~~~~

Links
-----

Links, like \#\#\#FEUSER\_LISTLINK\#\#\#back to list\#\#\#FEUSER\_LISTLINK\#\#\# can be configured fully through TypoScript. It's a rn\_base feature and the TypoScript Configuration path is as follows (check rn\_base for all configuration possibilities):

~~~~ {.sourceCode .ts}
### default global
lib.t3users.feuser {
            links {
                   details.pid = {$plugin.tx_t3users.feuserdetailsPage}
                   list.pid = {$plugin.tx_t3users.feuserlistPage}
            }
}

### or directly in the plugin
plugin.tx_t3users_main.feuserdetails.feuser.links ...
~~~~

If you just want to edit the pids you can configure them in TypoScript constants editor. Or you use the flexform in the tab user information.
