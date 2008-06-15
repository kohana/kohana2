<p class="intro">Kohana (Кохана) - это <strong>PHP 5 фрейворк</strong>, использующий архитектурную модель <strong>MVC (Модель - Представление - Поведение)</strong>.</p>
<div style="float:left; padding-right: 4em;">
	<h2>Особенности</h2>
	<ul>
		<li>Высокая безопасность</li>
		<li>Экстремально легкий</li>
		<li>Прост в понимании</li>
		<li>Использует <abbr title="Model View Controller (Модель - Представление - Поведение)">MVC</abbr> модель</li>
		<li>100% совместим с UTF-8</li>
		<li>Легко расширяем</li>
	</ul>
</div>
<div style="float:left;">
	<h2>Технология</h2>
	<ul>
		<li>Использует PHP 5 <abbr title="Object Oriented Programming">ООП</abbr></li>
		<li>Абстракция базы данных, используя SQL helpers</li>
		<li>Разнообразные драйвера сессий (native, database и cookie)</li>
		<!-- <li>Advanced cache system with drivers (file, database, memcache, shmop)</li> -->
		<li>Powerful event handler allows small modifications dynamically</li>
		<li>Основан на базе <?php echo html::anchor('http://www.codeigniter.com', 'CodeIgniter') ?></li>
	</ul>
</div>
<h3 style="clear:both;padding-top:1em;">How is Kohana Different?</h3>
<p>Although Kohana reuses many common design patterns and concepts, there are some things that make Kohana stand out:</p>
<ol>
	<li><strong>Community, not company, driven.</strong> Kohana development is driven by a team of dedicated people that need a framework for fast, powerful solutions.</li>
	<li><strong>Strict PHP 5 <abbr title="Object Oriented Programming">OOP</abbr>.</strong> Offers many benefits: visibility protection, automatic class loading, overloading, interfaces, abstracts, and singletons.</li>
	<li><strong>Extremely lightweight.</strong> Kohana has no dependencies on PECL extensions or PEAR libraries. Large, monolithic libraries are avoided in favor of optimized solutions.</li>
	<li><strong>GET, POST, COOKIE, <em>and</em> SESSION arrays all work as expected.</strong> Kohana does not limit your access to global data, but offers	filtering and <abbr title="Cross Site Scripting">XSS</abbr> protection.</li>
	<li><strong>True auto-loading of classes.</strong> True on-demand loading of classes, as they are requested in your application.</li>
	<li><strong>No namespace conflicts.</strong> All classes are suffixed to allow similar names between components, for a more coherent API.</li>
	<li><strong>Cascading resources offer unparalleled extensibility.</strong> Almost every part of Kohana can be overloaded or extended without editing core system files. Modules allow multi-file plugins to be added to your application, transparently.</li>
	<li><strong>Library drivers and API consistency.</strong> Libraries can use different "drivers" to handle different external <abbr title="Application Programming Interface">API</abbr>s transparently. For example, multiple session storage options are available (database, cookie, and native), but the same interface is used for all of them. This allows new drivers to be developed for existing libraries, which keeps the API consistent and transparent.</li>
	<li><strong>Powerful event handler.</strong> Observer-style event handlers allow for extreme levels of customization potential.</li>
	<li><strong>Rapid development cycle.</strong> Rapid development results in faster response to user bugs and requests.</li>
</ol>