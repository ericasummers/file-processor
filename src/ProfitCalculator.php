<?php
    class ProfitCalculator {
        private $csv_table_array;

        function __construct($file) {
            $this->csv_table_array = fgetcsv($file);
        }

        function getCSVArray() {
            return $this->csv_table_array;
        }
    }