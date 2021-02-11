<?php

defined('TYPO3_MODE') or exit();

call_user_func(function () {
    // list static templates in templates selection
    tx_rnbase_util_Extensions::addStaticFile('t3users', 'static/ts/', 'FE User Management');
});
