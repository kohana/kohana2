<p class="intro">
	Kohana is a <strong>PHP5 framework</strong> that uses the <strong>Model View Controller</strong> architectural pattern.
	It aims to be <strong>secure</strong>, <strong>lightweight</strong>, and <strong>easy</strong> to use.
</p>

<div style="float:left; padding-right: 4em;">
<h2>Features</h2>
<ul>
	<li>Highly secure</li>
	<li>Extremely lightweight</li>
	<li>Short learning curve</li>
	<li>Uses the <abbr title="Model View Controller">MVC</abbr> pattern</li>
	<li>100% UTF-8 compatible</li>
	<li>Loosely coupled architecture</li>
	<li>Extremely easy to extend</li>
</ul>
</div>

<div style="float:left;">
<h2>Technology</h2>
<ul>
	<li>Strict PHP5 <abbr title="Object Oriented Programming">OOP</abbr></li>
	<li>Simple database abstraction using SQL helpers</li>
	<li>Multiple session drivers (native, database, and cookie)</li>
	<!-- <li>Advanced cache system with drivers (file, database, memcache, shmop)</li> -->
	<li>Powerful event handler allows small modifications dynamically</li>
	<li>Originally based on <?php echo html::anchor('http://www.codeigniter.com', 'CodeIgniter') ?></li>
</ul>
</div>

<h3 style="clear:both;padding-top:1em;">How is Kohana Different?</h3>

<p>The most commonly asked question about Kohana is "What's the difference between Kohana and CodeIgniter?". Although this is a large topic, we have attempted to outline some of the primary differences and similarities:</p>

<ol>
<li><strong>Strict PHP5 <abbr title="Object Oriented Programming">OOP</abbr>.</strong> Offers many benefits: visibility protection, automatic class loading, overloading, interfaces, abstracts, and singletons.</li>
<li><strong>Continues CodeIgniter design patterns.</strong> Anyone who has used CodeIgniter will quickly understand Kohana's structure and design patterns.</li>
<li><strong>Community, not company, driven.</strong> Kohana is driven by community discussion, ideas, and code. Kohana developers are from all around the world, each with their own talents. This allows a rapid and flexible development cycle that can respond to bugs and requests within hours, instead of days or months.</li>
<li><strong>GET, POST, COOKIE, <em>and</em> SESSION arrays all work as expected.</strong> Kohana does not limit your access to global data, but offers the same filtering and <abbr title="Cross Site Scripting">XSS</abbr> protection that CodeIgniter does.</li>
<li><strong>Cascading resources, modules, and inheritance.</strong> Controllers, models, libraries, helpers, and views can be loaded from any location within your <tt>system</tt>, <tt>application</tt>, or <strong>module paths</strong>. Configuration options are inherited and can by dynamically overwritten by each application.</li>
<li><strong>No namespace conflicts.</strong> Class suffixes, like <tt>_Controller</tt>, are used to prevent namespace conflicts. This allows a Users controller and Users model to both be loaded at the same time.</li>
<li><strong>True auto-loading of classes.</strong> This includes libraries, controllers, models, and helpers. This is <strong>not</strong> pre-loading, but true dynamic loading of classes, as they are requested.</li>
<li><strong>Helpers are static classes, not functions.</strong> For example, instead of using <tt>form_open()</tt>, you would use <tt>form::open()</tt>.</li>
<li><strong>Library drivers and API consistency.</strong> Libraries can use different "drivers" to handle different external <abbr title="Application Programming Interface">API</abbr>s transparently. For example, multiple session storage options are available (database, cookie, and native), but the same interface is used for all of them. This allows new drivers to be developed for existing libraries, which keeps the API consistent and transparent.</li>
<li><strong>Powerful event handler.</strong> Kohana events can by dynamically added to, replaced, or even removed completely. This allows many changes to Kohana execution process, without modification to existing system code.</li>
</ol>