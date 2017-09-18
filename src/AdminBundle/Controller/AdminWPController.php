<?php

namespace AdminBundle\Controller;

use AppBundle\Foto\FotoUtils;
use AppBundle\Menu\MenuCity;
use AppBundle\Menu\MenuGeneralType;
use AppBundle\Menu\MenuMarkModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminWPController extends Controller
{
    /**
     * @Route("/wpUserBase")
     */
    public function wpUserBaseAction()
    {
        $em = $this->get('doctrine')->getManager();
        $wp = $this->get('doctrine')->getManager('wpcon');
        $conn = $wp->getConnection();
        $new_conn = $em->getConnection();

        $users = $conn->fetchAll('SELECT * FROM wp_users');

        foreach($users as $user){
            $ids[] = $user['ID'];
            $u_sql[] = '('.$user['ID'].",'".$user['user_email']."','".$user['user_pass']."','".$user['display_name']."','".$user['user_registered']."',1,'','".$user['user_login']."')";
            if($user['user_url']!='') $inf[] = "(".$user['ID'].","."'website','".$user['user_url']."')";
        }
        $ids = implode(",",$ids);

        $usermeta = $conn->fetchAll("SELECT *,p.guid FROM wp_usermeta m 
LEFT JOIN wp_posts p ON (p.ID = m.meta_value) AND m.meta_key = 'user_photo'
WHERE user_id IN ($ids)");
        foreach($usermeta as $m){
            if($m['meta_key'] == 'user_mobile' and $m['meta_value'] != '') $inf[] = "(".$m['user_id'].","."'phone','".$m['meta_value']."')";
            if($m['meta_key'] == 'user_photo' and $m['guid']!='') $inf[] = "(".$m['user_id'].","."'foto','".$m['ID']."')";
        }

        $user_sql = 'INSERT INTO user (id,email,password,header,date_create,is_activated,activate_string,login) VALUES '.implode(",",$u_sql).';';
        $new_conn->query($user_sql);

        $info_sql = 'INSERT INTO user_info (user_id,ui_key,ui_value) VALUES '.implode(",",$inf).';';
        $new_conn->query($info_sql);

        echo 'done';
        return new Response();
    }

    /**
     * @Route("/wp")
     */
    public function wpAction()
    {
        echo 'ok';
        //$file = file_get_contents('dump.json');
        //$json = json_decode($file,true);
        //var_dump($json['mainfotos']);
        return new Response();
    }

    /**
     * @Route("/wpSaveFile")
     */
    public function wpCard()
    {
        if(is_file('dump.json')) unlink('dump.json');

        $em = $this->get('doctrine')->getManager();
        $wp = $this->get('doctrine')->getManager('wpcon');
        $conn = $wp->getConnection();
        $new_conn = $em->getConnection();

        $sql = "SELECT * FROM wp_posts WHERE post_type='listing' AND post_status='publish'";

        $posts = $conn->fetchAll($sql);
        foreach($posts as $post){
            $ids[] = (int)$post['ID'];
            $p[(int)$post['ID']] = $post;
        }
        $ids = implode(",",$ids);

        $sql = "SELECT * FROM wp_postmeta WHERE post_id IN ($ids)";
        $result1 = $conn->fetchAll($sql);

        foreach($result1 as $row){
            if($row['meta_key'] == 'webbupointfinder_item_images' and $row['meta_value']!='' and $row['meta_value']!='[object Object]'){
                $meta[(int)$row['post_id']]['images'][] = $row['meta_value'];
                $fotos[] = $row['meta_value'];
            }
            else $meta[(int)$row['post_id']][$row['meta_key']] = $row['meta_value'];
            if($row['meta_key'] == '_thumbnail_id') $main_foto[(int)$row['post_id']] = $row['meta_value'];
        }

        $sql = "SELECT r.term_taxonomy_id,t.taxonomy,r.object_id FROM wp_term_relationships  r
                LEFT JOIN wp_term_taxonomy t ON (t.term_taxonomy_id = r.term_taxonomy_id)
                WHERE r.object_id IN ($ids)";

        $result2 = $conn->fetchAll($sql);
        foreach($result2 as $row){
            $taxonomy = $row['taxonomy'];
            if ($taxonomy == 'pointfinderconditions') $cond[(int)$row['object_id']] = (int)$row['term_taxonomy_id'];
            if ($taxonomy == 'pointfinderfeatures') $tax[(int)$row['object_id']][] = (int)$row['term_taxonomy_id'];
            if ($taxonomy == 'pointfinderltypes') $cats[(int)$row['object_id']] = (int)$row['term_taxonomy_id'];
        }

        $main_foto_ids = implode(",",$main_foto);
        $sql = "SELECT ID,guid FROM wp_posts WHERE post_type = 'attachment' AND ID IN ($main_foto_ids)";
        $result3 = $conn->fetchAll($sql);
        foreach($result3 as $mainfoto){
            $mf[(int)$mainfoto['ID']] = $mainfoto['guid'];
        }

        $all_foto_ids = implode(",",$fotos);
        $sql = "SELECT ID,guid FROM wp_posts WHERE post_type = 'attachment' AND ID IN ($all_foto_ids)";
        $result4 = $conn->fetchAll($sql);
        foreach($result4 as $allfoto){
            $af[(int)$allfoto['ID']] = $allfoto['guid'];
        }

        $file =json_encode(array('posts'=>$p, 'meta'=>$meta, 'features'=>$tax, 'categories'=>$cats, 'conditions' => $cond, 'mainfotos' => $mf, 'allfotos' => $af));

        file_put_contents('dump.json',$file);

        echo 'file saved!';

        return new Response();
    }

    private function checkMeta($key,$meta){
        if(isset($meta[$key]) and $meta[$key] != '' and $meta[$key] != ',') return $meta[$key];
        else return null;
    }

    private function conditions($c)
    {
        $conds = array(
        '99' => '1',
        '100' => '2',
        '101' => '4',
        '102' => '3');
        if($c!='') return $conds[$c]; else return 2;
    }

    private function serviceType($t)
    {
        $type = 1;
        if ($t == '02') $type = 2;
        return $type;
    }

    private function color($c)
    {
        $color = 1;
        if ($c != '') $color = (int)$c;
        if($color<1) $color = 1;
        if($color>9) $color = 9;
        return $color;
    }

    private function general($g)
    {
        $catArray = array(
        "32" => "2",
        "61" => "2",
        "98"=>"3",
        "137"=>"4",
        "136"=>"5",
        "227"=>"6",
        "93"=>"8",
        "139"=>"9",
        "138"=>"10",
        "96"=>"12",
        "97"=>"13",
        "94"=>"14",
        "95"=>"15",
        "140"=>"16",
        "141"=>"17",
        "228"=>"18",
        "142"=>"19",
        "2169"=>"2"
        );
        return $catArray[$g];
    }


    private function fillCity($adress) // ready
    {
        $em = $this->get('doctrine')->getManager();
        $new_conn = $em->getConnection();

        $filled = false;
        $adress = explode(",", $adress);
        foreach ($adress as $adr) {
            $adr = trim(str_replace('г.', '', $adr));
            $sql = "SELECT id FROM city WHERE parent_id!=0 AND city.header LIKE '%" . $adr . "%'";
            $result = $new_conn->fetchColumn($sql);
            if ($result != 0) {
                $city = $result;
                $filled = true;
            }
            if ($adr=='Москва'){
                $city = 77;
                $filled = true;
            }
            if ($adr=='Санкт-Петербург'){
                $city = 78;
                $filled = true;
            }
            if ($adress == ''){
                $city = 77;
                $filled = true;
            }
        }
        if(!$filled){
            $city = 77;
        }
        return $city;
    }


    private function getNonModel($parent_id)
    {
        $em = $this->get('doctrine')->getManager();
        $new_conn = $em->getConnection();
        return $new_conn->fetchColumn("SELECT id FROM mark WHERE parent_id=$parent_id AND header='---'");
    }

    private function fillModel($header) // ready
    {
        $model = 20991;
        $header = explode(" ", $header);

        $marks = $this->checkCount($header,NULL);
        if ($marks != 0) {
            //echo 'parent - '.$header[$parent[1]].'<br>';
            unset($header[$marks[1]]);
            foreach ($marks[0] as $mark) {
                $model = $this->checkCount($header, $mark['id']);
                if ($model == 0) {

                    //$mark = $this->getNonModel($parent[0]);
                    //echo 'no model, set:'.$mark.'<br>';
                } else {
                    $model = $model[0][0]['id'];
                    return $model;
                }
            }
        } else {
            //echo 'no parent<br>';
            $model = $this->checkCount($header,'all');
            if ($model != 0){
                $model = $model[0][0]['id'];
                //echo 'set:'.$mark.'<br>';
            }
        }
        if ($model == 0) $model = 20991;
        return $model;
    }

    private function checkCount($array, $parent_id){

        $em = $this->get('doctrine')->getManager();
        $new_conn = $em->getConnection();

        foreach ($array as $key=>$mark_name){
            $mark_name = trim(str_replace('.','',$mark_name));

            if(mb_strlen($mark_name,'UTF-8')>2) {

                $sql = "SELECT id FROM car_model WHERE car_mark_id=$parent_id AND header LIKE '%" . $mark_name . "%'";
                if ($parent_id == NULL) $sql = "SELECT id FROM car_mark WHERE header LIKE '%" . $mark_name . "%'";
                if ($parent_id == 'all') $sql = "SELECT id FROM car_model WHERE header LIKE '%" . $mark_name . "%'";

                $result = $new_conn->fetchAll($sql);
                if (count($result) > 0)  {
                    return array($result,$key);
                }
            }
        }
        return 0;
    }



    /**
     * @Route("/wpModelSet")
     */
    public function wpModelSet()
    {

        $em = $this->get('doctrine')->getManager();
        $conn = $em->getConnection();

        $cards = $conn->fetchAll('SELECT id,header FROM card');
        foreach ($cards as $card) {
            $model_id = $this->fillModel($card['header']);
            $id = $card['id'];
            $sql = "UPDATE card SET model_id = $model_id WHERE id=$id";
            $conn->query($sql);
        }

    }



    /**
     * @Route("/wpInsert")
     */
    public function wpInsert()
    {

        $file = file_get_contents('dump.json');
        $json = json_decode($file,true);
        $em = $this->get('doctrine')->getManager();
        $new_conn = $em->getConnection();

        $users = $new_conn->fetchAll('SELECT ID FROM user');
        foreach($users as $user){
            $ids[] = $user['ID'];
        }

        $sql = 'INSERT INTO card (id,header,content,prod_year,general_type_id,condition_id,service_type_id,model_id,color_id,user_id,is_active,date_create,date_update,date_expiry,views,city_id,is_top,coords,address,street_view,video) VALUES ';

        $i=0;

        foreach($json['posts'] as $post_id => $post){

            if($i>=0 and in_array($post['post_author'],$ids)) {
                $meta = $json['meta'][$post_id];
                $s = array();
                $s[] = "(" . (int)$post_id;
                $s[] = $new_conn->quote($post['post_title'], \PDO::PARAM_STR);
                $s[] = $new_conn->quote($post['post_content'], \PDO::PARAM_STR);
                $s[] = (int)$this->checkMeta('webbupointfinder_item_transportyear', $meta);
                $s[] = (int)$this->general($json['categories'][$post_id]);
                if (isset($json['conditions'][$post_id])) $s[] = (int)$this->conditions($json['conditions'][$post_id]); else $s[] = (int)$this->conditions('');
                if (isset($meta['webbupointfinder_item_serv'])) $s[] = (int)$this->serviceType($meta['webbupointfinder_item_serv']); else $s[] = (int)$this->serviceType('');
                $s[] = (int)$this->fillMark($post['post_title']);
                if (isset($meta['webbupointfinder_item_transportcolor'])) $s[] = (int)$this->color($meta['webbupointfinder_item_transportcolor']); else $s[] = (int)$this->color('');
                $s[] = (int)$post['post_author'];
                $s[] = 1;
                $s[] = "'" . $post['post_date'] . "'";
                $s[] = 'NOW()';
                $s[] = "'2020-01-01'";
                $s[] = (int)$this->checkMeta('webbupointfinder_page_itemvisitcount', $meta);
                $s[] = (int)$this->fillCity($this->checkMeta('webbupointfinder_items_address', $meta));
                $s[] = (int)$this->checkMeta('webbupointfinder_item_featuredmarker', $meta);
                $s[] = "'" . $this->checkMeta('webbupointfinder_items_location', $meta) . "'";
                $s[] = $new_conn->quote($this->checkMeta('webbupointfinder_items_address', $meta), \PDO::PARAM_STR);
                $s[] = $new_conn->quote($this->checkMeta('webbupointfinder_item_streetview', $meta), \PDO::PARAM_STR);
                $s[] = $new_conn->quote($this->checkMeta('webbupointfinder_item_video', $meta), \PDO::PARAM_STR) . ")";
                $string[] = implode(",", $s);
            }
            $i++;
            //if($i==100) break;
        }

        $string = implode(",", $string);

        $new_conn->query($sql.$string.';');

        echo 'cards inserted!';

        return new Response();
    }

    /**
     * @Route("/wpPrice")
     */
    public function wpPrice()
    {

        $file = file_get_contents('dump.json');
        $json = json_decode($file,true);
        $em = $this->get('doctrine')->getManager();
        $new_conn = $em->getConnection();

        $users = $new_conn->fetchAll('SELECT ID FROM user');
        foreach($users as $user){
            $ids[] = $user['ID'];
        }

        $sql = 'INSERT INTO card_price (card_id,price_id,value) VALUES ';

        $i=0;

        foreach($json['posts'] as $post_id => $post) if (in_array($post['post_author'],$ids)) {

            $meta = $json['meta'][$post_id];
            $s = array();
            if(isset($meta['webbupointfinder_item_priceforrenthour']) and $meta['webbupointfinder_item_priceforrenthour']!=0) {
                $s[] = '(' . (int)$post_id;
                $s[] = 1;
                $s[] = (int)trim(str_replace(" ", "", $meta['webbupointfinder_item_priceforrenthour'])) . ')';
            }

            if(isset($meta['webbupointfinder_item_priceforrentday']) and $meta['webbupointfinder_item_priceforrentday']!=0) {
                $s[] = '('.(int)$post_id;
                $s[] = 2;
                $s[] = (int)trim(str_replace(" ","",$meta['webbupointfinder_item_priceforrentday'])).')';
            }

            if(isset($meta['webbupointfinder_item_priceforrent3day']) and $meta['webbupointfinder_item_priceforrent3day']!=0) {
                $s[] = '('.(int)$post_id;
                $s[] = 3;
                $s[] = (int)trim(str_replace(" ","",$meta['webbupointfinder_item_priceforrent3day'])).')';
            }

            if(isset($meta['webbupointfinder_item_field499001607305037600000']) and $meta['webbupointfinder_item_field499001607305037600000']!=0) {
                $s[] = '('.(int)$post_id;
                $s[] = 4;
                $s[] = (int)trim(str_replace(" ","",$meta['webbupointfinder_item_field499001607305037600000'])).')';
            }

            if(isset($meta['webbupointfinder_item_priceforrentmonth']) and $meta['webbupointfinder_item_priceforrentmonth']!=0) {
                $s[] = '('.(int)$post_id;
                $s[] = 5;
                $s[] = (int)trim(str_replace(" ","",$meta['webbupointfinder_item_priceforrentmonth'])).')';
            }

            if(!empty($s)) $string[] = implode(",", $s);

            $i++;
            //if ($i==100) break;
        }

        $string = implode(",", $string);

        $new_conn->query($sql.$string.';');

        echo 'prices inserted!';

        return new Response();
    }


    /**
     * @Route("/wpSubs")
     */
    public function wpSubs()
    {

        $bodies = array(
            '0'=>'3',
            '1'	=>'3',
            '2'	=>'5',
            '3'	=>'6',
            '4'	=>'7',
            '5'	=>'8',
            '6'	=>'9',
            '7'	=>'10',
            '8'	=>'11',
            '9'	=>'12',
            '10'=>'13',
            '11'=>'14'
        );

        $eng = array(
            '0'=>'18',
          '1'=>'18',
          '2'=>'19',
          '3'=>'20'
        );

        $file = file_get_contents('dump.json');
        $json = json_decode($file,true);
        $em = $this->get('doctrine')->getManager();
        $new_conn = $em->getConnection();

        $users = $new_conn->fetchAll('SELECT ID FROM user');
        foreach($users as $user){
            $ids[] = $user['ID'];
        }

        $sql = 'INSERT INTO field_integer (card_id,card_field_id,value) VALUES ';

        $i=0;

        foreach($json['posts'] as $post_id => $post) if (in_array($post['post_author'],$ids)) {

            $meta = $json['meta'][$post_id];
            $s = array();
            if(isset($meta['webbupointfinder_item_transportmiles'])) {
                $s[] = '(' . (int)$post_id;
                $s[] = 1;
                $s[] = (int)trim(str_replace(" ", "", $meta['webbupointfinder_item_transportmiles'])) . ')';
            }

            if(isset($meta['webbupointfinder_item_transportbodytype'])) {
                $s[] = '('.(int)$post_id;
                $s[] = 3;
                $s[] = (int)trim(str_replace(" ","",$bodies[(int)$meta['webbupointfinder_item_transportbodytype']])).')';
            }

            if(isset($meta['webbupointfinder_item_transportenginety'])) {
                $s[] = '('.(int)$post_id;
                $s[] = 4;
                $s[] = (int)trim(str_replace(" ","",$eng[(int)$meta['webbupointfinder_item_transportenginety']])).')';
            }

            if(!empty($s)) $string[] = implode(",", $s);

            $i++;
            //if ($i==100) break;
        }

        $string = implode(",", $string);

        $new_conn->query($sql.$string.';');

        echo 'subs inserted!';

        return new Response();
    }

    /**
     * @Route("/wpFeat")
     */
    public function wpFeat()
    {

        $features = array(
            '3' => '6',
            '4' => '7',
            '6' => '8',
            '8' => '9',
            '9' => '10',
            '11' => '11',
            '13' => '12',
            '14' => '13',
            '15' => '14',
            '18' => '15',
            '24' => '16',
            '25' => '17',
        );

        $file = file_get_contents('dump.json');
        $json = json_decode($file,true);
        $em = $this->get('doctrine')->getManager();
        $new_conn = $em->getConnection();

        $users = $new_conn->fetchAll('SELECT ID FROM user');
        foreach($users as $user){
            $ids[] = $user['ID'];
        }

        $sql = 'INSERT INTO card_feature (card_id,feature_id) VALUES ';

        $i=0;

        foreach($json['posts'] as $post_id => $post) if (in_array($post['post_author'],$ids) and isset($json['features'][$post_id])) {

            $ft = $json['features'][$post_id];
            $s = array();

            foreach($ft as $f) {
                $s[] = '(' . (int)$post_id;
                $s[] = (int)$features[$f] . ')';
            }
            if(!empty($s)) $string[] = implode(",", $s);

            $i++;
            //if ($i==100) break;
        }

        $string = implode(",", $string);

        $new_conn->query($sql.$string.';');

        echo 'feat inserted!';

        return new Response();
    }



    /**
     * @Route("/wpFotoInsertMain")
     */
    public function wpFotoInsertMain()
    {
        $file = file_get_contents('dump.json');
        $json = json_decode($file,true);
        $em = $this->get('doctrine')->getManager();
        $new_conn = $em->getConnection();

        $users = $new_conn->fetchAll('SELECT ID FROM user');
        foreach($users as $user){
            $ids[] = $user['ID'];
        }

        $sql = 'INSERT INTO foto (id,card_id,folder,is_main) VALUES ';

        $i=0;

        foreach($json['posts'] as $post_id => $post) if (in_array($post['post_author'],$ids) and isset($json['meta'][$post_id]) ) {

            $meta = $json['meta'][$post_id];

            $mainfoto_url = $json['mainfotos'][$meta['_thumbnail_id']];
            $x_url = explode("/",$mainfoto_url);

                $s = array();

                    $s[] = '(' . (int)$meta['_thumbnail_id'];
                    $s[] = (int)$post_id;
                    $s[] = "'".$x_url[5].'/'.$x_url[6]."'";
                    $s[] = '1)';

                if(!empty($s)) $string[] = implode(",", $s);

            $i++;

        }

        $string = implode(",", $string);

        $new_conn->query($sql.$string.';');

        echo 'fotos inserted!';

        return new Response();
    }

    /**
     * @Route("/wpFotoMoveMain")
     */
    public function wpFotoMoveMain(FotoUtils $fu)
    {
        $file = file_get_contents('dump.json');
        $json = json_decode($file,true);
        $em = $this->get('doctrine')->getManager();
        $new_conn = $em->getConnection();

        $users = $new_conn->fetchAll('SELECT ID FROM user');
        foreach($users as $user){
            $ids[] = $user['ID'];
        }

        $i=0;

        foreach($json['posts'] as $post_id => $post) if (in_array($post['post_author'],$ids) and isset($json['meta'][$post_id]) ) {

            $meta = $json['meta'][$post_id];

            $mainfoto_url = $json['mainfotos'][$meta['_thumbnail_id']];
            $x_url = explode("/",$mainfoto_url);
            $ext = explode(".",$x_url[7]);
            $from_img = $_SERVER['DOCUMENT_ROOT'].'/assets/images/source/'.$x_url[5].'/'.$x_url[6].'/'.$x_url[7];
            $to_img = $_SERVER['DOCUMENT_ROOT'].'/assets/images/cards/'.$x_url[5].'/'.$x_url[6].'/'.(int)$meta['_thumbnail_id'].'.jpg';
            $to_thumb_img = $_SERVER['DOCUMENT_ROOT'].'/assets/images/cards/'.$x_url[5].'/'.$x_url[6].'/t/'.(int)$meta['_thumbnail_id'].'.jpg';


            @$fu->moveResizeImage($from_img, $to_img, $to_thumb_img);


            $i++;
            //if ($i==50) break;
        }

        echo 'fotos moved!';

        return new Response();
    }


    /**
     * @Route("/wpFotoInsertAll")
     */
    public function wpFotoInsertAll()
    {
        $file = file_get_contents('dump.json');
        $json = json_decode($file,true);
        $em = $this->get('doctrine')->getManager();
        $new_conn = $em->getConnection();

        $users = $new_conn->fetchAll('SELECT ID FROM user');
        foreach($users as $user){
            $ids[] = $user['ID'];
        }

        $sql = 'INSERT INTO foto (id,card_id,folder,is_main) VALUES ';

        $i=0;

        $all = array();
        foreach($json['posts'] as $post_id => $post) if (in_array($post['post_author'],$ids) and isset($json['meta'][$post_id])) {

            $meta = $json['meta'][$post_id];

            if (isset($meta['images'])) foreach ($meta['images'] as $img) if(isset($json['allfotos'][$img]) and !in_array((int)$img,$all) and !in_array($img,array_keys($json['mainfotos']))) {
                $foto_url = $json['allfotos'][$img];
                $x_url = explode("/", $foto_url);

                $s = array();

                $s[] = '(' . (int)$img;
                $s[] = (int)$post_id;
                $s[] = "'" . $x_url[5] . '/' . $x_url[6] . "'";
                $s[] = '0)';

                $all[] = (int)$img;
                if(!empty($s)) $string[] = implode(",", $s);
            }

            $i++;
        }



        $string = implode(",", $string);

        $new_conn->query($sql.$string.';');

        echo 'All fotos inserted!';

        return new Response();
    }

    /**
     * @Route("/wpFotoMoveAll")
     */
    public function wpFotoMoveAll(FotoUtils $fu)
    {
        $file = file_get_contents('dump.json');
        $json = json_decode($file,true);
        $em = $this->get('doctrine')->getManager();
        $new_conn = $em->getConnection();

        $users = $new_conn->fetchAll('SELECT ID FROM user');
        foreach($users as $user){
            $ids[] = $user['ID'];
        }

        $sql = 'INSERT INTO foto (id,card_id,folder,is_main) VALUES ';

        $i=0;

        $all = array();
        foreach($json['posts'] as $post_id => $post) if (in_array($post['post_author'],$ids) and isset($json['meta'][$post_id])) {

            $meta = $json['meta'][$post_id];

            if (isset($meta['images'])) foreach ($meta['images'] as $img) if(isset($json['allfotos'][$img]) and !in_array((int)$img,$all) and !in_array($img,array_keys($json['mainfotos']))) {
                $foto_url = $json['allfotos'][$img];
                $x_url = explode("/", $foto_url);



                $ext = explode(".",$x_url[7]);
                $from_img = $_SERVER['DOCUMENT_ROOT'].'/assets/images/source/'.$x_url[5].'/'.$x_url[6].'/'.$x_url[7];
                $to_img = $_SERVER['DOCUMENT_ROOT'].'/assets/images/cards/'.$x_url[5].'/'.$x_url[6].'/'.(int)$img.'.jpg';
                $to_thumb_img = $_SERVER['DOCUMENT_ROOT'].'/assets/images/cards/'.$x_url[5].'/'.$x_url[6].'/t/'.(int)$img.'.jpg';


                @$fu->moveResizeImage($from_img, $to_img, $to_thumb_img);
            }

            $i++;
        }

        echo 'All fotos moved!';

        return new Response();
    }




}
//        wpid = ?,
//        header = ?,
//        content = ?,
//        mark_id = ?,
//        category_id = ?,
//        coords = ?,
//        probeg = ?,
//        prod_year = ?,
//        price_hour = ?,
//        price_day = ?,
//        price_3day = ?,
//        price_week = ?,
//        price_month = ?,
//        body_type = ?,
//        engine_type_id = ?,
//        service_type_id = ?,
//        transport_type_id = ?,
//        date_create = ?,
//        city_id = ?,
//        adress = ?,
//        user_id = ?,
//        views = ?,
//        color_id = ?
//        ";
//$this->db->query( $sql, array(
//    $object['ID'],
//    $object['post_title'],
//    $object['post_content'],
//    $this->checkMeta('webbupointfinder_item_brands', $meta),
//    0,
//    $this->checkMeta('webbupointfinder_items_location', $meta),
//    $this->checkMeta('webbupointfinder_item_transportmiles', $meta),
//    $this->checkMeta('webbupointfinder_item_transportyear', $meta),
//    $this->checkMeta('webbupointfinder_item_priceforrenthour', $meta),
//    $this->checkMeta('webbupointfinder_item_priceforrentday', $meta),
//    $this->checkMeta('webbupointfinder_item_priceforrent3day', $meta),
//    $this->checkMeta('webbupointfinder_item_field499001607305037600000', $meta),
//    $this->checkMeta('webbupointfinder_item_priceforrentmonth', $meta),
//    $this->checkMeta('webbupointfinder_item_transportbodytype', $meta),
//    $this->checkMeta('webbupointfinder_item_transportenginety', $meta),
//    $this->checkMeta('webbupointfinder_item_serv', $meta),
//    $this->checkMeta('webbupointfinder_item_types', $meta),
//    $object['post_date'],
//    0,
//    $this->checkMeta('webbupointfinder_items_address', $meta),
//    $object['post_author'],
//    $this->checkMeta('webbupointfinder_page_itemvisitcount', $meta),
//    $this->checkMeta('webbupointfinder_item_transportcolor', $meta),
//) );