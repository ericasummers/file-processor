<?php
    class ProfitCalculator {
        private $csv_file;

        function __construct($file) {
            $this->csv_file = $file;
        }

        function getCSVFile() {
            return $this->csv_file;
        }

        function convertCsvToArray() {
            return array_map('str_getcsv', file($this->csv_file));
        }

        function parseCSV() {
            $csvArray = $this->convertCsvToArray();

            $output = '<table>';
            $headers = [];
            $row = 1;
            foreach($csvArray as $csvRow) {
                $output .= '<tr>';
                if ($row == 1) {
                    foreach($csvRow as $dataCell) {
                        array_push($headers, $dataCell);
                        $output .= '<th>';
                        $output .= $dataCell;
                        $output .= '</th>';
                    }
                } else {
                    foreach($csvRow as $dataCell) {
                        $output .= '<td>';
                        $output .= $dataCell;
                        $output .= '</td>';
                    }
                }
                $row++;
                $output .= '</tr>';
            }

            $output .= '</table>';

            return $output;
        }
    }