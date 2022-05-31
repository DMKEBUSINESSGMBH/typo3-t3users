<?php

defined('TYPO3') or exit();

call_user_func(function () {
    // list static templates in templates selection
    \Sys25\RnBase\Utility\Extensions::addStaticFile('t3users', 'static/ts/', 'FE User Management');
});
