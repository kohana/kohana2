<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'userfile_not_set'   => 'Não foi possível encontrar uma variável POST chamada %s.',
	'file_exceeds_limit' => 'The uploaded file exceeds the maximum allowed size in your PHP configuration file',
	'file_partial'       => 'O arquivo foi parcialmente enviado',
	'no_file_selected'   => 'Você não selecionou um arquivo para ser enviado',
	'invalid_filetype'   => 'O tipo de arquivo que você está tentando enviar não é permitido.',
	'invalid_filesize'   => 'The file you are attempting to upload is larger than the permitted size (%s)',
	'invalid_dimensions' => 'The image you are attempting to upload exceedes the maximum height or width (%s)',
	'destination_error'  => 'A problem was encountered while attempting to move the uploaded file to the final destination.',
	'no_filepath'        => 'The upload path does not appear to be valid.',
	'no_file_types'      => 'You have not specified any allowed file types.',
	'bad_filename'       => 'The file name you submitted already exists on the server.',
	'not_writable'       => 'The upload destination folder, %s, does not appear to be writable.',
	'error_on_file'      => 'Erro durante o processo de upload %s:',
	// Error code responses
	'set_allowed'        => 'Por questões de seguranca, você deve configurar os tipos de arquivos que são permitidos para serem enviados.',
	'max_file_size'      => 'Por questões de seguranca, por favor não utilize MAX_FILE_SIZE para controlar o tamanho máximo de upload.',
	'no_tmp_dir'         => 'Não foi possível encontrar um diretório temporário para escrita.',
	'tmp_unwritable'     => 'Could not create write to the configured upload directory, %s.'
);
