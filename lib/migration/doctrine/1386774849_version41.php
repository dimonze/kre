<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version41 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->removeIndex('seo_text', 'url_index', array(
             'fields' => 
             array(
              0 => 'url',
             ),
             'type' => 'unique',
             ));
    }

    public function down()
    {
        $this->addIndex('seo_text', 'url_index', array(
             'fields' => 
             array(
              0 => 'url',
             ),
             'type' => 'unique',
             ));
    }
}