<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'required' => 'Les champs suivants sont requis et n\'ont pas été remplis: %s',
	'gateway_connection_error' => 'Une erreur de connexion à la plateforme de paiement est survenue. Veuillez contacter le Webmaster si le problème persiste.',
	'invalid_certificate' => 'Le fichier de certificats suivant est invalide: %s',
	'no_dlib' => 'Impossible de charger la librairie dynamique suivante: %s',
	'error' => 'Une erreur s\'est produite lors de la transaction suivante: %s',

	'errors' => array('Trustcommerce' => array('decline' => array('avs' => 'Le Service de Vérification d\'Adresse (AVS) a retourné une erreur. L\'adresse entrée ne correspond pas à l\'adresse de facturation du fichier bancaire.',
	                                                              'cvv' => 'Le code de vérification (CVV) de votre carte n\'a pas été accepté. Le numéro que vous avez entré n\'est pas le bon ou ne correspond pas à cette carte.',
	                                                              'call' => 'La carte doit être autorisée par téléphone. Vous devez choisir ce numéro d\'appel parmis ceux listés sur la carte et demander un code d\'authentification offline (authcode). Celui-ci pourra ensuite être entré dans le champ réservé à cet effet.',
	                                                              'expiredcard' => 'La carte a expirée. Vous devez obtenir une carte possédant une date de validitée en cours auprès du fournisseur de celle-ci.',
	                                                              'carderror' => 'Le numéro de carte est invalide. Veuillez vérifier que vous avez correctement entré le numéro, ou que cette carte n\'ait pas été reportée comme étant volée.',
	                                                              'authexpired' => 'Tentative d\'autoriser une pré-autorisation qui a expirée il y a plus de 14 jours.',
	                                                              'fraud' => 'Le score de vérification est en dessous du score anti-fraude CrediGuard.',
	                                                              'blacklist' => 'CrediGuard donne cette valeur comme étant en liste noire (blacklistée).',
	                                                              'velocity' => 'Le seuil CrediGuard a été atteint. Trop de transactions on été effectués.',
	                                                              'dailylimit' => 'La limite journalière des transactions de cette carte a été atteinte.',
	                                                              'weeklylimit' => 'La limite hebdomadaire des transactions de cette carte a été atteinte.',
	                                                              'monthlylimit' => 'La limite mensuelle des transactions de cette carte a été atteinte.'),
	                                           'baddata' => array('missingfields' => 'Un ou plusieurs paramètres requis pour ce type de transaction n\'a pas été transmis',
	                                                              'extrafields' => 'Des paramètres interdits pour ce type de transaction ont été envoyés.',
	                                                              'badformat' => 'Un champ n\'a pas été formaté correctement, comme par exemple des caractères alphabétiques insérés dans un champ numérique.',
	                                                              'badlength' => 'Un champ est plus grand ou plus petit que la taill acceptée par le serveur.',
	                                                              'merchantcantaccept' => 'Le commercant ne peut accepter les données passées dans ce champ.',
	                                                              'mismatch' => 'Data in one of the offending fields did not cross-check with the other offending field.'),
	                                             'error' => array('cantconnect' => 'Impossible de se connecter à la plateforme TrustCommerce ! Veuillez vous assurer que votre connexion internet fonctionne.',
	                                                              'dnsfailure' => 'Le logiciel TCLink a été incapable de résoudre l\'adresse DNS du serveur. Assurez-vous que votre machine possède la capacité a résoudre les DNS.',
	                                                              'linkfailure' => 'La connexion n\'a pas pu être établie et vous avez été déconnecté avant que la transaction soit complète.',
	                                                              'failtoprocess' => 'Les serveurs bancaires ne sont pas disponibles actuellement et ne peuvent accepter des transactions. Veuillez réessayer dans quelques minutes. Vous pouvez également tester avec une autre carte.')))
);