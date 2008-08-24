<p class="intro">Kohana è un <strong>framework PHP 5</strong> che usa la struttura <strong>Model View Controller</strong>. Questo framework mira essenzialmente ad essere <strong>sicuro</strong>, <strong>leggero</strong> e <strong>facile</strong> da usare.</p>
<div style="float:left; padding-right: 4em;">
	<h2>Caratteristiche</h2>
	<ul>
		<li>Estrememente sicuro</li>
		<li>Eccezionalmente leggero</li>
		<li>Breve curva di apprendimento</li>
		<li>Usa la struttura <abbr title="Model View Controller">MVC</abbr></li>
		<li>UTF-8 compatibile al 100%</li>
		<li>Architettura <em>Loosely coupled</em></li>
		<li>Estremamente semplice da estendere</li>
	</ul>
</div>
<div style="float:left;">
	<h2>Tecnologia</h2>
	<ul>
		<li>PHP 5 <abbr title="Object Oriented Programming (Programmazione Orientata agli Oggetti)">OOP</abbr> rigorosa</li>
		<li>Semplice astrazione dei database attraverso helper SQL</li>
		<li>Driver di sessione multipli (nativo, database, cookie)</li>
		<!-- <li>Sistema di caching avanzato attraverso l'uso di drivers (file, database, memcache, shmop)</li> -->
		<li>Potente gestione degli eventi che permette modifiche dinamiche</li>
		<li>Originariamente basato su <?php echo html::anchor('http://www.codeigniter.com', 'CodeIgniter') ?></li>
	</ul>
</div>
<h3 style="clear:both;padding-top:1em;">Cosa ha di diverso Kohana?</h3>
<p>Sebbene Kohana utilizzi strutture e concetti ormai comuni, ci sono alcuni aspetti per cui Kohana riesce a distinguersi:</p>
<ol>
	<li><strong>È gestito da una comunità, non da un'azienda.</strong> Lo sviluppo di Kohana è gestito da un team di persone che necessitano di un framework per questioni di velocità e potenza delle soluzioni.</li>
	<li><strong>PHP 5 <abbr title="Object Oriented Programming (Programmazione Orientata agli Oggetti)">OOP</abbr> rigorosa.</strong> Offre numerosi vantaggi: protezione della visibilità, caricamento automatico delle classi, overloading, connessioni, astrazioni e unicità.</li>
	<li><strong>Estremamente leggero.</strong> Kohana non ha alcuna dipendenza da estensioni PECL o librerie PEAR. È stato evitato l'uso di librerie monolitiche a favore di soluzioni ottimizzate.</li>
	<li><strong>GET, POST, COOKIE, <em>e</em> SESSION funzionano come devono.</strong> Kohana non limita l'accessso alle variabili globali e offre al tempo stesso un filtro e una protezione da <abbr title="Cross Site Scripting">XSS</abbr>.</li>
	<li><strong>Caricamento automatico delle classi.</strong> Il caricamento delle classi avviene nel momento in cui lo necessita l'applicazione.</li>
	<li><strong>Nessun conflitto sui nomi.</strong> Tutti le classi hanno un suffisso per permettere l'uso di nomi simili tra i componenti, al fine di ottenere API il più coerenti possibile.</li>
	<li><strong><a href="http://upload.wikimedia.org/wikipedia/en/1/1c/Kohana-modules.png">Risorse a cascata</a> che offrono un'estendibilità senza precedenti.</strong> Praticamente ogni parte di  Kohana puà essere sostituita o estesa senza dover modificare alcuna parte del core. Il sistema di moduli permette di aggiungere plugin con file multipli alla tua applicazione in maniera trasparente.</li>
	<li><strong>Consistenza tra libreria di driver e API.</strong> Le librerie possono utilizzare diversi "driver" per gestire <abbr title="Application Programming Interface">API</abbr> esterne diverse in maniera trasparente. Per esempio, è possibile gestire contemporaneamente sessioni multiple di memorizzazione di dati (database, cookie e nativa), e al tempo stesso usare la stessa interfaccia tra tutte quante. Questo permette di sviluppare nuovi driver per le librerie esistenti, mantenendo così inalterate e trasparenti le API che le utilizzano.</li>
	<li><strong>Potente gestione degli eventi.</strong> Il sistema di gestione degli eventi incorpora una struttura di tipo <em>Observer</em>, la quale permette un estremo livello di personalizzazione sulla gestione degli eventi, potenzialmente senza limiti.</li>
	<li><strong>Rapido ciclo di sviluppo.</strong> Un rapido sviluppo ottiene come risultato una risposta veloce agli errori e alle richieste degli utenti.</li>
</ol>