<p class="intro">Kohana es un <strong>framework para PHP 5</strong> que implementa el patrón de <strong>Modelo Vista Controlador</strong>. Sus principales objetivos se basan en ser <strong>seguro</strong>, <strong>ligero</strong>, y <strong>fácil</strong> de utilizar.</p>
<div style="float:left; padding-right: 4em;">
	<h2>Características</h2>
	<ul>
		<li>Extremadamente seguro</li>
		<li>Extremadamente ligero</li>
		<li>Mínima curva de aprendizaje</li>
		<li>Utiliza el patrón <abbr title="Modelo Vista Controlador">MVC</abbr></li>
		<li>Compatibilidad UTF-8 100%</li>
		<li>Arquitectura <em>Loosely coupled</em></li>
		<li>Extremadamente sencilla de extender</li>
	</ul>
</div>
<div style="float:left;">
	<h2>Tecnología</h2>
	<ul>
		<li>PHP 5 <abbr title="Programación Orientada a Objetos">OOP</abbr> estricto</li>
		<li>Sencilla abstracción de base de datos mediante librerías SQL</li>
		<li>Múltiples drivers de sesión (nativo, base de datos, y cookie)</li>
		<!-- <li>Sistema de cache avanzado mediante drivers (fichero, base de datos, memcache, shmop)</li> -->
		<li>Un Poderoso gestor de eventos que permite pequeñas modificaciones dinámicamente</li>
		<li>Originalmente basado en <?php echo html::anchor('http://www.codeigniter.com', 'CodeIgniter') ?></li>
	</ul>
</div>
<h3 style="clear:both;padding-top:1em;">&iquest;Que hace diferente a Kohana?</h3>
<p>Aunque Kohana reutiliza muchos patrones de programación y conceptos comunes, hay algunas cosas que lo hacen destacar:</p>
<ol>
	<li><strong>Guiado por la comunidad no por una compañía privada.</strong> el desarrollo de Kohana esta guiado por un equipo de personas que necesitan un framework para soluciones rápida y de gran potencia.</li>
	<li><strong>PHP 5 <abbr title="Programación Orientada a Objetos">OOP</abbr> estricto.</strong> Ofrece una gran cantidad de beneficios: protección de visibilidad, carga de clases automática, overloading, interfaces, abstractas y singletons.</li>
	<li><strong>Extremadamente ligero.</strong> Kohana no tiene dependencias de extensiones PECL o librerías PEAR. Evita las librerías de tipo monolítico en favor de soluciones optimizadas.</li>
	<li><strong>GET, POST, COOKIE, <em>y</em> SESSION funcionan tal y como se espera.</strong> Kohana no limita el acceso a los datos globales, a la vez que ofrece filtrado y protección <abbr title="Cross Site Scripting">XSS</abbr>.</li>
	<li><strong>Auto carga de clases real.</strong> Auto carga las clases bajo demanda, en el mismo momento que sean requeridas por tu aplicación.</li>
	<li><strong>Sin conflictos de espacios de nombre.</strong> Todas las clases tienen su propio sufijo para permitir nombres similares entre componentes, ofreciendo total coherencia en el API.</li>
	<li><strong>Los recursos en cascada ofrecen una extensibilidad sin precedentes.</strong> Cada una de las partes de Kohana puede ser sustituida o extendida sin editar ningun fichero del sistema base. El sistema de Módulos permite añadir extensiones con múltiples ficheros de manera transparente.</li>
	<li><strong>El sistema de drivers en las librerías y la consistencia del API.</strong> Las librerías pueden hacer uso de diferentes "drivers" para manejar diferentes <abbr title="Application Programming Interface">API</abbr> externas de forma transparente. Por ejemplo, se incluyen múltiples sistemas de almacenamiento para las sesiones (base de datos, cookies, y nativa), utilizando en todos los casos el mismo interface. Esto permite el desarrollo de nuevos drivers para las librerías existentes, manteniendo el API consistente y totalmente transparente.</li>
	<li><strong>Un gestor de eventos de gran potencia.</strong> El sistema de eventos implementa el patrón <em>Observer</em>, lo que permite niveles casi sin limites de personalización.</li>
	<li><strong>Ciclo de desarrollo rápido.</strong> Un rápido desarrollo tiene como resultado una respuesta rápida a errores y peticiones de los usuarios.</li>
</ol>