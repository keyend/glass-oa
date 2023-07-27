<?php
namespace mashroom;

class Excel
{
    private $excel;

    public function output()
    {
        $buffer = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $buffer->save('php://output');
        die;
    }

    /**
     * 导出为EXCEL
     *
     * @param array $list
     * @param array $options
     * @return void
     */
    public function excel($list = [], $options = [])
    {
        static $excel;
        static $beginAscll = 65;
        static $prefix = '';
        static $prefixIndex = 0;
        static $stage = 0;
        static $rowIndex = 1;
        static $cellIndex = 1;
        static $drawing = null;

        if($excel == null) {
            if(!isset($options['headers'])) {
                throw new \Exception('Export failed of options invalid.');
            }

            $this->excel = new \PHPExcel();
            $excel = $this->excel;

            $options['title'] = !isset($options['title']) ? 'DEFAULT' : $options['title'];
    
            $excel->setActiveSheetIndex(0);
            $excel->getActiveSheet()->setTitle($options['title']);
            $excel->getActiveSheet()->getRowDimension($rowIndex)->setRowHeight(32);

            foreach($options['headers'] as $header) {
                if ($stage > 25 || $stage > 51) {
                    $prefix = chr($beginAscll + $prefixIndex); //AA AB AC AE
                    $prefixIndex += 1;
                    $stage = 0;
                }

                $id = $prefix . chr($stage + $beginAscll); // A B C D E
                $rowId = $id . $rowIndex;

                $excel->getActiveSheet()->setCellValue($id . $rowIndex, $header['title']);
                $excel->getActiveSheet()->getColumnDimension($id)->setWidth($header['width']);

                if ($header['type'] === 'id') {
                    $excel->getActiveSheet()->getStyle($rowId)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                } else {
                    $excel->getActiveSheet()->getStyle($rowId)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                }

                $excel->getActiveSheet()->getStyle($rowId)->getFill()->applyFromArray(array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array(
                        'rgb' => 'F0E7DB'
                    )
                ));
                $excel->getActiveSheet()->getStyle($rowId)->applyFromArray(array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                ));

                $stage += 1;
            }

            $filename = $options['title'] . '_' . date('ymdHis') . '.xlsx';

            header("Pragma: public");
            header("Expires: 0");
            header('Cache-Control: max-age=0');
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="' . $filename . '"');
            header("Content-Transfer-Encoding:binary");
        }

        foreach($list as $i => $row) {
            $rowIndex += 1;
            $prefix = '';
            $prefixIndex = 0;
            $stage = 0;
            $rowImage = false;

            foreach($options['headers'] as $mapper) {
                $key = $mapper['field'];
                $image = false;
                $drawing = null;

                if ($stage === 26 || $stage === 52) {
                    $prefix = chr($beginAscll + $prefixIndex); //AA AB AC AE
                    $prefixIndex += 1;
                    $stage = 0;
                }

                $id = $prefix . chr($stage + $beginAscll);  // A B C D E
                $rowId = $id . $rowIndex;

                if ($mapper['type'] != 'id') {
                    $excel->getActiveSheet()->getStyle($rowId)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                } else {
                    $excel->getActiveSheet()->getStyle($rowId)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }

                if ($mapper['type'] == 'id') {
                    $value = $rowIndex - 1;
                } elseif($mapper['type'] == 'money') {
                    $value = "¥".number_format(floatval($row[$key]) / 100, 2, '.', '');
                } elseif($mapper['type'] == 'numeric') {
                    $value = "\t".$row[$key];
                } elseif($mapper['type'] == 'date') {
                    $value = $row[$key];
                    if (is_numeric($value)) {
                        $value = date('Y-m-d H:i', $value);
                    }
                    $value = "\t".$value;
                } elseif($mapper['type'] == 'qrcode') {
                    $image = true;
                    $value = m('qrcode')->createQrcode($row[$key]);
                    $values = [$value];
                } elseif($mapper['type'] == 'link') {
                    $excel->getActiveSheet()->getCell($rowId)->getHyperlink()->setUrl($value);
                    $value = strip_tags($row[$key]);
                } elseif($mapper['type'] == 'text') {
                    $value = strip_tags($row[$key]);
                } elseif($mapper['type'] == 'image') {
                    $values = explode(",", $row[$key]);
                    $image = true;
                } else {
                    $value = $row[$key];
                }

                if ($image) {
                    foreach($values as $image) {
                        if (!empty($image)) {
                            $localimage = str_replace($_W['siteroot'], IA_ROOT . '/', $image);

                            if (file_exists($localimage)) {
                                $drawing = new \PHPExcel_Worksheet_Drawing();
                                $drawing->setPath($localimage);
                                $drawing->setWidth(50);
                                $drawing->setHeight(50);
                                $drawing->setCoordinates($rowId);
                                $drawing->setWorksheet($excel->getActiveSheet());
                            } else {
                                $excel->getActiveSheet()->getCell($rowId)->getHyperlink()->setUrl($image);
                                $value = $image;
                            }
                        }

                        break;
                    }

                    // 如果已存在画图，则提前结束
                    if ($drawing != null) {
                        $stage += 1;
                        $rowImage = true;
                        continue;
                    }
                }

                $excel->getActiveSheet()->setCellValue($rowId, $value);
                $value = "";

                $stage += 1;
            }

            $excel->getActiveSheet()->getRowDimension($rowIndex)->setRowHeight($rowImage ? 50 : 26);
        }

        if (empty($list)) {
            return $this->output();
        }

        return $this;
    }
}