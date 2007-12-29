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

<p>Although Kohana reuses many common design patterns and concepts, there are some things that make Kohana stand out:</p>

<ol>
<li><strong>Community, not company, driven.</strong> Kohana development is driven by a team of dedicated people that need a framework for fast, powerful solutions.</li>
<li><strong>Strict PHP5 <abbr title="Object Oriented Programming">OOP</abbr>.</strong> Offers many benefits: visibility protection, automatic class loading, overloading, interfaces, abstracts, and singletons.</li>
<li><strong>Extremely lightweight.</strong> Kohana has no dependencies on PECL extensions or PEAR libraries. Large, monolithic libraries are avoided in favor of optimized solutions.</li>
<li><strong>GET, POST, COOKIE, <em>and</em> SESSION arrays all work as expected.</strong> Kohana does not limit your access to global data, but offers  filtering and <abbr title="Cross Site Scripting">XSS</abbr> protection.</li>
<li><strong>True auto-loading of classes.</strong> True on-demand loading of classes, as they are requested in your application.</li>
<li><strong>No namespace conflicts.</strong> All classes are suffixed to allow similar names between components, for a more coherent API.</li>
<li><strong>Cascading resources offer unparalleled extensibility.</strong> Almost every part of Kohana can be overloaded or extended without editing core system files. Modules allow multi-file plugins to be added to your application, transparently.</li>
<li><strong>Library drivers and API consistency.</strong> Libraries can use different "drivers" to handle different external <abbr title="Application Programming Interface">API</abbr>s transparently. For example, multiple session storage options are available (database, cookie, and native), but the same interface is used for all of them. This allows new drivers to be developed for existing libraries, which keeps the API consistent and transparent.</li>
<li><strong>Powerful event handler.</strong> Observer-style event handlers allow for extreme levels of customization potential.</li>
<li><strong>Rapid development cycle.</strong> Rapid development results in faster response to user bugs and requests.</li>
</ol>