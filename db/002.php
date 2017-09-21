<?php

class kanbeterdb002 extends DbMigrate
{

    public function change()
    {
        $this->table('ssetest')
            ->addColumn('message', 'text', array('null' => true))
            ->save()
        ;
        $this->table('ssesess')
            ->addColumn('sessid', 'string', array('null' => true))
            ->addColumn('started', 'biginteger', array('null' => true, 'length' => 15))
            ->save()
        ;
    }

}
