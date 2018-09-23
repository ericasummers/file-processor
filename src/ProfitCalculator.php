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
                // Header Row setting up columns
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
                // All other data rows
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
                            $output .= $this->get_total_profit_cad($productPrice, $productQuantity, $productCost);
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
            return round((($totalProfit - $totalCost) / $totalCost) * 100, 2) . '%';
        }

        function get_total_profit_usd($productPrice, $productQuantity, $productCost) {
            return number_format(($productPrice * $productQuantity) - ($productCost * $productQuantity), 2) . ' $';
        }

        function get_total_profit_cad($productPrice, $productQuantity, $productCost) {
            $total_profit_in_usd = $this->get_total_profit_usd($productPrice, $productQuantity, $productCost);
            $currency_exchange_rate = $this->get_currency_usd_to_cad_rate();
            return number_format($total_profit_in_usd * $currency_exchange_rate, 2) . ' C$';
        }

        function get_currency_usd_to_cad_rate() {
            $curl = curl_init('http://free.currencyconverterapi.com/api/v5/convert?q=USD_CAD&compact=y');
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $json_result = curl_exec($curl);
            $result = json_decode($json_result, true);
            curl_close($curl);

            return $result['USD_CAD']['val'];
        }

    }