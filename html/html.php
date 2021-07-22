<?php

class tlSegment
{
    private $elements = array();

    public function appendElement($element)
    {
        array_push($element);   
    }

    public function flush()
    {
        echo "<div class='tl_segment'>";

        foreach($this->elements as $element)
        {
            $element->flush();
        }

        echo "</div>";
    }
}

class tlHead extends tlSegment
{
    public function __construct()
    {}

    public function flush()
    {
        $company = $_SESSION["company"];

        $tl_head_button_select_title_image_type = '';
        $tl_head_button_select_title_image_name = '';
        $tl_head_button_select_title_image_url  = new image_api_url($tl_head_button_select_title_image_type,$tl_head_button_select_title_image_name);

        $tl_head_button_select_title_zh = "杭州图灵贸易有限公司";
        $tl_head_button_select_title_en = "Hangzhou Truio Trading co.LTD";

        echo "<div class='tl_head'>";
            //网站选择按钮
            echo "<div id='tl_head_button_select' class=''>";
                $tl_head_button_select_title_image_url_content = $tl_head_button_select_title_image_url->echoURL();
                echo "<img  class='tl_head_button_select_image' src='$tl_head_button_select_title_image_url_content'>";
                echo "<div class='tl_head_button_select_title_zh'>$tl_head_button_select_title_zh<div>";
                echo "<div class='tl_head_button_select_title_en'>$tl_head_button_select_title_en<div>";
            echo "</div>"; 

            echo "<div class='tl_head_button_main'>首页</div>";
            echo "<div class='tl_head_button_company'>企业介绍</div>";
            echo "<div class='tl_head_button_product'>产品展示</div>";
            echo "<div class='tl_head_button_news'>新闻动态</div>";
            echo "<div class='tl_head_button_contact'>联系我们</div>";
            echo "<div class='tl_head_button_jobs'>人才招聘</div>";
        echo "</div>";
    }
}

class tlFoot extends tlSegment
{
    public function __construct()
    {
    }

    public function flush()
    {
        echo "<div class='tl_foot>'";
            //公司LOGO
            echo "<div class='tl_foot_logo'>";
                $tl_foot_logo_image_type = '';
                $tl_foot_logo_image_name = '';
                $tl_foot_logo_image_url  = new image_api_url($tl_foot_logo_image_type,$tl_foot_logo_image_name);
                $tl_foot_logo_image_url_content = $tl_foot_logo_image_url->echoURL();
                echo "<img class='tl_foot_logo_image' src='$tl_foot_logo_image_url_content'>";
                echo "<span class='tl_foot_logo_text'>TURIO</span>";
            echo "</div>";

            echo "<hr class='tl_foot_line'/>";

            //备案信息
            echo "<div class='tl_foot_beian'>";
                echo "<div class='tl_foot_beian'>";
                echo "</div>";
            echo "</div>";
        echo "</div>";
    }
}

class tlBannerItem
{
    public function __construct($model,$class)
    {

    }

    public function load($model)
    {

    }

    public function flush()
    {

    }
}

class tlBanner extends tlSegment
{
    
}

class tlImageButton
{

}

class tlScrollView
{
    public function __construct($model,$class)
    {

    }

    public function load($model)
    {

    }

    public function flush()
    {

    }
}

class tlPage
{
    private $title = '';
    private $head  = null;
    private $foot  = null;
    private $segments = array();

    public function __construct($title)
    {
        $this->title = $title;

        $this->head = new tlHead();

        $this->foot = new tlFoot();
    }

    public function appendSegment($segment)
    {
        array_push($this->segments,$segment);
    }

    public function flush()
    {
        echo "<html>";
            echo "<head>";
                echo "<title>" . $this->title . "</title>";
            echo "</head>";
            echo "<body>";
                $this->head->flush();

                foreach($this->segments as $segment)
                {
                    $segment->flush();
                }

                $this->foot->flush();
            echo "</body>";
        echo "</html>";
    }
}
?>
