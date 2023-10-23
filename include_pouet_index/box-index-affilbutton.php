<?php
class PouetBoxIndexAffilButton extends PouetBoxCachable
{
  var $data;
  function __construct()
  {
    parent::__construct();
    $this->uniqueID = "pouetbox_affilbutton";
    $this->title = "affiliate buttons";
  }
  //use PouetFrontPage;

  function Load( $cached = false )
  {
    $s = new SQLSelect();
    $s->AddTable("buttons");
    $s->AddOrder("rand()");
    $s->AddWhere("dead = 0");
    $s->SetLimit("1");
    $this->data = SQLLib::SelectRow($s->GetQuery());

    $this->title = $this->data->type;
  }

  function RenderContent()
  {
    echo "<a href='"._html($this->data->url)."'><img src='".POUET_CONTENT_URL."buttons/".$this->data->img."' title='"._html($this->data->alt)."' alt='"._html($this->data->alt)."'/></a>";
  }

  function RenderFooter()
  {
    echo " <div class='foot'><a href='buttons.php'>more</a>...</div>";
    echo "</div>";
  }
};

$indexAvailableBoxes[] = "AffilButton";
?>