<h1>System Bootstrapping</h1>

<p>Kohana uses a <abbr title="Front Controller: A single file responsible for all subsequent loading of application resources">front controller</abbr> as part of it&#39;s design. This file is the <file>index</file> file that rests in the directory that Kohana is installed in.</p>

<p>The front controller validates the application and system paths, then loads <file>system/core/Bootstrap</file>. The Bootstrap file begins the process of initializing Kohana.</p>

<h2 id="init">Loading</h2>
<p id="init_stage_1">Benchmarking is loaded, and the <benchmark>total_execution_time</benchmark> is started. Next the <benchmark>base_classes_loading</benchmark> benchmark is started.</p>

<p id="init_stage_2">Core classes (Config, Event, Kohana, Log, utf8) are loaded. Kohana setup is run:</p>
<ol>
	<li>Global output buffer registered, enabling a central function that will replace the following strings in the buffer before sending it to the browser:<ul>
			<li><code>&#123;kohana_version&#125;</code>: Version of Kohana that is running</li>
			<li><code>&#123;execution_time&#125;</code>: Benchmark of the total execution time up to this point</li>
			<li><code>&#123;memory_usage&#125;</code>: Total memory being used by the current request</li>
		</ul></li>
	<li>Class auto-loading is enabled for Controllers, Libraries, Models, and Helpers</li>
	<li>Error handling is changed to Kohana methods, rather than the PHP defaults</li>
	<li><benchmark>base_classes_loading</benchmark> is stopped</li>
	<li><event>system.shutdown</event> is registered</li>
	<li><event>system.ready</abbr> is executed</li>
</ol>

<p id="init_stage_3">Routing is performed for the current request. A controller is chosen and located, the segments are prepared for executing the controller.</p>
