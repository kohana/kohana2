<p class="intro">Kohana ist ein <strong>PHP-5-Framework</strong>, das das <strong>Model-View-Controller</strong>-Architekturmuster einsetzt. Es beabsichtigt <strong>sicher</strong>, <strong>schlank</strong> und <strong>einfach</strong> bedienbar zu sein.</p>
<div style="float:left; padding-right: 4em;">
	<h2>Eigenschaften</h2>
	<ul>
		<li>Sehr sicher</li>
		<li>Besonders schlank</li>
		<li>Einfach zu erlernen</li>
		<li>Setzt das <abbr title="Model View Controller">MVC</abbr>-Muster ein</li>
		<li>100% kompatibel zu UTF-8</li>
		<li>Architektur der losen Kopplung</li>
		<li>Besonders einfach zu erweitern</li>
	</ul>
</div>
<div style="float:left;">
	<h2>Technologie</h2>
	<ul>
		<li>Striktes PHP-5-<abbr title="Objektorientierte Programmierung">OOP</abbr></li>
		<li>Einfache Datenbankabstraktion mittels SQL-Helfern</li>
		<li>Multiple Session-Treiber (Nativ, Datenbank und Cookies)</li>
		<!-- <li>Advanced cache system with drivers (file, database, memcache, shmop)</li> -->
		<li>Mächtige Ereignisbehandlung erlaubt dynamische kleine Modifizierungen</li>
		<li>Basierte ursprünglich auf <?php echo html::anchor('http://www.codeigniter.com', 'CodeIgniter') ?></li>
	</ul>
</div>
<h3 style="clear:both;padding-top:1em;">Was macht Kohana so besonders?</h3>
<p>Auch wenn Kohana viele bekannte Konzepte und Entwurfsmuster einsetzt, gibt es einige Dinge, die Kohana hervorheben:</p>
<ol>
	<li><strong>Betrieben von einer Comunity und nicht einem Unternehmen.</strong> Kohana entwickelt ein Team mit ausgewählten Leuten, die ein Framework für schnelle und leistungsstarke Lösungen brauchen.</li>
	<li><strong>Striktes PHP-5-<abbr title="Object Oriented Programming">OOP</abbr>.</strong> Bietet viele Vorteile: visibility protection, automatic class loading, overloading, interfaces, abstracts, and singletons.</li>
	<li><strong>Besonders Schlank.</strong> Kohana hängt von keinen PECL-Erweiterungen oder PEAR-Bibliotheken ab. Große, monolithische Bibliotheken werden vermieden und optmierte Lösungen bevorzugt.</li>
	<li><strong>GET-, POST-, COOKIE-, <em>und</em> SESSION-Arrays funktionieren, wie gewohnt.</strong> Kohana beschränkt nicht den Zugriff auf globale daten, bietet jedoch Filterung und <abbr title="Cross Site Scripting">XSS</abbr>-Schutz an.</li>
	<li><strong>Echtes auto-loading von Klassen.</strong> Klassen werden geladen, sobald diese in ihrer Applikation gebraucht werden.</li>
	<li><strong>Keine Namensraum-Konflikte.</strong> Allen Klassen sind Suffixe angehängt, um ähnliche Namen innerhalb von Komponenten zu erlauben und somit eine einheitliche API entsteht.</li>
	<li><strong>Kaskadierte Ressourcen erlauben einmalige Erweiterbarkeit.</strong> Nahezu jeder Bestandteil in Kohana kann überladen oder erweitert werden, ohne System-Dateien bearbeiten zu müssen. Module erlauben es Plugins mit mehrere Dateien zu Ihrer Applikation transparent hinzuzufügen.</li>
	<li><strong>Treiber-Bibliotheken und konsistente API.</strong> Bibliotheken können verschiedene "Treiber" einsetzen, um externen <abbr title="Application Programming Interface">API</abbr>s transparent zu bearbeiten. Es gibt beispielsweise verschiedene Möglichkeiten eine Session zu speichern (Nativ, Datenbank, und Cookies), jedoch wird das selbe Interface für alle Treiber benutzt. Dies stellt sicher, dass neue Treiber für vorhandene Bibliotheken entwickelt werden können, was die Konsistenz und Transparenz des API erhält.</li>
	<li><strong>Mächtige Ereignisbehandlung.</strong> Ereignisbehandlung im Observer-Stil ermöglicht ein sehr hohes Anpassungs-Potenzial.</li>
	<li><strong>Rasanter Entwicklungszyklus.</strong> Rasante Entwicklung ermöglicht eine schnellere Reaktion auf Fehlermeldungen und Wünsche der Benutzer.</li>
</ol>