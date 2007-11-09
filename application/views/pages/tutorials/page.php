<h2>Creating a Page Controller <span>By Jeremy Bush, &copy; 2007</span></h2>
<p>Here we will show a short example of many of the features of Kohana:</p>
<ol>
	<li>Setting up templates using Views within Views</li>
	<li>Create a virtual, database storage page system</li>
	<li>Create a simple authentication library for admins to edit pages</li>
</ol>

<h3>Starting Out</h3>
<p>To start we will create a file to hold our controller:</p>

<h4>application/controllers/page.php</h4>
<?php

echo geshi_highlight('class Page_Controller extends Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{

	}
}', 'php', NULL, TRUE)

?>

<p>Next we will set up the files and code to run our views using an area for a header, content and footer:</p>

<h4>application/views/layout.php</h4>
<?php

echo geshi_highlight('<?= $header ?>
<?= $content ?>
<?= $footer ?>
', 'php', NULL, TRUE)

?>


<h4>application/views/header.php</h4>
<?php

echo geshi_highlight('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
	<head>
		<title><?=$title?></title>
		<link rel="stylesheet" type="text/css" href="/css/layout.css" />
		<link rel="stylesheet" type="text/css" href="/css/style.css" />
		<link rel="stylesheet" type="text/css" href="/css/menu.css" />

		<script type="text/javascript" src="/js/jquery.js"></script>
		<script type="text/javascript" src="/js/jquery.livequery.js"></script>
		<script type="text/javascript" src="/js/jquery.corner.js"></script>
		<script type="text/javascript" src="/js/jquery.overlabel.js"></script>
		<script type="text/javascript" src="/js/jqModal.js"></script>
		<script type="text/javascript" src="/js/effects.js"></script>
	</head>
	<body>
		<div id="menu">
			<ul class="nav">
				<?php foreach ($menu["menu"] as $url => $title):?>
				<li><?=html::anchor($url, htmlentities($title))?>
					<?php if (isset($menu["submenu"][$url])):?><ul>
						<?php foreach ($menu["submenu"][$url] as $child_url => $title):?>
						<li><?=html::anchor($child_url, htmlentities($title))?></li>
						<?php endforeach; ?>
					</ul><?php endif; ?>
				</li><?php endforeach; ?>
			</ul>
		</div>
		<div id="content">
', 'php', NULL, TRUE)

?>


<h4>application/views/footer.php</h4>
<?php

echo geshi_highlight('<?php

 $no_edit = array("add", "edit", "details", "store");

 /* Set this page for the login redirections */
 if ($this->uri->segment(1) != "user" and $this->uri->segment(2) != "edit")
 {
 	$this_page = $_SERVER["REQUEST_URI"];
 	$this->session->set(array("last_page" => $this_page));
 }
 if ($this->session->get("loggedin") and !in_array($this->uri->segment(1), $no_edit))
 	echo html::anchor(($this->uri->rsegment(1) != "") ? $this->uri->rsegment(1) : "page") . "/edit/" . $this->uri->segment(1) . (($this->uri->segment(2) !== false) ? ("/" . $this->uri->segment(2)) : "", "Edit this page");

?>
</div>
<div id="footer">
	<p>&copy; Copyright 2007 Kohana</p>
</div>

<?php if (!$this->session->get("loggedin")):?><h3><?=html::anchor("user/login", "LOGIN")?><?php endif; ?>
<?php if ($this->session->get("loggedin")):?><h3><?=html::anchor("user/logout", "Logout")?></h3>
	<h3><?=html::anchor("page/list_pages", "Page Administration")?></h3><?php endif; ?>
</body>
</html>
', 'php', NULL, TRUE)

?>

<p>Just glance over those files, they contain some things we will finalize later in the tutorial.</p>
<p>Next we will set up the view for displaying our main content:</p>

<h4>application/views/page/index.php</h4>
<?php

echo geshi_highlight('<?=$content->content?>
', 'php', NULL, TRUE)

?>

<p>Wasn't that easy? ;)</p>
<p>Now that we have all the view files set up, we will set up our controller to use them. This will use Views in Views to create a basic templating system:</p>

<h4>application/controllers/page.php</h4>
<?php

echo geshi_highlight('function index()
{
	$this->layout->header = $this->load->view("header", array_merge($this->header, array("title" => $page->title)));
	$this->layout->content = $this->load->view("page/index", array("content" => ""));
	$this->layout->footer = $this->load->view("footer.php");
	$this->layout->render(TRUE);

}
', 'php', NULL, TRUE)

?>
<p>Here we are just passing an empty page into the view, so we will have a pretty boring blank white page.
This actually won't work yet, since we haven't set any pages for the menus that you see in the header file.
We will get to that in a bit =)</p>
<p>We need to set up a pages table in our database, which you can do with the following SQL:</p>
<?php 

echo geshi_highlight("CREATE TABLE `pages` (
`id` mediumint( 9 ) NOT NULL AUTO_INCREMENT ,
`page_name` varchar( 100 ) NOT NULL ,
`title` varchar( 255 ) NOT NULL ,
`content` longtext NOT NULL ,
`menu` tinyint( 1 ) NOT NULL default '0',
`filename` varchar( 255 ) NOT NULL ,
`order` mediumint( 9 ) NOT NULL ,
`date` int( 11 ) NOT NULL ,
`child_of` mediumint( 9 ) NOT NULL default '0',
PRIMARY KEY ( `id` ) ,
UNIQUE KEY `filename` ( `filename` )
) ENGINE = MYISAM DEFAULT CHARSET = utf8 PACK_KEYS =0;

INSERT INTO `pages` ( `id` , `page_name` , `title` , `content` , `menu` , `filename` , `order` , `date` , `child_of` ) 
VALUES (  NULL , 'Home', 'Home', 'index', '1', '', '1', '0', '0' );", 'sql', NULL, TRUE)

?>
<p>Next we will set up a model to grab some pages out of our database and feed them into our controller.</p>

<h4>application/models/page.php</h4>
<?php

echo geshi_highlight('class Page_Model extends Model {

	function get_page_id($page_name)
	{
		$query = $this->db->from("pages")->where("filename", $page_name)->get();

		return (count($query) > 0) ? $query->current()->id : 0;
	}

	function get_page($page)
	{
		if (isset($page["child"]))
		{
			$child_id = $this->get_page_id($page["parent"]);
			$query = $this->db->from("pages")->where(array("filename" => $page["child"], "child_of" => $child_id))->limit("1")->get();
		}
		else
		{
			$query = $this->db->from("pages")->where("filename", $page["parent"])->limit("1")->get();
		}

		/* Check to see if the page exists */
		if (count($query) > 0)
			return $query->current();
		else
			return false;
	}
', 'php', NULL, TRUE)

?>
<p>We will also add in some code into our controller constructor to fetch what page was called from the browser. Also, we will add the main view load to the constructor.</p>

<h4>application/controllers/page.php</h4>
<?php

echo geshi_highlight('function __construct()
{
	parent::__construct();

	$this->load->model("page");
	$this->header = array("menu" => $this->page->get_menu(), "title" => "example.com :: Home");

	/* Get the page (page or folder/page) */
	$base = ($this->uri->segment(2) == "edit") ? 2 : 0;

	if ($this->uri->segment((1+$base)) !== false && $this->uri->segment((2+$base)) !== false) // This is a sub page
		$this->location = array("parent" => $this->uri->segment((1+$base)), "child" => $this->uri->segment((2+$base)));
	else if ($this->uri->segment((1+$base)) !== false && $this->uri->segment((2+$base)) === false) // This is a main page
		$this->location = array("parent" => $this->uri->segment((1+$base)));
	else // This is the home page
		$this->location = array("parent" => "index");

	$this->layout = $this->load->view("layout");

	$this->layout->footer = ($this->input->get("no_header")) ? "" : $this->load->view("footer");
}
', 'php', NULL, TRUE)

?>
<p>In this example, our site will support folder/subpage.html layout, and no deeper. You could expand this system to allow for an infinite level of "folders" if you wish.
We will also support having the user add a GET parameter to not load the header and footer, useful for loading pages with ajax.</p>
<p>Now we will add this support into the index function:</p>
<?php

echo geshi_highlight('function index()
{
	if ($page = $this->page->get_page($this->location))
	{
		if (!$this->input->get("no_header"))
			$this->layout->header = $this->load->view("header", array_merge($this->header, array("title" => "radd-cpa.org :: " . $page->title)));
		else
			$this->layout->header = "";
		$this->layout->content = $this->load->view("page/index", array("page" => $page));

		$this->layout->render(TRUE);
	}
	else
		Kohana::show_404();
}
', 'php', NULL, TRUE)

?>
<p>Here we load the page from the model, and also cleverly show a 404 status if the page doesn't exist (the database result would be FALSE).</p>
<p>I have provided a whole <?=html::anchor('/tutorials/download/page.zip', 'application')?> for you to look at, pick apart or use on your own projects.<br />
Feel free to send any changes or improvements back to me at <?=html::mailto('contractfrombelow@gmail.com')?>.</p>
<p>It includes a basic authentication system for administrating pages, a FCKeditor for easy editing of pages, and some basic HTML views to get you going.</p>

<p><strong><?php echo html::file_anchor(Config::item('core.index_page', TRUE).'tutorials/download/page_tutorial.zip', 'Download Tutorial Files') ?></strong></p>