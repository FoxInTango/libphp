<?php
class image
{
    public static $image_type_to_mime_type_array = array('bmp'=>'image/bmp','jpg'=>'image/jpeg','png'=>'image/png');
    public static $image_mime_type_to_type_array = array('image/bmp'=>'bmp','image/jpeg'=>'jpg','image/png'=>'png');
    public $path   = null;
    public $mime   = null;
    public $type   = null;
    public $entity = null;
    /** 
     * $name
     */
    public function __construct($path)
    {
        $info = getimagesize($path);
        if($info)
        {
            //file_put_contents('/home/www/logs/image.log', "getImageSize : " . json_encode($info);
            $this->mime = end($info);
            $this->type = image::$image_mime_type_to_type_array[$this->mime];
        } else return;

        switch($this->type)
        {
            case 'png':
            {
                $this->entity = imagecreatefrompng($path);
                //file_put_contents('/home/www/logs/image.log', "imagecreatefrompng : " . $path;
            }break;
            case 'jpg':
            {
                $this->entity = imagecreatefromjpeg($path);
            }break;
            case 'bmp':
            {
                $this->entity = imagecreatefrombmp($path);
            }break;
            default:{
            }break;
        }
        
        $this->path = $path;
    }

    public function __destruct() {
        if($this->entity)
        imagedestroy($this->entity);
    }

    public function flush()
    {
        if(!$this->entity) return false;
        ob_clean();
        switch($this->type)
        {
            case 'png':
            {
                header("Content-Type: image/x-png");
                imagepng($this->entity);
                return true;
            }break;
            case 'jpg':
            {
                header('Content-Type:image/jpeg');
                imagejpeg($this->entity);
                return true;
            }break;
            default:break;
        }
        ob_flush();
        echo ob_get_clean();
        //ob_end_clean();
        return false;
    }
};
?>

