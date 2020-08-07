<?php

namespace App\Service\Export;


class CsvToArray {

    protected $delimiter;
    protected $text_separator;
    protected $replace_text_separator;
    protected $line_delimiter;

    public function __construct($delimiter = ";", $text_separator = '"', $replace_text_separator = "'", $line_delimiter = "\n")
    {
        $this->delimiter              = $delimiter;
        $this->text_separator         = $text_separator;
        $this->replace_text_separator = $replace_text_separator;
        $this->line_delimiter         = $line_delimiter;
    }


    public function convert($csv, $delimiter = ';', $enclosure = '"', $escape = '\\', $terminator = "\n")
    {
      $r = array();
      $rows = explode($terminator, trim($csv));
      $names = array_shift($rows);
      $names = str_getcsv($names,$delimiter,$enclosure,$escape);
      $nc = count($names);
      foreach ($rows as $row) {
          if (trim($row)) {
              $values = str_getcsv($row,$delimiter,$enclosure,$escape);
             
              if (!$values) $values = array_fill(0,$nc,null);
              if(count($names) != count($values)) {
                throw new \Exception('CsvToArray count fields problem!');
              }
              
              $r[] = array_combine($names,$values);
          }
      }

      return $r;
  }

}
