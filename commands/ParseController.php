<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Category;
use app\models\Product;
use app\models\ProductPrice;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;

/**
 *
 */
class ParseController extends Controller
{
    /**
     * Парсим csv-файл с данными отсюда https://docs.google.com/spreadsheets/d/10En6qNTpYNeY_YFTWJ_3txXzvmOA7UxSCrKfKCFfaRw/edit?gid=1428297429#gid=1428297429
     * команда: php yii parse/from-file "/home/vlad/Загрузки/Dev Test 2022 MASTER BUDGET - MA.csv"
     * @param string $file_path path to file with filename
     * @return int Exit code
     */
    public function actionFromFile($file_path = '')
    {
        if (!file_exists($file_path)) {
            echo "File not exist. Check path: $file_path\n"; exit;
        }


        // читаем
        $aRows = [];
        $i = 0;
        $handle = fopen($file_path, "r");
        while (($row = fgetcsv($handle, 500, ",")) !== FALSE) {
            $i++;
            if($i < 3) {
                continue;
            }
            if($row[0] == "" && $row[1] == "") {
                continue;
            }
            if($row[0] == "CO-OP") {
                break;
            }
            $aRows[] = $row;
        }
        fclose($handle);


        // проверка наличия каких-то данных
        if(count($aRows) < 6) {
            echo "Not enough data\n"; exit;
        }
        // проверка формата колонок дат
        $date_row = strtolower(implode(',', array_slice($aRows[0], 0, 14)));
        if(!preg_match('/,january,february,march,april,may,june,july,august,september,october,november,december,total/', $date_row)) {
            echo "Row №3 has wrong format, expected: ,january,february,march,april,may,june,july,august,september,october,november,december,total...\n"; exit;
        }
        // проверяем наличие колонки Total
        $has_total = false;
        foreach ($aRows as $aRow) {
            if(strtolower($aRow[0]) == 'total') {
                $has_total = true;
                break;
            }
        }
        if(!$has_total) {
            echo "Total not exist\n"; exit;
        }


        // извлекаем данные: названия категорий, названия продуктов, цена продукта по месяцам.
        // Итог за год для продукта - не нужен
        // total для категории по месяца и итог за год для категории - не нужны эти данные т.к. их можно вычислить по данным из базы
        // строки "Internet Total" и "PVR on Internet" пропускаю, т.к. не они похоже не нужны...
        $year = Date("Y");
        $i = 0;
        $are_inside_category_table = false;

        $aCategories = [];
        //$aCatsProducts = [];
        $aCatsProducts = [];
        $aCatsProductsDatesPrices = [];
        $cat_name = '';
        foreach ($aRows as $aRow) {
            $i++;
            if($i == 1) {
                continue;
            }
            if(!$are_inside_category_table && strlen(trim($aRow[0])) > 0 && strlen(trim($aRow[1])) == 0) { // категория
                $are_inside_category_table = true;
                $cat_name = trim($aRow[0]);
                $aCategories[$cat_name] = $cat_name;
                continue;
            }
            if($are_inside_category_table && trim($aRow[0]) == "Total") {
                $are_inside_category_table = false;
                continue;
            }
            if($are_inside_category_table && strlen(trim($aRow[0])) > 0) { // продукт

                $product_name = trim($aRow[0]);
                $aCatsProducts[$cat_name][$product_name] = $product_name;

                for($m = 1; $m <= 12; $m++) {
                    $price = trim($aRow[$m]);
                    if($price == "") {
                        continue;
                    }
                    $price = floatval(str_replace(["$", ","], "", $price));
                    if($price > 0) { // цены равные 0.00 не нужны в базе
                        $aCatsProductsDatesPrices[$cat_name][$product_name][$year.'-'.($m < 10 ? '0' : '').$m.'-01'] = $price;
                    }
                }
            }
        }

        // удаляем категории из БД которых больше нет в excel
        $categories = Category::find()->all();
        if(count($categories) > 0) {
            foreach ($categories as $category) {
                if(!isset($aCategories[$category->name])) { // категория из БД не существует в пришедших данных, значит удаляем из БД категорию и связанные продукты с ценами
                    $products = Product::find()->where(['cat_id' => $category->id])->all();
                    if(count($products) > 0) {
                        ProductPrice::deleteAll(['IN', 'product_id', ArrayHelper::getColumn($products, 'id')]);
                        Product::deleteAll(['IN', 'id', ArrayHelper::getColumn($products, 'id')]);
                    }
                    $category->delete();
                }
            }
        }
        // добавляем новые категории появившиеся в excel
        $aDBCategories = ArrayHelper::map(Category::find()->all(), 'name', 'name');
        foreach ($aCategories as $cat_name) {
            if(!isset($aDBCategories[$cat_name])) {
                $category = new Category();
                $category->name = $cat_name;
                if(!$category->save()) {
                    echo "Can`t save category ".$category->name."\n"; exit;
                }
            }
        }

        // удаляем продукты в БД которых больше нет в excel
        $products = Product::find()->all();
        $aDBCategories = ArrayHelper::map(Category::find()->all(), 'id', 'name');
        if(count($products) > 0) {
            foreach ($products as $product) {
                $cat_name = $aDBCategories[$product->cat_id];
                if(!isset($aCatsProducts[$cat_name][$product->name])) { // продукт из БД не существует в пришедших данных, значит удаляем продукт и связанные цены
                    ProductPrice::deleteAll(['product_id' => $product->id]);
                    $product->delete();
                }
            }
        }
        // добавляем новые продукты появившиеся в excel
        $aDBCatsProducts = [];
        if(count($products) > 0) {
            foreach ($products as $product) {
                $cat_name = $aDBCategories[$product->cat_id];
                $aDBCatsProducts[$cat_name][$product->name] = true;
            }
        }
        foreach ($aCatsProducts as $cat_name => $aCatProducts) {
            foreach ($aCatProducts as $product_name) {
                if(!isset($aDBCatsProducts[$cat_name][$product_name])) {
                    $product = new Product();
                    $product->cat_id = array_flip($aDBCategories)[$cat_name];
                    $product->name = $product_name;
                    if(!$product->save()) {
                        echo "Can`t save product ".$product->name."\n"; exit;
                    }
                }
            }
        }

        // удаляем цены на продукт месяца которых больше нет в excel, и изменяем цены если изменились
        $products_prices = ProductPrice::find()->all();
        $aProducts = ArrayHelper::index(Product::find()->all(), 'id');
        if(count($products_prices) > 0) {
            foreach ($products_prices as $product_price) {
                $product = $aProducts[$product_price->product_id];
                $cat_name = $aDBCategories[$product->cat_id];
                if(!isset($aCatsProductsDatesPrices[$cat_name][$product->name][$product_price->date])) { // удаляем product_price
                    $product_price->delete();
                }else {
                    $new_price = $aCatsProductsDatesPrices[$cat_name][$product->name][$product_price->date];
                    if($product_price->price != $new_price) {
                        $product_price->price = $new_price;
                        if(!$product_price->save()) {
                            echo "Can`t update product price for product «".$product->name."» with price ".$new_price." and date ".$product_price->date." \n"; exit;
                        }
                    }
                }
            }
        }
        // добавляем новые цены появившиеся в excel
        $aProducts = ArrayHelper::index(Product::find()->all(), 'id');
        $products_prices = ProductPrice::find()->all();
        $aDBCatsProductsDatesPrices = [];
        if(count($products_prices) > 0) {
            foreach ($products_prices as $product_price) {
                $product = $aProducts[$product_price->product_id];
                $cat_name = $aDBCategories[$product->cat_id];
                $aDBCatsProductsDatesPrices[$cat_name][$product->name][$product_price->date] = $product_price->price;
            }
        }
        $aProductsNameId = ArrayHelper::map(Product::find()->all(), 'name','id');
        foreach ($aCatsProductsDatesPrices as $cat_name => $aProductsDatesPrices) {
            foreach ($aProductsDatesPrices as $product_name => $aDatesPrices) {
                foreach ($aDatesPrices as $date => $price) {
                    if(!isset($aDBCatsProductsDatesPrices[$cat_name][$product_name][$date])) {
                        $product_price = new ProductPrice();
                        $product_price->product_id = $aProductsNameId[$product_name];
                        $product_price->date = $date;
                        $product_price->price = $price;
                        if(!$product_price->save()) {
                            echo "Can`t save product price for product «".$product->name."» with price ".$product_price->price." and date ".$product_price->date." \n"; exit;
                        }
                    }
                }
            }
        }


        echo "Finish\n";

        return ExitCode::OK;
    }

