<?php defined('SYSPATH') or die('No direct script access.'); 

$lang = array
(
    'driver_not_supported'    => 'No se encuentra el driver, %s, requerido por la librería Image.',
    // CI's Image_lib stuff below
    'source_image_required'   => 'Debes especificar un origen de imagen en las preferencias.',
    'gd_required'             => 'La librería de imágenes GD es necesaria para ésta característica.',
    'gd_required_for_props'   => 'Tu servidor ha de soportar librería de imágenes GD para poder determinar las propiedades de la imagen.',
    'unsupported_imagecreate' => 'Tu servidor no soporta la función GD necesaria para procesar éste tipo de imagen.',
    'gif_not_supported'       => 'Las imágenes GIF no suelen estar soportadas por las restricciones de licencia. Debes usar imá,genes JPG o PNG en su lugar.',
    'jpg_not_supported'       => 'Las imágenes JPG no están soportadas',
    'png_not_supported'       => 'Las imágenes PNG no están soportadas',
    'jpg_or_png_required'     => 'El protocolo de cambio de tamaño de una imagen especificado en tus preferencias sólo funciona con imágenes de tipo JPEG ó PNG.',
    'copy_error'              => 'Ocurrió un error cuando se intentó reemplazar el fichero. Por favor, asegúrate de que tu directorio de ficheros tiene permisos de escritura.',
    'rotate_unsupported'      => 'La rotación de imágenes parece no estar soportada por tu servidor.',
    'libpath_invalid'         => 'La ruta a tu librería de imagen no es correcta. Por favor establece la ruta correcta en las preferencias de imagen.',
    'image_process_failed'    => 'Procesamiento de la Imagen erróneo. Por favor ,verifica que tu servidor soporta el protocolo elegido y que la ruta de tu librería de imagen es correcta.',
    'rotation_angle_required' => 'Necesito un ángulo de rotación para rotar la imagen.',
    'writing_failed_gif'      => 'La rutina que guarda las imágenes GIF ha devuelto un error.',
    'invalid_path'            => 'La ruta a la imágenes no es correcta',
    'copy_failed'             => 'La rutina de copia de la imagen ha fallado.',
    'missing_font'            => 'Imposible encontrar una fuente a utilizar.'
);
