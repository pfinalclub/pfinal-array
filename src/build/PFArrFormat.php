<?php
namespace pf\arr\build;

trait PFArrFormat {
    public $type;
    public $rootNodeName='root';
    public $type_func=array(
        'json'=>'format_json',
        'xml'=>'format_xml',
        'serialize'=>'format_serialize',
        'obj'      =>'format_obj'
    );
    public function __construct($type='json') {
        $this->type = $type;
    }

    public function pf_encode($array) {
        if(method_exists($this, $this->type_func[$this->type])) {
            return call_user_func(array($this,$this->type_func[$this->type]), $array);
        }
        else{
            throw new Exception(sprintf('The required method "'.$this->type_func[$this->type].'" does not exist for!', $this->type_func[$this->type], get_class($this)));
        }
    }

    private function format_json($array) {
        return json_encode($array);
    }

    private function format_xml($array) {
        if (ini_get('zend.ze1_compatibility_mode') == 1)
        {
            ini_set ('zend.ze1_compatibility_mode', 0);
        }
        return $this->toXml($array,$this->rootNodeName);
    }

    private function format_serialize($array) {
        $array = serialize($array);
        return $array;
    }

    private function toXml($data, $rootNodeName = 'root', $xml=null) {
        if ($xml == null)
        {
            $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
        }
        foreach($data as $key => $value)
        {
            if (is_numeric($key))
            {
                $key = "unknownNode_". (string) $key;
            }

            if (is_array($value))
            {
                $node = $xml->addChild($key);
                $this->toXml($value, $rootNodeName, $node);
            }
            else
            {
                $value =htmlentities($value,ENT_QUOTES,'UTF-8');
                $xml->addChild($key,$value);
            }

        }
        return $xml->asXML();
    }

    private function format_obj($array) {
        $array = json_encode($array);
        $arr = json_decode($array,false);
        return $arr;
    }
}