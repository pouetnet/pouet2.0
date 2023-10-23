<?php
require_once(POUET_ROOT_LOCAL . "/include_pouet/box-modalmessage.php");

class PouetFormProcessor
{
  private $objects;
  private $errors;
  private $successURL;
  private $redirectOnSuccess;
  public $renderForm = true;
  public $successMessage = "see what you've done";
  
  const fieldName = "formProcessorAction";

  function __construct()
  {
    $this->objects = array();
    $this->errors = array();
    $this->successURL = NULL;
    $this->redirectOnSuccess = false;
  }
  function Add( $key, $object )
  {
    if (!has_trait($object,"PouetForm"))
      throw new Exception("The box being added to FormProcessor must have the PouetForm trait!");
    $this->objects[$key] = $object;
  }
  function SetSuccessURL( $url, $redirect = false )
  {
    $this->successURL = $url;
    $this->redirectOnSuccess = $redirect;
  }
  function GetErrors()
  {
    return $this->errors;
  }
  function Process()
  {
    $this->errors = array();
    if (@$_POST[ self::fieldName ] && $this->objects[$_POST[ self::fieldName ]])
    {
      $csrf = new CSRFProtect();
      if (!$csrf->ValidateToken())
      {
        $this->errors = array("who are you and where did you come from ?");
        return;
      }

      $this->errors = $this->objects[$_POST[ self::fieldName ]]->ParsePostMessage( $_POST );
      if (!$this->errors)
      {
        $this->successURL = str_replace("{%NEWID%}",rawurlencode($this->objects[$_POST[ self::fieldName ]]->GetInsertionID()),$this->successURL);
        if ($this->redirectOnSuccess)
        {
          redirect($this->successURL."#success");
          exit();
        }
      }
    }
  }
  function Display()
  {
    $showBox = true;
    if (count($this->errors))
    {
      $msg = new PouetBoxModalMessage( true );
      $msg->classes[] = "errorbox";
      $msg->title = "An error has occured:";
      $msg->message = "<ul><li>".implode("</li><li>",$this->errors)."</li></ul>";
      $msg->Render();
    }
    else
    {
      if (@$_POST[ self::fieldName ] && $this->objects[$_POST[ self::fieldName ]])
      {
        $msg = new PouetBoxModalMessage( true );
        $msg->classes[] = "successbox";
        $msg->title = "Success!";
        if ($this->successURL)
          $msg->message = "<a href='"._html($this->successURL)."'>".$this->successMessage."</a>";
        else
          $msg->message = "<a href='".POUET_ROOT_URL."'>go back to the front page</a>";
        $msg->Render();
        $showBox = false;
      }
    }
    if ($showBox)
    {
      foreach($this->objects as $key=>$object)
      {
        $object->Load();
        if ($this->renderForm)
        {
          printf("<form action='%s' method='post' enctype='multipart/form-data'>\n",_html(selfPath()));
          $csrf = new CSRFProtect();
          $csrf->PrintToken();
        }
        
        $object->Render();
  
        if ($this->renderForm)
        {
          printf("  <input type='hidden' name='%s' value='%s'/>\n",self::fieldName,_html($key));
          printf("</form>\n\n\n");
        }
      }
    }
  }
};

?>
