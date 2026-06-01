<?php

class ReportExportHelper {
    public static function csv(array $headers, array $rows) {
        $lines = [self::csvLine($headers)];

        foreach ($rows as $row) {
            $values = [];
            foreach ($headers as $key => $label) {
                $values[] = is_array($row) ? ($row[$key] ?? '') : '';
            }
            $lines[] = self::csvLine($values);
        }

        return implode("\n", $lines) . "\n";
    }

    public static function csvLine(array $values) {
        $escaped = array_map(function ($value) {
            $value = (string) $value;
            $value = str_replace('"', '""', $value);
            return '"' . $value . '"';
        }, $values);

        return implode(',', $escaped);
    }
}
