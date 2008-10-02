<p class="intro">Kohana ist ein <strong>PHP-5-Framework</strong>, bei dem die <strong>Model-View-Controller</strong>-Architektur zum Einsatz kommt. Ziel ist es <strong>sicher</strong>, <strong>schlank</strong> und <strong>leicht bedienbar</strong> zu sein.</p>
<div style="float:left; padding-right: 4em;">
	<h2>Eigenschaften</h2>
	<ul>
		<li>Sehr sicher</li>
		<li>Besonders schlank</li>
		<li>Einfach zu erlernen</li>
		<li>Setzt das <abbr title="Model View Controller">MVC</abbr>-Muster ein</li>
		<li>100% kompatibel zu UTF-8</li>
		<li>Lose gekoppelte Architektur</li>
		<li>Besonders leicht erweiterbar</li>
	</ul>
</div>
<div style="float:left;">
	<h2>Technologie</h2>
	<ul>
		<li>Strenge PHP-5-<abbr title="Objektorientierte Programmierung">OOP</abbr></li>
		<li>Einfache Datenbankabstraktion mittels SQL-Helfern</li>
		<li>Mehrere Session-Treiber (Nativ, Datenbank und Cookies)</li>
		<!-- <li>Advanced cache system with drivers (file, database, memcache, shmop)</li> -->
		<li>Mächtige Ereignisbehandlung erlaubt dynamisch kleine Änderungen</li>
		<li>Basierte ursprünglich auf <?php echo html::anchor('http://www.codeigniter.com', 'CodeIgniter') ?></li>
	</ul>
</div>
<h3 style="clear:both;padding-top:1em;">Was macht Kohana so besonders?</h3>
<p>Auch wenn Kohana viele bekannte Konzepte und Entwurfsmuster einsetzt, gibt es dennoch einige Dinge, die Kohana hervorheben:</p>
<ol>
	<li><strong>Geführt von einer Comunity und nicht einem Unternehmen.</strong> Die Entwicklung Kohanas wird von engagierten Menschen angetrieben, die ein Framework für schnelle und leistungsstarke Lösungen brauchen.</li>
	<li><strong>Strenge PHP-5-<abbr title="Objektorientierte Programmierung">OOP</abbr>.</strong> Bietet viele Vorteile: visibility protection, automatic class loading, overloading, interfaces, abstracts, and singletons.</li>
	<li><strong>Besonders schlank.</strong> Kohana hängt von keinen PECL-Erweiterungen oder PEAR-Bibliotheken ab. Große, monolithische Bibliotheken werden vermieden und optmierte Lösungen bevorzugt.</li>
	<li><strong>GET-, POST-, COOKIE-, <em>und</em> SESSION-Arrays funktionieren, wie gewohnt.</strong> Kohana beschränkt nicht den Zugriff auf globale Daten, sondern bietet deren Filterung sowie <abbr title="Cross Site Scripting">XSS</abbr>-Schutz an.</li>
	<li><strong>Echtes auto-loading von Klassen.</strong> Klassen werden geladen, sobald diese in ihrer Applikation gebraucht werden.</li>
	<li><strong>Keine Namensraum-Konflikte.</strong> Allen Klassen sind Suffixe angehängt, um ähnliche Namen innerhalb von Komponenten zu erlauben, so dass eine einheitliche API entsteht.</li>
	<li><strong><a href="http://upload.wikimedia.org/wikipedia/en/1/1c/Kohana-modules.png">Kaskadierte Ressourcen</a> erlauben einmalige Erweiterbarkeit.</strong> Nahezu jeder Bestandteil in Kohana kann überladen oder erweitert werden, ohne System-Dateien bearbeiten zu müssen. Module erlauben es Plugins mit mehreren Dateien zu Ihrer Applikation transparent hinzuzufügen.</li>
	<li><strong>Treiber-Bibliotheken und konsistente API.</strong> Bibliotheken können verschiedene "Treiber" nutzen, um externe <abbr title="Application Programming Interface">API</abbr>s transparent zu verarbeiten. Es gibt beispielsweise verschiedene Möglichkeiten eine Session zu speichern (Nativ, Datenbank und Cookies), jedoch wird das selbe Interface für alle Treiber benutzt. Dies stellt sicher, dass neue Treiber für vorhandene Bibliotheken entwickelt werden können, was die Konsistenz und Transparenz des API erhält.</li>
	<li><strong>Mächtige Ereignisbehandlung.</strong> Ereignisbehandlung im Observer-Stil ermöglicht ein sehr hohes Anpassungspotenzial.</li>
	<li><strong>Rasanter Entwicklungszyklus.</strong> Rasante Entwicklung ermöglicht eine schnellere Reaktion auf Fehlermeldungen und Wünsche der Benutzer.</li>
</ol>