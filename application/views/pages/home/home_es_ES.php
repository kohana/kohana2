<p class="intro">Kohana es un <strong>framewor para PHP 5</strong> que implementa el patron de <strong>Modelo Vista Controlador</strong>. Sus principales objetivos se basan en ser <strong>seguro</strong>, <strong>ligero</strong>, y <strong>facil</strong> de utilizar.</p>
<div style="float:left; padding-right: 4em;">
	<h2>Características</h2>
	<ul>
		<li>Extremadamente seguro</li>
		<li>Extremadamente ligero</li>
		<li>Minima curva de aprendizaje</li>
		<li>Utiliza el patron <abbr title="Modelo Vista Controlador">MVC</abbr></li>
		<li>Compatibilidad UTF-8 100%</li>
		<li>Arquitectura <em>Loosely coupled</em></li>
		<li>Extremadamente sencilla de extender</li>
	</ul>
</div>
<div style="float:left;">
	<h2>Tecnología</h2>
	<ul>
		<li>PHP 5 <abbr title="Programación Orientada a Objetos">OOP</abbr> estricto</li>
		<li>Sencilla abstracción de base de datos mediante librerias SQL</li>
		<li>Multiples drivers de sesion (nativo, base de datos, y cookie)</li>
		<!-- <li>Sistema de cache avanzado mediante drivers (fichero, base de datos, memcache, shmop)</li> -->
		<li>Un Poderoso gestor de eventos que permite pequeñas modificaciones dinamicamente</li>
		<li>Originalmente basado en <?php echo html::anchor('http://www.codeigniter.com', 'CodeIgniter') ?></li>
	</ul>
</div>
<h3 style="clear:both;padding-top:1em;">&iquest;Que hace diferente a Kohana?</h3>
<p>Aunque Kohana reutiliza muchos patrones de programación y conceptos comunes, hay algunas cosas que lo hacen destacar:</p>
<ol>
	<li><strong>Guiado por la comunidad no por una compañia privada.</strong> el desarrollo de Kohana esta guiado por un equipo de personas que necesitan un framework para soluciones rapida y de gran potencia.</li>
	<li><strong>PHP 5 <abbr title="Programación Orientada a Objetos">OOP estricto</abbr>.</strong> Ofrece una gran cantidad de beneficios: protección de visibilidad, carga de clases automatica, overloading, interfaces, abstractas y singletons.</li>
	<li><strong>Extremadamente ligero.</strong> Kohana no tiene dependencias de extensiones PECL o librerias PEAR. Evita las librerias de tipo monolitico en favor de soluciones optimizadas.</li>
	<li><strong>GET, POST, COOKIE, <em>y</em> SESSION arrays funcionan tal y como se espera.</strong> Kohana no limita el acceso a los datos globales, a la vez que ofrece filtrado y proteccion <abbr title="Cross Site Scripting">XSS</abbr>.</li>
	<li><strong>Auto carga de clases real.</strong> Auto carga las clases bajo demanda, en el mismo momento que sean requeridas por tu aplicación.</li>
	<li><strong>Sin conflictos de espacios de nombre.</strong> Todas las clases tienen su propio sufijo para permitir nombres similares entre componentes, ofreciendo total coherencia en el API.</li>
	<li><strong>Los recursos en cascada ofrecen una extensibilidad sin precedentes.</strong> Cada una de las partes de Kohana puede ser sustituida o extendida sin editar ningun fichero del sistema base. El sistema de Modulos permite añadir extensiones con multiples ficheros de manera transparente.</li>
	<li><strong>El sistema de drivers en las librerias y la consistencia del API.</strong> Las librarias pueden hacer uso de diferentes "drivers" para manejar diferentes <abbr title="Application Programming Interface">API</abbr> externas de forma transparente. Por ejemplo, se incluyen multiples sistemas de almacenamiento para las sesiones (base de datos, cookies, y nativa), utilizando en todos los casos el mismo interface. Esto permite el desarrollo de nuevos drivers para las librerias existentes, manteniendo el API consistente y totalmente transparente.</li>
	<li><strong>Un gestor de eventos de gran potencia.</strong> El sistema de eventos implementa el patron <em>Observer</em>, lo que permite niveles casi sin limites de personalizacion.</li>
	<li><strong>Ciclo de desarrollo rapido.</strong> Un rapido desarrollo tiene como resultado una respuesta rapida a bugs y peticiones de los usuarios.</li>
</ol>