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
                    array_push($headers, 'Profit Margin');
                    array_push($headers, 'Total Profit (USD)');
                    array_push($headers, 'Total Profit (CAD)');
                    $output .= '<th>Profit Margin</th><th>Total Profit (USD)</th><th>Total Profit (CAD)</th>';
                } else {
                    for ($i = 0; $i < count($headers); $i++) {
                        $output .= '<td>';

                        $productPrice = $csvRow[array_search('price', $headers)];
                        $productCost = $csvRow[array_search('cost', $headers)];
                        $productQuantity = $csvRow[array_search('qty', $headers)];

                        if ($headers[$i] == 'Profit Margin') {
                            $output .= $this->get_profit_margin($productPrice, $productQuantity, $productCost);
                        } else if ($headers[$i] == 'Total Profit (USD)') {
                            $output .= $this->get_total_profit_usd($productPrice, $productQuantity, $productCost);
                        } else if ($headers[$i] == 'Total Profit (CAD)') {
                            $output .= 'N/A';
                        } else {
                            $output .= $csvRow[$i];
                        }

                        $output .= '</td>';
                    }
                }
                $row++;
                $output .= '</tr>';
            }

            $output .= '</table>';

            return $output;
        }

        function get_profit_margin($productPrice, $productQuantity, $productCost) {
            $totalProfit = $productPrice * $productQuantity;
            $totalCost = $productCost * $productQuantity;
            return (($totalProfit - $totalCost) / $totalCost) * 100;
        }

        function get_total_profit_usd($productPrice, $productQuantity, $productCost) {
            return ($productPrice * $productQuantity) - ($productCost * $productQuantity);
        }

        function get_total_profit_cad() {

        }

    }