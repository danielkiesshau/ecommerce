<?php
namespace Hcode\Model;
// Contra barra inicial indica para começar da root do projeto
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Product extends Model{

    public static function getPage($page = 1, $itemsPerPage = 8){
        $start = ($page-1)*$itemsPerPage;
        
        $sql = new Sql();
        $rs = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_products ORDER BY desproduct
            LIMIT $start, $itemsPerPage;
        ");
        $total = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
                'data'=>$rs,
                'total'=>(int)$total[0]["nrtotal"],
                'pages'=>ceil($total[0]["nrtotal"]/ $itemsPerPage)
               ];
    }
   
    public static function getPageSearch($search, $page = 1, $itemsPerPage = 8){
        $start = ($page-1)*$itemsPerPage;
        $adm = 0;
        strcasecmp($search,'admin') == 0 ? $adm = 1 : 0;
        $sql = new Sql();
        
        $rs = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_products 
            WHERE desproduct LIKE :search 
            ORDER BY desproduct
            LIMIT $start, $itemsPerPage;
        ",[
            ':search'=>'%'.$search.'%',
        ]);
        $total = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
                'data'=>$rs,
                'total'=>(int)$total[0]["nrtotal"],
                'pages'=>ceil($total[0]["nrtotal"]/ $itemsPerPage)
               ];
    }
    
    public static function listAll(){
        $sql = new Sql();
        return  $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
    }
   
    public static function checkList($list){
        foreach($list as &$row){
            $p = new Product();
            $p->setData($row);
            $row = $p->getValues();
            
        }
        
        return $list;
    }

    public function save(){
        $sql = new Sql();
       
    
        $rs = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllenght, :vlweight, :desurl)" , array(
            ":idproduct"=>$this->getidproduct(),
            ":desproduct"=>$this->getdesproduct(),
            ":vlprice"=>$this->getvlprice(),
            ":vlwidth"=>$this->getvlwidth(),
            ":vlheight"=>$this->getvlheight(),
            ":vllenght"=>$this->getvllength(),
            ":vlweight"=>$this->getvlweight(),
            ":desurl"=>$this->getdesurl()
        ));
    

        $this->setData($rs[0]);
        
    }
    
    public function get($idproduct){
        $sql = new Sql();
        $rs = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(":idproduct"=>$idproduct));
        $this->setData($rs[0]);
    }
    
    public function delete(){
        $sql = new Sql();
        $sql->query("DELETE FROM tb_products  WHERE idproduct = :idproduct", array(":idproduct"=>$this->getidproduct()));
    }
    
    public function checkPhoto(){
        
        if(file_exists($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
                       "res".DIRECTORY_SEPARATOR.
                       "site".DIRECTORY_SEPARATOR.
                       "img".DIRECTORY_SEPARATOR.
                       "products".DIRECTORY_SEPARATOR.
                       $this->getidproduct().".jpg")){
            $url = "/res/site/img/products/".$this->getidproduct().".jpg";
        }else{
            $url = "/res/site/img/product.jpg";
            
        }
        
        return $this->setdesphoto($url);
    }
    
    public function getValues(){
        $this->checkPhoto();
        $values = parent::getValues();
        
        return $values;
    }
    
    public function setPhoto($file){
        $extension = explode('.', $file["name"]);
        $extension = end($extension);
        
        $dest = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
                       "res".DIRECTORY_SEPARATOR.
                       "site".DIRECTORY_SEPARATOR.
                       "img".DIRECTORY_SEPARATOR.
                       "products".DIRECTORY_SEPARATOR.
                       $this->getidproduct().".jpg";
        
        switch($extension){
            case "jpg":
            case "jpeg":
                $image = imagecreatefromjpeg($file["tmp_name"]);
                break;
            case "gif":
                $image = imagecreatefromgif($file["tmp_name"]);
                break;
            case "png":
                $image = imagecreatefrompng($file["tmp_name"]);
                break;
                
        }
        
        echo $dest;
        echo "<br/>".$image;
        echo '<img src ='.$dest.'/>';
        
        
        imagejpeg($image, $dest);
        
        
        $this->checkPhoto();
    }
    
    public function getFromURL($desurl){
        $sql = new Sql();
        $rs = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1",[':desurl'=>$desurl]);
        $this->setData($rs[0]);
    }
    
    public function getCategories(){
        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory=b.idcategory WHERE b.idproduct = :idproduct",[
            ':idproduct'=>$this->getidproduct() 
        ]);
    }
    
}


?>