<?php

namespace KHerGe\File;

use KHerGe\File\Exception\ReadException;
use KHerGe\File\Exception\WriteException;

/**
 * Defines the public interface for a CSV file manager.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
interface CSVInterface
{
    /**
     * Reads a row of comma separated values from the file.
     *
     * The `readCSV()` method will read a line from the file as a row of comma
     * separated values. If an empty row is read, `null` is returned. Otherwise,
     * the separated values are returned as an array. If you know the maximum
     * length of the row (in characters), it is strongly recommended that you
     * provide it to improve performance.
     *
     * ```php
     * $values = $file->readCSV();
     * ```
     *
     * > You may need to check for the end of the file before reading.
     * >
     * > ```php
     * > while (!$file->eof()) {
     * >     $row = $file->readCSV();
     * > }
     * > ```
     *
     * @param string  $delimiter The character used to separate values.
     * @param string  $enclosure The character used to encapsulate values.
     * @param string  $escape    The character used to escape `$enclosure`.
     * @param integer $length    The maximum number of characters per line.
     *
     * @return array|null The parsed values, if any.
     *
     * @throws ReadException If the file could not be read.
     *
     * @link https://secure.php.net/fgetcsv#58124 To understand `$escape`.
     */
    public function readCSV(
        $delimiter = ',',
        $enclosure = '"',
        $escape = '\\',
        $length = 0
    );

    /**
     * Writes a row of comma separated values from the file.
     *
     * ```php
     * $file->writeCSV(['a', 2, 'gamma']);
     * ```
     *
     * @param array  $values    The values to write.
     * @param string $delimiter The character used to separate values.
     * @param string $enclosure The character used to encapsulate values.
     * @param string $escape    The character used to escape `$enclosure`.
     *
     * @throws WriteException If the values could not be written.
     */
    public function writeCSV(
        array $values,
        $delimiter = ',',
        $enclosure = '"',
        $escape = '\\'
    );
}
