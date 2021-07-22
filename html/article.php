<?php
/**

{
"title":"",
"subtitle":"",
"sections":[
           
           ]

}
*
*/
class articleItem {
    public __construct($image,$content){
        
    }
}

class articleItemList {
    public $items;
    public __construct() {
        $this->items = Array();
    }
}

class articleSection {
      public $image;
      public $content;
      public $itemLists;
      public $subsections;
      public __construct($image,$content)
      {
          $this->image   = $image;
          $this->content = $content;
          $this->itemLists = Array();
          $this->subsections = Array();
      }
      public function flush()
      {
          //echo "<div class=logo><img src=" . $this->image ." alt=\"\"></div>";
      }
}

class article {
    public $title;
    public $subTitle;
    public $sections;
    public __construct($path)
    {
        $this->sections = Array();

        if($path !== null && file_exists($path)){
           $articleObject = json_decode(file_get_contents($path));

           $this->title = $articleObject->title;
           $this->subTitle = $articleObject->subtitle;
        }
    }

    public function loadFile($path){

    }

    public function loadString($content){
    
    }
}
