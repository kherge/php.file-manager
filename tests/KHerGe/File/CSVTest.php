<?php

namespace Test\KHerGe\File;

use KHerGe\File\CSV;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the CSV file manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\File\CSV
 */
class CSVTest extends TestCase
{
    /**
     * The CSV file manager.
     *
     * @var CSV
     */
    private $manager;

    /**
     * Verify that comma separated values can be read.
     */
    public function testReadTheCommaSeparatedValues()
    {
        $this->manager->write(PHP_EOL);
        $this->manager->write('a,"beta test",123,"a ""gamma"" test"');
        $this->manager->seek(0);

        self::assertNull(
            $this->manager->readCSV(),
            'The empty record did not return `null`.'
        );

        self::assertEquals(
            ['a', 'beta test', '123', 'a "gamma" test'],
            $this->manager->readCSV(),
            'The expected comma separated values were not returned.'
        );
    }

    /**
     * Verify that comma separated values can be written.
     */
    public function testWriteTheCommaSeparatedValues()
    {
        $this->manager->writecsv(['a', 'beta test', 123, 'a "gamma" test']);
        $this->manager->seek(0);

        self::assertEquals(
            'a,"beta test",123,"a ""gamma"" test"' . PHP_EOL,
            $this->manager->read(),
            'The expected comma separated values were not written.'
        );
    }

    /**
     * Creates a new CSV file manager.
     */
    protected function setUp()
    {
        $this->manager = new CSV('php://memory', 'w+');
    }
}
