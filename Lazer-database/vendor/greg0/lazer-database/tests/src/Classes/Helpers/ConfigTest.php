<?php

declare(strict_types=1);

namespace Lazer\Test\Classes\Helpers;

use Lazer\Classes\Helpers\Config;
use Lazer\Test\VfsHelper\Config as TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-04-03 at 16:03:47.
 */
class ConfigTest extends TestCase {

    use TestHelper;

    /**
     * @var Config
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->setUpFilesystem();
        $this->object = Config::table('users');
    }

    /**
     * @covers \Lazer\Classes\Helpers\Config::getKey()
     */
    public function testGetKey()
    {
        $this->assertIsInt($this->object->getKey('last_id'));
        $this->assertIsObject($this->object->getKey('schema'));
        $this->assertIsArray($this->object->getKey('schema', true));
    }

    /**
     * @covers \Lazer\Classes\Helpers\Config::fields
     */
    public function testFieldIdExists()
    {
        $this->assertContains('id', $this->object->fields());
    }

    /**
     * @covers \Lazer\Classes\Helpers\Config::relations
     */
    public function testGetMultipleRelations()
    {
        /* as object */
        $this->assertIsObject($this->object->relations(['comments', 'news']));
        
        /* as array */
        $this->assertIsArray($this->object->relations(['comments', 'news'], true));
    }

    /**
     * @covers \Lazer\Classes\Helpers\Config::relations
     */
    public function testGetSingleRelation()
    {
        $this->assertIsObject($this->object->relations('news'));
    }

    /**
     * @covers \Lazer\Classes\Helpers\Config::lastId
     */
    public function testLastId()
    {
        $this->assertIsInt($this->object->lastId());
    }

}
