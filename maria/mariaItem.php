<?php
class mariaItem
{
    /**
     * {"key":"value","key":"value","key":"value","key":"value"} 
     */
    private $fields;
    
    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    public function fields()
    {
        return array_keys($this->fields);
    }

    public function fieldCount()
    {
        return count($this->fields);
    }

    public function fieldGet($name)
    {
        return $this->fields[$name];
    }

    public function fieldSet($name,$value)
    {
        if(!array_key_exists($name)) return false;

        $this->fields[$name] = $value;

        return true;
    }
}
?>
