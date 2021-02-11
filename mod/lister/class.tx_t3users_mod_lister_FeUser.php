<?php

tx_rnbase::load('Tx_Rnbase_Backend_Lister_AbstractLister');

/**
 * Hilfsklassen um nach Landkreisen im BE zu suchen.
 */
class tx_t3users_mod_lister_FeUser extends Tx_Rnbase_Backend_Lister_AbstractLister
{
    /**
     * Liefert die Funktions-Id.
     */
    public function getSearcherId()
    {
        return 'feuser';
    }

    /**
     * Liefert den Service.
     *
     * @return tx_t3users_srv_Base
     */
    protected function getService()
    {
        return tx_t3users_util_ServiceRegistry::getFeUserService();
    }

    /**
     * Returns the repository.
     *
     * @return Tx_Rnbase_Domain_Repository_InterfaceSearch
     */
    protected function getRepository()
    {
        return $this->getService();
    }

    /**
     * Returns the complete search form.
     *
     * @return  string
     */
    protected function addMoreFields(&$data, &$options)
    {
        $this->options['pid'] = $this->getModule()->getPid();
        if (isset($this->options['pid'])) {
            $options['pid'] = $this->options['pid'];
        }
        $selector = $this->getSelector();

        $out = $this->buildFilterTable($data);

        return $out;
    }

    /**
     * Der Selector wird erst erzeugt, wenn er benötigt wird.
     *
     * @return  tx_t3users_mod_util_Selector
     */
    protected function getSelector()
    {
        if (!$this->selector) {
            $this->selector = tx_rnbase::makeInstance('tx_t3users_mod_util_Selector');
            $this->selector->init($this->getModule());
        }

        return $this->selector;
    }

    /**
     * (non-PHPdoc).
     *
     * @see tx_t3users_mod_searcher_abstractBase::getSearchColumns()
     */
    protected function getSearchColumns()
    {
        return ['FEUSER.uid', 'FEUSER.username', 'FEUSER.first_name',
                'FEUSER.last_name', 'FEUSER.email', 'FEUSER.address',
                'FEUSER.zip', 'FEUSER.company', 'FEUSER.www',
                'FEUSER.telephone', 'FEUSER.city', ];
    }

    /**
     * Liefert die Spalten für den Decorator.
     *
     * @param   tx_t3users_mod_decorator_Base   $oDecorator
     *
     * @return  array
     */
    protected function addDecoratorColumns(
        array &$columns
    ) {
        $sTableAlias = 'FEUSER.';
        $decorator = $this->getDecorator();
        $columns['uid'] = [
            'title' => '',
            'decorator' => $decorator,
            'sortable' => $sTableAlias,
        ];
        $columns['actions'] = [
            'title' => 'label_tableheader_actions',
            'decorator' => $decorator,
        ];
        $columns['username'] = [
            'title' => 'label_tableheader_username',
            'decorator' => $decorator,
            'sortable' => $sTableAlias,
        ];
        $columns['first_name'] = [
            'title' => 'label_tableheader_firstname',
            'decorator' => $decorator,
            'sortable' => $sTableAlias,
        ];
        $columns['last_name'] = [
            'title' => 'label_tableheader_lastname',
            'decorator' => $decorator,
            'sortable' => $sTableAlias,
        ];
        $columns['usergroup'] = [
            'title' => 'label_tableheader_usergroup',
            'decorator' => $decorator,
        ];

        return $columns;
    }

    /**
     * @return tx_rnbase_mod_IDecorator
     */
    protected function getDecoratorClass()
    {
        return 'tx_t3users_mod_decorator_FeUser';
    }

    /**
     * Kann von der Kindklasse überschrieben werden, um weitere Filter zu setzen.
     *
     * @param   array   $fields
     * @param   array   $options
     */
    protected function prepareFieldsAndOptions(array &$fields, array &$options)
    {
        parent::prepareFieldsAndOptions($fields, $options);

        if ($this->options['pid']) {
            $fields['FEUSER.pid'][OP_EQ_INT] = $this->options['pid'];
        }

        // mehr Filter per Hook
        tx_rnbase_util_Misc::callHook(
            't3users',
            'mod_feuser_addFieldsAndOptions',
            [
                'fields' => &$fields,
                'options' => &$options,
                'filter' => $this->getFilter(),
            ],
            $this
        );
    }

    /**
     * Liefert die Daten für das Basis-Suchformular damit
     * das Html gebaut werden kann.
     *
     * @return array
     */
    protected function getSearchFormData()
    {
        $data = parent::getSearchFormData();
        // mehr Filter per Hook
        tx_rnbase_util_Misc::callHook(
            't3users',
            'mod_feuser_addSearchFormData',
            [
                'data' => &$data,
                'module' => $this->getModule(),
                'filter' => $this->getFilter(),
            ],
            $this
        );

        return $data;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/lister/class.tx_t3users_mod_lister_FeUser.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/t3users/mod/lister/class.tx_t3users_mod_lister_FeUser.php'];
}
