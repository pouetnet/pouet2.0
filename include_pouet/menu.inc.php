<?php
$menusetting = "bottombar";
if (!defined("MENU_DONE_ONCE"))
{
  $menusetting = "topbar";
  define("MENU_DONE_ONCE",true);
}
if (get_setting($menusetting))
{
?>
<nav id="<?=$menusetting?>">
  <ul>
<?php if ($currentUser) { ?>
   <li><a href="account.php">Account</a></li>
<?php } else { ?>
   <li><a href="login.php?return=<?=_html(rootRelativePath())?>">Log in</a></li>
<?php } ?>
   <li><a href="prodlist.php">Prods</a></li>
   <li><a href="groups.php">Groups</a></li>
   <li><a href="parties.php">Parties</a></li>
   <li><a href="userlist.php">Users</a></li>
   <li><a href="boards.php">Boards</a></li>
   <li><a href="lists.php">Lists</a></li>
   <li><a href="search.php">Search</a></li>
   <li><a href="bbs.php">BBS</a></li>
   <li><a href="faq.php">FAQ</a></li>
   <li><a href="submit.php">Submit</a></li>
<?php if ($currentUser && $currentUser->IsGloperator()) { ?>
   <li><a href="admin.php" class="adminlink">Admin</a></li>
<?php } ?>
  </ul>
</nav>
<?php
if (POUET_MOBILE)
{
  if ($menusetting == "topbar")
  {
    printf("<a href='#bottombar' class='mobileNavLink'>Go to bottom</a>");
  } 
  else
  {
    printf("<a href='#topbar' class='mobileNavLink'>Go to top</a>");
  } 
}
}
?>
