<?php

/** @var yii\web\View $this */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">Congratulations!</h1>

        <p class="lead">You have successfully created your Yii-powered application.</p>

        <p><a class="btn btn-lg btn-success" href="https://www.yiiframework.com">Get started with Yii</a></p>
    </div>

    <div class="body-content">
        <style type="text/css">
            .excel-table {
                width: 100%;
            }
            .excel-table td {
                border: solid 1px #000;
            }
            .cat td {
                font-weight: bold;
                background: #EEE;
            }
            .cat-total td {
                font-weight: bold;
            }
        </style>
        <table class="excel-table">
            <tr>
                <th>&nbsp;</th>
                <th>January</th>
                <th>February</th>
                <th>March</th>
                <th>April</th>
                <th>May</th>
                <th>June</th>
                <th>July</th>
                <th>August</th>
                <th>September</th>
                <th>October</th>
                <th>November</th>
                <th>December</th>
                <th>TOTAL</th>
            </tr>
            <?php foreach ($cats as $cat) { ?>
                <tr class="cat"><td><?= $cat->name ?></td><td colspan="13">&nbsp;</td></tr>
                <?php
                $aCatMonthsTotals = [
                        0,0,0,0,0,0,0,0,0,0,0,0
                ];
                $cat_total = 0;
                $aCatProducts = isset($aCatsProducts[$cat->id]) ? $aCatsProducts[$cat->id] : [];
                foreach ($aCatProducts as $product) {
                    $aProductPrices = isset($aProductsPrices[$product->id]) ? $aProductsPrices[$product->id] : [];
                    ?>
                    <tr>
                        <td><?= $product->name ?></td>
                        <?php if(count($aProductPrices) > 0) {
                            $aMonthsPrices = [
                                $aProductPrices[date('Y').'-01-01'] ?? 0.00,
                                $aProductPrices[date('Y').'-02-01'] ?? 0.00,
                                $aProductPrices[date('Y').'-03-01'] ?? 0.00,
                                $aProductPrices[date('Y').'-04-01'] ?? 0.00,
                                $aProductPrices[date('Y').'-05-01'] ?? 0.00,
                                $aProductPrices[date('Y').'-06-01'] ?? 0.00,
                                $aProductPrices[date('Y').'-07-01'] ?? 0.00,
                                $aProductPrices[date('Y').'-08-01'] ?? 0.00,
                                $aProductPrices[date('Y').'-09-01'] ?? 0.00,
                                $aProductPrices[date('Y').'-10-01'] ?? 0.00,
                                $aProductPrices[date('Y').'-11-01'] ?? 0.00,
                                $aProductPrices[date('Y').'-12-01'] ?? 0.00,
                            ];
                            // print_r($aMonthsPrices); exit;
                            $product_total = 0;
                            foreach ($aMonthsPrices as $key => $price) {
                                $product_total += $price;
                                $aCatMonthsTotals[$key] += $price;
                                $cat_total += $price;
                            }
                            ?>
                            <td>$<?= $aMonthsPrices[0] ?></td>
                            <td>$<?= $aMonthsPrices[1] ?></td>
                            <td>$<?= $aMonthsPrices[2] ?></td>
                            <td>$<?= $aMonthsPrices[3] ?></td>
                            <td>$<?= $aMonthsPrices[4] ?></td>
                            <td>$<?= $aMonthsPrices[5] ?></td>
                            <td>$<?= $aMonthsPrices[6] ?></td>
                            <td>$<?= $aMonthsPrices[7] ?></td>
                            <td>$<?= $aMonthsPrices[8] ?></td>
                            <td>$<?= $aMonthsPrices[9] ?></td>
                            <td>$<?= $aMonthsPrices[10] ?></td>
                            <td>$<?= $aMonthsPrices[11] ?></td>
                            <td>$<?= $product_total ?></td>
                        <?php }else { ?>
                            <td colspan="13">&nbsp;</td>
                        <?php } ?>
                    </tr>
                <?php } ?>
                <tr class="cat-total">
                    <td>Total</td>
                    <td>$<?= $aCatMonthsTotals[0] ?></td>
                    <td>$<?= $aCatMonthsTotals[1] ?></td>
                    <td>$<?= $aCatMonthsTotals[2] ?></td>
                    <td>$<?= $aCatMonthsTotals[3] ?></td>
                    <td>$<?= $aCatMonthsTotals[4] ?></td>
                    <td>$<?= $aCatMonthsTotals[5] ?></td>
                    <td>$<?= $aCatMonthsTotals[6] ?></td>
                    <td>$<?= $aCatMonthsTotals[7] ?></td>
                    <td>$<?= $aCatMonthsTotals[8] ?></td>
                    <td>$<?= $aCatMonthsTotals[9] ?></td>
                    <td>$<?= $aCatMonthsTotals[10] ?></td>
                    <td>$<?= $aCatMonthsTotals[11] ?></td>
                    <td>$<?= $cat_total ?></td>
                </tr>
                <tr><td colspan="14">&nbsp;</td></tr>
            <?php } ?>
        </table>
        <?php /*
        <div class="row">
            <div class="col-lg-4 mb-3">
                <h2>Heading</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-outline-secondary" href="https://www.yiiframework.com/doc/">Yii Documentation &raquo;</a></p>
            </div>
            <div class="col-lg-4 mb-3">
                <h2>Heading</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-outline-secondary" href="https://www.yiiframework.com/forum/">Yii Forum &raquo;</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Heading</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-outline-secondary" href="https://www.yiiframework.com/extensions/">Yii Extensions &raquo;</a></p>
            </div>
        </div>
        */ ?>
    </div>
</div>
