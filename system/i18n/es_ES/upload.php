<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
    'userfile_not_set'   => 'No existe ninguna variable &8220;post&8221; definida como %s.',
    'file_exceeds_limit' => 'El tamaño del fichero enviado supera el tamaño maximo permitido por la configuración de PHP',
    'file_partial'       => 'El fichero solo ha llegado parcialmente',
    'no_file_selected'   => 'No has seleccionado ningun fichero para enviar',
    'invalid_filetype'   => 'El tipo de fichero que estas intentando enviar no esta permitido.',
    'invalid_filesize'   => 'El fichero que estas intentando enviar supera el tamaño permitido (%s)',
    'invalid_dimensions' => 'La imagen que estas intentando enviar supera las dimensiones permitidas (%s)',
    'destination_error'  => 'Se ha encontrado un problema al tratar de mover el fichero enviado hacia su destino final.',
    'no_filepath'        => 'La ruta para guardar los fichero enviados no parece ser correcta.',
    'no_file_types'      => 'El tipo de fichero especificado no esta permitido.',
    'bad_filename'       => 'Un fichero con el mismo nombre del que has enviado ya existe en el servidor.',
    'not_writable'       => 'La carpeta seleccionada como destino, %s, no tiene permisos de escritura.',
    'error_on_file'      => 'Error enciando %s:',
    // Error code responses
    'set_allowed'        => 'Por seguridad, deberias definir los tipos de fichero que esta permitido enviar.',
    'max_file_size'      => 'Por seguridad, por favor no utilice MAX_FILE_SIZE para controlar el tamaño maximo permitido.',
    'no_tmp_dir'         => 'No es posible encontrar un fichero temporal donde escribir.',
    'tmp_unwritable'     => 'No es posible crear/escribir dentro del directorio especificado, %s.'
);