<?php
    class ProfitCalculator {
        private $csv_file;
        private $summedUpPrices;
        private $totalQty;
        private $summedUpCosts;
        private $summedUpProfitMargin;
        private $totalProfitUSD;
        private $totalProfitCAD;
        private $rows;


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
                        $productPrice = $csvRow[array_search('price', $headers)];
                        $productCost = $csvRow[array_search('cost', $headers)];
                        $productQuantity = $csvRow[array_search('qty', $headers)];

                        if ($headers[$i] == 'Profit Margin') {
                            $profitMargin = $this->get_profit_margin($productPrice, $productQuantity, $productCost);
                            $this->summedUpProfitMargin = $this->summedUpProfitMargin + $profitMargin;
                            $output .= $this->set_color_class($profitMargin) . $profitMargin . '%';
                        } else if ($headers[$i] == 'Total Profit (USD)') {
                            $total_profit_usd = $this->get_total_profit_usd($productPrice, $productQuantity, $productCost);
                            $this->totalProfitUSD = $this->totalProfitUSD + $total_profit_usd;
                            $output .= $this->set_color_class($total_profit_usd) . $total_profit_usd . ' $';
                        } else if ($headers[$i] == 'Total Profit (CAD)') {
                            $total_profit_cad = $this->get_total_profit_cad($productPrice, $productQuantity, $productCost);
                            $this->totalProfitCAD = $this->totalProfitCAD + $total_profit_cad;
                            $output .= $this->set_color_class($total_profit_cad) . $total_profit_cad . ' C$';
                        } else if ($headers[$i] == 'qty') {
                            $this->totalQty = $this->totalQty + $csvRow[$i];
                            $output .= $this->set_color_class($csvRow[$i]) . $csvRow[$i];
                        } else {
                            if ($headers[$i] == 'price') {
                                $this->summedUpPrices = $this->summedUpPrices + $csvRow[$i];
                            } else {
                                $this->summedUpCosts = $this->summedUpCosts + (int) $csvRow[$i];
                            }
                            $output .= '<td>' . $csvRow[$i];
                        }

                        $output .= '</td>';
                    }
                }
                $row++;
                $output .= '</tr>';
            }
            
            //Add final totals to last row of table
            $this->rows = $row - 2;
            $output .= '<tr>';
            for ($j = 0; $j < count($headers); $j++) {
                $output .= '<td class="totals">';
                if ($headers[$j] == 'Profit Margin') {
                    $output .= round($this->summedUpProfitMargin / $this->rows, 2) . '%';
                }
                if ($headers[$j] == 'Total Profit (USD)') {
                    $output .= number_format($this->totalProfitUSD, 2) . ' $';
                }
                if ($headers[$j] == 'Total Profit (CAD)') {
                    $output .= number_format($this->totalProfitCAD, 2) . ' C$';
                }
                if ($headers[$j] == 'qty') {
                    $output .= $this->totalQty;
                }
                if ($headers[$j] == 'price') {
                    $output .= number_format($this->summedUpPrices / $this->rows, 2);
                }
                if ($headers[$j] == 'cost') {
                    $output .= number_format($this->summedUpCosts / $this->rows, 2);
                }
                $output .= '</td>';

            }
            $output .= '</tr>';

            $output .= '</table>';

            return $output;
        }

        function get_profit_margin($productPrice, $productQuantity, $productCost) {
            $totalProfit = $productPrice * $productQuantity;
            $totalCost = $productCost * $productQuantity;
            return round((($totalProfit - $totalCost) / $totalCost) * 100, 2);
        }

        function get_total_profit_usd($productPrice, $productQuantity, $productCost) {
            return number_format(($productPrice * $productQuantity) - ($productCost * $productQuantity), 2);
        }

        function get_total_profit_cad($productPrice, $productQuantity, $productCost) {
            $total_profit_in_usd = $this->get_total_profit_usd($productPrice, $productQuantity, $productCost);
            $currency_exchange_rate = $this->get_currency_usd_to_cad_rate();
            return number_format($total_profit_in_usd * $currency_exchange_rate, 2);
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

        function set_color_class($number) {
            if ($number > 0) {
                return '<td class="positive">';
            } else {
                return '<td class="negative">';
            }
        }

    }