<?php

require_once dirname(__FILE__) . "/mariaDB.php";

class mariaTB
{
    private $nameDB;
    private $linkDB;

    public function __construct($entityDB,$name)
    {
        $this->nameTB = $name;
        $this->linkDB = $entityDB;
    }

    public function rename($name)
    {
        if($this->linkDB)
        {
            $query = "ALTER TABLE " . $this->nameTB . " RENAME TO ".  $name . ";";

            return $this->linkDB->query($query);
        }

        return false;
    }

    public function changeComment($comment)
    {
        if($this->linkDB)
        {
            $query = "ALTER TABLE " . $this->nameTB . " COMMENT ".  $comment . ";";

            return $this->linkDB->query($query);
        }

        return false;
    }

    //表记录数
    public function count()
    {
        if($this->linkDB)
        {
            $query = "SELECT COUNT(*) FROM " . $this->nameTB;
            $query = "select table_name,table_rows from information_schema.tables where TABLE_SCHEMA = '$this->nameTB' order by table_rows desc;";
            $items = $this->linkDB->query($query);
            $item = mysqli_fetch_array($items);
            
            if($item) return $item['table_rows'];

            return FALSE;
        }

        return FALSE;
    }

    //表修改
    public function appendField($field,$type,$comment=null)
    {
        if($this->linkDB)
        {
            $query = "ALTER TABLE " . $this->nameTB . " ADD COLUMN " . $field . " "  . $type;

            if($comment)
            {
                $query .= " COMMNET " . $comment;
            }

            $query .= ";";

            return $result = $this->linkDB->query($query);
        }
    }

    public function insertField($field,$type,$after,$comment=null)
    {
        if($this->linkDB)
        {
            $query = "ALTER TABLE " . $this->nameTB . " ADD COLUMN " . $field . " "  . $type . " AFTER " . $after;

            if($comment)
            {
                $query .= " COMMNET " . $comment;
            }

            $query .= ";";

            return $result = $this->linkDB->query($query);
        }
    }

    public function deleteField($field)
    {
        if($this->linkDB)
        {
            $query = "ALTER TABLE " . $this->nameTB . " ADD COLUMN " . $field . " "  . $type . " AFTER " . $after . ";";

            return $result = $this->linkDB->query($query);
        }

        return false;
    }

    /** 
     * $old 旧字段名
     * $new 新字段名
     */
    public function renameField($oldName,$newName,$type)
    {
        if($this->linkDB)
        {
            $query = "ALTER TABLE " . $this->nameTB . " CHANGE " . $oldName . " " . $newName . " " . $type . ";";

            return $result = $this->linkDB->query($query);
        }

        return false;
    }

    public function retypeField($name,$type)
    {
        if($this->linkDB)
        {
            $query = "ALTER TABLE " . $this->nameTB . " CHANGE " . $name . " " . $name . " " . $type . ";";

            return $result = $this->linkDB->query($query);
        }

        return false;
    }

    //查询记录
    public function lookupItem($fields,$conditions=null)
    {
        if($this->linkDB && strlen($fields))
        {
            $query = "SELECT ";

            $query .= $fields;
            $query .= " FROM " . $this->nameTB;

            if($conditions && strlen($conditions))
            {
                $query .= " WHERE " . $conditions;
            }

            $query .= ";";
            //echo "lookupItem : " . $query . PHP_EOL;
            $result = $this->linkDB->query($query);

            if($result === FALSE) return null;

            $results = array();

            while($record = mysqli_fetch_array($result))
            {
                array_push($results,$record);
            }
            if(count($results)) {
                return $results;
            } else {
                return false;
            }
        }

        return false;
    }

    //添加记录
    public function appendItem($item)
    {
        if($this->linkDB && $item)
        {
            $query = "INSERT INTO " . $this->nameTB;

            $field_array = $item->fields();
            $field_count = count($field_array);

            $name_string  = ' (';
            $value_string = ' (';

            for($i = 0;$i < $field_count;$i ++)
            {
                $name_string  .= $field_array[$i];

                $value_string .= $item->fieldGet($field_array[$i]);

                if($i != $field_count - 1)
                {
                    $name_string  .= ',';
                    $value_string .= ',';
                }
                else
                {
                    $name_string  .= ')';
                    $value_string .= ')';
                }
            }

            $query .= $name_string . " VALUES " . $value_string . ";";
            //echo "mariaTB::appendItem : " . $query . PHP_EOL;
            return $this->linkDB->query($query);
        }

        return FALSE;
    }

    //更新记录
    public function updateItem($item,$conditions)
    {
        /**
         * UPDATE nameTB SET fieldA=valueA, fieldB=valueB [WHERE conditions]
         */
        if($this->linkDB && $item && $conditions)
        {
            $query = "UPDATE " . $this->nameTB . " SET ";

            $field_array = $item->fields();
            $field_count = count($field_array);

            for($i = 0;$i < $field_count;$i ++)
            {
                $name  = $field_array[$i];
                $value = $item->fieldGet($field_array[$i]);

                $query .= $name . "=" . $value;

                if($i != $field_count - 1)
                {
                    $query .= ",";
                }
                else
                {
                    $query .= " ";
                }
            }
            $query .= " WHERE " . $conditions . ";";
             //echo "theQuery : " . $query . "<br>";
            return $this->linkDB->query($query);
        }

        return FALSE;
    }

    //删除记录
    public function removeItem($conditions)
    {
        /**
         * DELETE FROM nameTB [WHERE conditions]
         */
        if($this->linkDB && $conditions)
        {
            $query = "DELETE FROM " . $this->nameTB . " WHERE " . $conditions . ";";

            return $this->linkDB->query($query);
        }

        return FALSE;
    }
}

/**
 * SELECT expression1, expression2, ... expression_n
 * FROM tables
 * [WHERE conditions]
 * UNION [ALL | DISTINCT] ALL:返回所有结果集，包含重复数据 | DISTINCT - 默认:删除结果集中重复的数据
 * SELECT expression1, expression2, ... expression_n
 * FROM tables
 * [WHERE conditions];
 */
?>
