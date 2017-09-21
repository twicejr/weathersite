<?php

class kanbeterdb001 extends DbMigrate
{

    public function change()
    {
        $this->table('afbij')
            ->addColumn('date', 'date')
            ->addColumn('name', 'string', array('null' => true))
            ->addColumn('account', 'string', array('null' => true))
            ->addColumn('issuer', 'string', array('null' => true))
            ->addColumn('amount', 'integer', array('null' => true))
            ->addColumn('comments', 'text', array('null' => true))
            ->save()
        ;
        
        ModelEav::setupDbmigrateTables($this, 'afbij');
        
        $this->table('afbijimportsetting')
            ->addColumn('column_index', 'integer', array('null' => true))
            ->addColumn('column_nameindex', 'string', array('null' => true, 'length' => 100))
            ->addColumn('eavattributegroup_id', 'integer', array('null' => true))
            ->addColumn('rule_import', 'boolean', array('null' => true))
            ->addColumn('rule_keyfield', 'boolean', array('null' => true))
            ->addColumn('rule_filter', 'text')
            ->addColumn('rule_match', 'text')
            ->addColumn('rule_exclude', 'text')
            ->addColumn('rule_fail', 'boolean', array('null' => true))
            ->addColumn('rule_eavattribute_id', 'integer', array('null' => true))
            ->addColumn('rule_column', 'text', array('null' => true))
            ->addColumn('rule_lang', 'text', array('null' => true))
            ->addColumn('rule_transform', 'text', array('null' => true))
            ->addForeignKey('rule_eavattribute_id', 'eavattribute', 'id', array('delete' => 'cascade'))
            ->addForeignKey('eavattributegroup_id', 'eavattributegroup', 'id', array('delete' => 'set_null'))
            ->addIndex(array('column_index', 'eavattributegroup_id',), array('unique' => true))
            ->addIndex(array('column_nameindex', 'eavattributegroup_id',), array('unique' => true))
            ->save()
        ;
    }

}
