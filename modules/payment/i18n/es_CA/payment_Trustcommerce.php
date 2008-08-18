<?php

$lang = array
(
	'decline' => array
	(
		'avs'                => 'Fallada AVS; l\'adreça introduïda no coincideix amb l\'adreça de facturació que apareix en el fitxer del banc.',
		'cvv'                => 'Fallada CVV; el nombre proveït no és el nombre de verificació correcte per a la targeta.',
		'call'               => 'La targeta ha de ser autoritzada manualment mitjançant telèfon. Pots triar cridar al servei d\'atenció al client que apareix en la targeta i sol·licitar un nombre d\'autorització &8220;offline&8221;, que pot ser introduït en el camp &8220;offlineauthcode&8221; .',
		'expiredcard'        => 'La targeta ha caducat. Sol·liciti una ampliació de la data de caducitat a l\'entitat emissora.',
		'carderror'          => 'Nombre de targeta incorrecte, el que podria ser un error d\'escriptura, o en alguns casos una targeta denunciada com robada.',
		'authexpired'        => 'Intentant tornar a autoritzar una autorització prèvia que ha expirat (mes de 14 dies d\'antiguitat).',
		'fraud'              => 'La puntuació de frau de CrediGuard aquesta per sota del limiti sol·licitat.',
		'blacklist'          => 'S\'han superat els valors per a la llista negra de CrediGuard.',
		'velocity'           => 'S\'ha superat el control de velocitat de CrediGuard.',
		'dailylimit'         => 'S\'ha arribat a el limit diari de transaccions, ja sigui per nombre o quantitat.',
		'weeklylimit'        => 'S\'ha arribat a el limit setmanal de transaccions, ja sigui per nombre o quantitat.',
		'monthlylimit'       => 'S\'ha arribat a el límit mensual de transaccions, ja sigui per nombre o quantitat. ',
	),
	'baddata' => array
	(
		'missingfields'      => 'No ha enviat un o més paràmetres requerits per a aquest tipus de transacció.',
		'extrafields'        => 'S\'han enviat paràmetres no permesos per a aquest tipus de transacció.',
		'badformat'          => 'Un dels camps s\'ha emplenat de manera incorrecta, per exemple un caràcter no numèric en un camp numèric.',
		'badlength'          => 'Un dels camps és més llarg o més curt del que permet el servidor.',
		'merchantcantaccept' => 'El comerciant no accepta les dades introduïdes en aquest camp.',
		'mismatch'           => 'Les dades d\'un dels camps no coincideix amb el de l\'altre.',
	),
	'error' => array
	(
		'cantconnect'        => 'Impossible connectar amb la passarel·la TrustCommerce. Comprova la teva connexió a Internet i assegura que aquest en funcionament.',
		'dnsfailure'         => 'El programa TCLink no ha estat capaç de resoldre els noms DNS. Assegura\'t de poder resoldre en la màquina.',
		'linkfailure'        => 'La connexió s\'ha establert , però s\'ha interromput abans que la transacció finalitzés.',
		'failtoprocess'      => 'Els servidors del Banc estan fora de línia pel que resulta impossible autoritzar transaccions. Torni a intentar-lo dintre d\'uns minuts, o intenti\'l amb una targeta d\'altra entitat bancària.',
	),
);