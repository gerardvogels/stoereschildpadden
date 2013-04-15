<?php

class Model_Myfilters
{
    public function ucWords($string)
    {
        $array = str_split($string);
        $ucFlag = false;
        foreach ($array as $char) {

            if($ucFlag)
            {
                $char = strtoupper($char);
                $ucFlag = false;
            }

            if ($char == ' ') 
            {
                $ucFlag = true;
            }
            else
            {
                $strOut[] = $char;
            }
        }
        
        return implode('', $strOut);
    }
    
    public function spaceToDash($string)
    {
        $array = str_split($string);
        $out=array();
        foreach ($array as $char) {
            if ($char == ' ') 
            {
                $char = '-';
            }
            $out[] = $char;
        }
        return implode('', $out);
    }

    public function commaToDot($string)
    {
        $out = str_replace(',','.',$string);
        return $out;
    }

    public function dotToComma($string)
    {
        $out = str_replace('.',',',$string);
        return $out;
    }



}
