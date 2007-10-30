<h1>Starting a Page Controller</h1>
<p>Here we will show a short example of many of the features of Kohana:</p>
<ol>
	<li>Setting up templates using Views within Views</li>
	<li>Create a virtual, database storage page system</li>
	<li>Create a simple authentication library for admins to edit pages</li>
</ol>
<h1>Starting Out</h1>
<p>To start we will create a file to hold our controller:</p>
<p><tt>application/controllers/page.php</tt>.</p>
<code>
class Page_Controller extends Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		
	}
}
</code>

<p>Next we will set up the files and code to run our views using an area for a header, content and footer:</p>
<p><tt>application/views/layout.php</tt></p>
<code>
<?= $header ?> 
<?= $content ?>
<?= $footer ?>
</code>

<p><tt>application/views/header.php</tt></p>
<code>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
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
				<?php foreach ($menu['menu'] as $url => $title):?><li><?=html::anchor($url, htmlentities($title))?>
					<?php if (isset($menu['submenu'][$url])):?><ul>
						<?php foreach ($menu['submenu'][$url] as $child_url => $title):?><li><?=html::anchor($child_url, htmlentities($title))?></li>
						<?php endforeach; ?>
					</ul><?php endif; ?>
				</li><?php endforeach; ?>
			</ul>
		</div>
		<div id="content">
</code>

<p><tt>application/views/footer.php</tt></p>
<code>
<?php
 
 $no_edit = array("add", "edit", 'details', 'store');
 
 /* Set this page for the login redirections */
 if ($this->uri->segment(1) != "user" and $this->uri->segment(2) != "edit")
 {
 	$this_page = $_SERVER['REQUEST_URI'];
 	$this->session->set(array('last_page' => $this_page));
 }
 if ($this->session->get('loggedin') and !in_array($this->uri->segment(1), $no_edit))
 	echo '<a href="/' . (($this->uri->rsegment(1) != "") ? $this->uri->rsegment(1) : "page") . '/edit/' . $this->uri->segment(1) . (($this->uri->segment(2) !== false) ? ("/" . $this->uri->segment(2)) : "") . '">Edit this page</a>';
 
?>
</div>
<div id="footer">
	<p>&copy; Copyright 2007 Kohana</p>
</div>

<?php if (!$this->session->get('loggedin')):?><h3><?=html::anchor('user/login', 'LOGIN')?><?php endif; ?>
<?php if ($this->session->get('loggedin')):?><h3><?=html::anchor('user/logout', 'Logout')?></h3>
	<h3><?=html::anchor('page/list_pages', 'Page Administration')?></h3><?php endif; ?>
</body>
</html>
</code>

<p>Just glance over those files, they contain some things we will finalize later in the tutorial.</p>
<p>Next we will set up the view for displaying our main content:</p>
<p><tt>application/views/page/index.php</tt></p>
<code>
<?=$content->content?>
</code>
<p>Wasn't that easy? ;)</p>
<p>Now that we have all the view files set up, we will set up our controller to use them:</p>
<p><tt>application/controllers/page.php</tt></p>
<code>
function index()
{
	$this->layout->header = $this->load->view('header', array_merge($this->header, array('title' => "radd-cpa.org :: " . $page->title)));
	$this->layout->content = $this->load->view('page/index', array('content' => ''));
	$this->layout->footer = $this->load->view('footer.php');
	$this->layout->render(TRUE);

}
</code>
<p>Here we are just passing an empty page into the view, so we will have a pretty boring blank white page.
This actually won't work yet, since we haven't set any pages for the menus that you see in the header file.
We will get to that in a bit =)</p>
<p>Next we will set up a model to grab some pages out of our database and feed them into our controller.</p>
<p><tt>application/models/page.php</tt></p>
<code>
class Page_Model extends Model {
	
	function get_page_id($page_name)
	{
		$query = $this->db->from('pages')->where('filename', $page_name)->get();
		
		return (count($query) > 0) ? $query->current()->id : 0;
	}

	function get_page($page)
	{
		if (isset($page['child']))
		{
			$child_id = $this->get_page_id($page['parent']);
			$query = $this->db->from('pages')->where(array('filename' => $page['child'], 'child_of' => $child_id))->limit('1')->get();			
		}
		else
		{
			$query = $this->db->from('pages')->where('filename', $page['parent'])->limit('1')->get();
		}

		/* Check to see if the page exists */
		if (count($query) > 0)
			return $query->current();
		else
			return false;
	}
</code>
<p>We will also add in some code into our controller constructor to fetch what page was called from the browser. Also, we will add the main view load to the constructor.</p>
<p><tt>application/controllers/page.php</tt></p>
<code>
function __construct()
{
	parent::__construct();

	$this->load->model('page');
	$this->header = array('menu' => $this->page->get_menu(), 'title' => "example.com :: Home");

	/* Get the page (page or folder/page) */
	$base = ($this->uri->segment(2) == 'edit') ? 2 : 0;

	if ($this->uri->segment((1+$base)) !== false && $this->uri->segment((2+$base)) !== false) // This is a sub page
		$this->location = array("parent" => $this->uri->segment((1+$base)), "child" => $this->uri->segment((2+$base)));
	else if ($this->uri->segment((1+$base)) !== false && $this->uri->segment((2+$base)) === false) // This is a main page
		$this->location = array("parent" => $this->uri->segment((1+$base)));
	else // This is the home page
		$this->location = array("parent" => "index");

	$this->layout = $this->load->view('layout');

	$this->layout->footer = ($this->input->get('no_header')) ? '' : $this->load->view('footer');
}
</code>
<p>In this example, our site will support folder/subpage.html layout, and no deeper. You could expand this system to allow for an infinite level of "folders" if you wish.
We also will support having the user add a GET parameter to not load the header and footer, useful for loading pages with ajax.</p>
<p>Now we will add this support into the index function:</p>
<code>
function index()
{
	if ($page = $this->page->get_page($this->location))
	{
		if (!$this->input->get('no_header'))
			$this->layout->header = $this->load->view('header', array_merge($this->header, array('title' => "radd-cpa.org :: " . $page->title)));
		else
			$this->layout->header = '';
		$this->layout->content = $this->load->view('page/index', array('page' => $page));

		$this->layout->render(TRUE);
	}
	else
		Kohana::show_404();
}
</code>
<p>Here we load the page from the model, and also cleverly show a 404 status if the page doesn't exist (the database result would be FALSE).</p>
<p>This is the basics of getting it going. You can find a whole application <?=html::anchor('#', 'here')?>.</p>