    /**
     * Алгоритм попроще, здесь не выполняется условие задачи: "Скрипт должен учитывать, что в данные могут быть внесены изменения, и при следующем запуске
     *  он должен уметь находить эти изменения и вносить коррективы в сохранённые данные."
     * Парсим csv-файл с данными отсюда https://docs.google.com/spreadsheets/d/10En6qNTpYNeY_YFTWJ_3txXzvmOA7UxSCrKfKCFfaRw/edit?gid=1428297429#gid=1428297429
     * команда: php yii parse/from-file-simple "/home/vlad/Загрузки/Dev Test 2022 MASTER BUDGET - MA.csv"
     * @param string $file_path path to file with filename
     * @return int Exit code
     */
    public function actionFromFileSimple($file_path = '')
    {
        if (!file_exists($file_path)) {
            echo "File not exist. Check path: $file_path\n"; exit;
        }


        // читаем
        $aRows = [];
        $i = 0;
        $handle = fopen($file_path, "r");
        while (($row = fgetcsv($handle, 500, ",")) !== FALSE) {
            $i++;
            if($i < 3) {
                continue;
            }
            if($row[0] == "" && $row[1] == "") {
                continue;
            }
            if($row[0] == "CO-OP") {
                break;
            }
            $aRows[] = $row;
        }
        fclose($handle);


        // проверка наличия каких-то данных
        if(count($aRows) < 6) {
            echo "Not enough data\n"; exit;
        }
        // проверка формата колонок дат
        $date_row = strtolower(implode(',', array_slice($aRows[0], 0, 14)));
        if(!preg_match('/,january,february,march,april,may,june,july,august,september,october,november,december,total/', $date_row)) {
            echo "Row №3 has wrong format, expected: ,january,february,march,april,may,june,july,august,september,october,november,december,total...\n"; exit;
        }
        // проверяем наличие колонки Total
        $has_total = false;
        foreach ($aRows as $aRow) {
            if(strtolower($aRow[0]) == 'total') {
                $has_total = true;
                break;
            }
        }
        if(!$has_total) {
            echo "Total not exist\n"; exit;
        }


        \Yii::$app->getDb()->createCommand('TRUNCATE `product_prices`')->execute();
        \Yii::$app->getDb()->createCommand('TRUNCATE `products`')->execute();
        \Yii::$app->getDb()->createCommand('TRUNCATE `categories`')->execute();

        // извлекаем данные: названия категорий, названия продуктов, цена продукта по месяцам.
        // Итог за год для продукта - не нужен
        // total для категории по месяца и итог за год для категории - не нужны эти данные т.к. их можно вычислить по данным из базы
        // строки "Internet Total" и "PVR on Internet" пропускаю, т.к. не они похоже не нужны...
        $year = Date("Y");
        $i = 0;
        $are_inside_category_table = false;
        foreach ($aRows as $aRow) {
            $i++;
            if($i == 1) {
                continue;
            }
            if(!$are_inside_category_table && strlen(trim($aRow[0])) > 0 && strlen(trim($aRow[1])) == 0) { // категория
                $are_inside_category_table = true;

                $cat = new Category();
                $cat->name = trim($aRow[0]);
                if(!$cat->save()) {
                    echo "Can`t save category ".$cat->name."\n"; exit;
                }
                continue;
            }
            if($are_inside_category_table && trim($aRow[0]) == "Total") {
                $are_inside_category_table = false;
                continue;
            }
            if($are_inside_category_table && strlen(trim($aRow[0])) > 0) { // продукт

                $product = new Product();
                $product->cat_id = $cat->id;
                $product->name = trim($aRow[0]);
                if(!$product->save()) {
                    echo "Can`t save product «".$product->name."»\n"; exit;
                }

                for($m = 1; $m <= 12; $m++) {
                    $price = trim($aRow[$m]);
                    if($price == "") {
                        continue;
                    }
                    $price = floatval(str_replace(["$", ","], "", $price));
                    if($price > 0) { // цены равные 0.00 не нужны в базе
                        $product_price = new ProductPrice();
                        $product_price->product_id = $product->id;
                        $product_price->date = $year.'-'.($m < 10 ? '0' : '').$m.'-01';
                        $product_price->price = $price;
                        if(!$product_price->save()) {
                            echo "Can`t save product price with product «".$product->name."» with price ".$price." and date ".$product_price->date." \n"; exit;
                        }
                    }
                }
            }
        }


        echo "Finish\n";

        return ExitCode::OK;
    }
}
