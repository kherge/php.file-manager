<?php

namespace KHerGe\File;

use KHerGe\File\Exception\ReadException;
use KHerGe\File\Exception\WriteException;

/**
 * Manages the contents of a file as rows of comma separated values.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class CSV extends File implements CSVInterface
{
    /**
     * {@inheritdoc}
     */
    public function readCSV(
        $delimiter = ',',
        $enclosure = '"',
        $escape = '\\',
        $length = 0
    ) {
        $values = fgetcsv(
            $this->getStream(),
            $length,
            $delimiter,
            $enclosure,
            $escape
        );

        if (!is_array($values)) {
            throw new ReadException(
                'The file stream could not be read.'
            );
        }

        if ((1 === count($values)) && (null === $values[0])) {
            return null;
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function writeCSV(
        array $values,
        $delimiter = ',',
        $enclosure = '"',
        $escape = '\\'
    ) {
        $wrote = fputcsv(
            $this->getStream(),
            $values,
            $delimiter,
            $enclosure,
            $escape
        );

        if (false === $wrote) {
            throw new WriteException(
                'The comma separated values could not be written to the file stream.'
            );
        }
    }
}
