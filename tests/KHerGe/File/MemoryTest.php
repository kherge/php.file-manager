<?php

namespace Test\KHerGe\File;

use KHerGe\File\Memory;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the in memory file stream manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class MemoryTest extends TestCase
{
    /**
     * The in memory file stream manager.
     *
     * @var Memory
     */
    private $manager;

    /**
     * The string contents.
     *
     * @var string
     */
    private $string;

    /**
     * Verify that the string is
     */
    public function testUsingAnInMemoryFileStream()
    {
        self::assertEquals(
            $this->string,
            $this->manager->read(),
            'The contents were not written to the in memory file stream properly.'
        );
    }

    /**
     * Creates a new in memory file stream manager.
     */
    protected function setUp()
    {
        $this->string = 'test';
        $this->manager = new Memory($this->string, false);
    }
}
