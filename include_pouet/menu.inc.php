<?
$menusetting = !defined("MENU_DONE_ONCE") ? "topbar" : "bottombar";
define("MENU_DONE_ONCE",true);
if (get_setting($menusetting))
{
?>
<nav>
  <ul>
   <li><a href="account.php">Account</a></li>
   <li><a href="prodlist.php">Prods</a></li>
   <li><a href="groups.php">Groups</a></li>
   <li><a href="parties.php">Parties</a></li>
   <li><a href="userlist.php">Users</a></li>
   <li><a href="search.php">Search</a></li>
   <li><a href="bbs.php">BBS</a></li>
   <li><a href="faq.php">FAQ</a></li>
   <li><a href="submit.php">Submit</a></li>
<? if ($currentUser && $currentUser->IsGloperator()) { ?>
   <li><a href="admin.php" class="adminlink">Admin</a></li>
<? } ?>
  </ul>
</nav>
<?
}
?>
