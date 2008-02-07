<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'userfile_not_set'   => 'Incapaz de encontrar uma variável post chamada %s.',
	'file_exceeds_limit' => 'O tamanho do arquivo enviado excede o tamanho máximo permitido em seu arquivo de configuração do PHP',
	'file_partial'       => 'O arquivo foi enviado apenas parcialmente',
	'no_file_selected'   => 'Você não selecionou um arquivo para enviar',
	'invalid_filetype'   => 'O tipo de arquivo que você esta tentando enviar não é permitido.',
	'invalid_filesize'   => 'O arquivo que você esta tentando enviar é maior do que o limite de tamanho permitido (%s)',
	'invalid_dimensions' => 'A imagem que você esta tentando enviar ultrapassa o limite de altura ou largura (%s)',
	'destination_error'  => 'Foi encontrado um problema ao mover o arquivo enviado para o seu destino final.',
	'no_filepath'        => 'O caminho para enviar parece não ser válido.',
	'no_file_types'      => 'Você não especificou nenhum tipo de arquivo permitido.',
	'bad_filename'       => 'O arquivo que você enviou já existe no servidor.',
	'not_writable'       => 'O diretório de destino dos arquivos enviados, %s, parece não ser gravável.',
	'error_on_file'      => 'Erro enviando %s:',
	// Error code responses
	'set_allowed'        => 'Para segurança, você deve definir os tipos de arquivos que é permitido enviar.',
	'max_file_size'      => 'Para segurança, por favor não use MAX_FILE_SIZE para controlar o limite de tamanho do arquivo enviado.',
	'no_tmp_dir'         => 'Não foi possível encontrar um diretório temporário para o qual escrever.',
	'tmp_unwritable'     => 'Não foi possível escrever no diretório configurado para o envio, %s.'
);
