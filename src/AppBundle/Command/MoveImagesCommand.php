<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class MoveImagesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:move-images');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fu = $this->getContainer()->get('AppBundle\Foto\FotoUtils');

        //$output->writeln(scandir('./web'));

        $file = file_get_contents('./web/dump.json');
        $json = json_decode($file,true);
        $em = $this->getContainer()->get('doctrine')->getManager();
        $new_conn = $em->getConnection();

        $users = $new_conn->fetchAll('SELECT ID FROM user');
        foreach($users as $user){
            $ids[] = $user['ID'];
        }

        $i=0;

        $all = array();
        foreach($json['posts'] as $post_id => $post) if (in_array($post['post_author'],$ids) and isset($json['meta'][$post_id])) {

            $meta = $json['meta'][$post_id];

            if (isset($meta['images'])) foreach ($meta['images'] as $img) if(isset($json['allfotos'][$img]) and !in_array((int)$img,$all) and !in_array($img,array_keys($json['mainfotos']))) {
                $foto_url = $json['allfotos'][$img];
                $x_url = explode("/", $foto_url);



                $ext = explode(".",$x_url[7]);
                $from_img = './web/assets/images/source/'.$x_url[5].'/'.$x_url[6].'/'.$x_url[7];
                $to_img = './web/assets/images/cards/'.$x_url[5].'/'.$x_url[6].'/'.(int)$img.'.jpg';
                $to_thumb_img = './web/assets/images/cards/'.$x_url[5].'/'.$x_url[6].'/t/'.(int)$img.'.jpg';


                if (is_file($from_img)) $fu->moveResizeImage($from_img, $to_img, $to_thumb_img);
            }

            $i++;
        }

        $output->writeln('All fotos moved!!');
    }
}