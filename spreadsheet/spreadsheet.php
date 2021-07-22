<?php
require_once dirname(__FILE__) . '/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as xlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as xlsxWriter;

class SheetCell {
    public $handler = null;

    public function __construct($value) {

    }

    public function clone() {

    }
}
class SheetItem {
    public $handler = null;
    public function clone(){

    }
}

class SheetPage {
    public $handler = null;
    public function clone() {

    }

    public function putCell($cell,$at) {

    }

    public function cellAt(){

    }
}

class SheetDocument {
    public $document = null;
    public function __construct($path=null) {

        if(!$path) {
            $this->document = new Spreadsheet();
        } else {
            $this->load($path);
        }
    }
    public function clone() {

    }

    public function load($path) {
        $reader = new xlsxReader();
        $this->document = $reader->load($path);
    }

    public function save($path) {
        $writer = new xlsxWriter($this->document);
        $writer->save($path);
    }
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', '入账');
$sheet->setCellValue('B1', '出账');
$sheet->setCellValue('C1', '余额');
$writer = new xlsxWriter($spreadsheet);
$writer->save('../spreadsheet.xlsx');
