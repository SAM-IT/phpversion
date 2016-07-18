<?php
namespace app\helpers;
class DomTableHelper {

    public static function parseTable(\DOMNode $table)
    {
        $rows = [];
        $headers = [];
        /** @var \DOMElement $row */
        foreach($table->getElementsByTagName('tr') as $row) {
            if (empty($headers)) {
                $headers = self::parseRow($row);
            } else {
                if ([] !== $fields = self::parseRow($row)) {
                    if (count($headers) === count($fields)) {
                        $rows[] = array_combine($headers, $fields);
                    } else {
                        $rows[] = $fields;
                    }
                }
            }

        }

        return $rows;

    }

    protected static function parseRow(\DOMNode $row)
    {
        $fields = [];
        /** @var \DOMElement $cell */
        foreach($row->childNodes as $cell) {
            $fields[] = trim($cell->textContent);
        }
        return $fields;
    }
}