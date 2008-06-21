<p class="intro">Kohana est un <strong>Framework PHP 5</strong> qui utilise l'architecture <strong>Modèle Vue Controleur</strong>. Il vise à être <strong>sécurisé</strong>, <strong>léger</strong>, et <strong>facile</strong> à utiliser.</p>
<div style="float:left; padding-right: 4em;">
	<h2>Fonctionnalités</h2>
	<ul>
		<li>Sécurisé</li>
		<li>Léger</li>
		<li>Aprentissage très rapide</li>
		<li>Utilise l'architecture <abbr title="Model View Controller">MVC</abbr></li>
		<li>100% compatible UTF-8</li>
		<!-- <li>Loosely coupled architecture</li> -->
		<li>Très facile à étendre</li>
	</ul>
</div>
<div style="float:left;">
	<h2>Technologies</h2>
	<ul>
		<li>Programmation <abbr title="Object Oriented Programming">OOP</abbr> PHP 5 Stricte</li>
		<li>Abstraction simple de la base de données grâce aux "helpers"</li>
		<li>Plusieurs implémentation des sessions (native, base de données, et cookie)</li>
		<!-- <li>Système avancé de gestion du cache (fichier, base de données, memcache, shmop)</li> -->
		<li>Système à base d'évènement très puissant facilitant les modifications</li>
		<li>Basé sur <?php echo html::anchor('http://www.codeigniter.com', 'CodeIgniter') ?></li>
	</ul>
</div>
<h3 style="clear:both;padding-top:1em;">Pourquoi Kohana est-il différent?</h3>
<p>Bien que Kohana réutilise de nombreux concepts et design patterns courants, les caractéristiques suivantes font que Kohana est différent:</p>
<ol>
	<li><strong>Projet communautaire et non lié à une compagnie.</strong> Le développement de Kohana est conduit par une équipe de gens motivés ayant besoin d'un framework pour rapidement construire des applications puissantes.</li>
	<li><strong>Programmation <abbr title="Object Oriented Programming">OOP</abbr> PHP 5 Stricte.</strong> Offre de nombreux avantages: protection de la visibilité, chargement automatique de classes, surcharge, interfaces, abstraction et singletons.</li>
	<li><strong>Très léger.</strong> Kohana n'a aucune dépendances par rapport aux extensions PECL ou aux librairies PEAR. Les librairies volumineuses et monolithiques sont évitées au profit de solutions optimisées.</li>
	<li>Les tableaux <strong>GET, POST, COOKIE, <em>et</em> SESSION fonctionnent tels quels.</strong> Kohana ne limite pas votre accès aux données globales mais offre des protections et du filtrage <abbr title="Cross Site Scripting">XSS</abbr>.</li>
	<li><strong>Véritable chargement automatique de classes.</strong> Les classes sont chargées au moment où elles sont requises dans votre application.</li>
	<li><strong>Pas de conflits de nommage.</strong> Toutes les classes sont suffixées pour permettre des noms similaire entre les composants et ainsi obtenir une API plus cohérente.</li>
	<li><strong>Extension grâce aux chargements en cascade des ressources.</strong> Preque toutes les parties de Kohana peuvent être surchargées ou étendues sans éditer le coeur du système. Les modules permettent l'ajout de plugins de façon transparente même si ceux-ci comprenent de multiples fichiers.</li>
	<li><strong>Drivers de librairies et API cohérente.</strong> Les librairies peuvent utiliser différents "drivers" pour supporter différentes <abbr title="Application Programming Interface">API</abbr>s externes de façon transparente. Par exemple, plusieurs conteneurs de session sont disponibles (base de données, cookie, et natif), mais c'est la même interface qui est utilisée pour tous ceux-ci, ce qui permet de garder l'API consistante et transparente.</li>
	<li><strong>Système d'évenement puissant.</strong> Un support des évènements inspiré du design pattern Observer offre un niveau de personnalisation extrême.</li>
	<li><strong>Cycle de développement rapide.</strong> Un développement rapide où les bugs et les améliorations reportées par les utilisateur sont pris en compte très rapidement.</li>
</ol>