<?php

namespace AppBundle\Twig\Extension;


class FileExtension extends \Twig_Extension
{

    /**
     * Return the functions registered as twig extensions
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('file_exists', 'file_exists'),
            new \Twig_SimpleFunction('main_foto', function($fotos){
                return $fotos->filter(function($foto) {
                    return $foto->getIsMain() === TRUE;
                })->first();
            }),
        );
    }

    public function getName()
    {
        return 'app_file';
    }
}
?>