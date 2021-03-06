<?php
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\Cart;

class Order extends Model{
    const SUCCESS = 'Order-Success';
    const ERROR = 'Order-Error';
    
    public static function getPage($page = 1, $itemsPerPage = 8){
        $start = ($page-1)*$itemsPerPage;
        
        $sql = new Sql();
        $rs = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_orders a    
            INNER JOIN tb_ordersstatus b USING(idstatus)
            INNER JOIN tb_carts c USING(idcart)
            INNER JOIN tb_users d ON d.iduser = a.iduser
            INNER JOIN tb_addresses e USING(idaddress) 
            INNER JOIN tb_persons f ON f.idperson = d.idperson
            ORDER BY a.dtregister DESC
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
            FROM tb_orders a
            INNER JOIN tb_ordersstatus b USING(idstatus)
            INNER JOIN tb_carts c USING(idcart)
            INNER JOIN tb_users d ON d.iduser = a.iduser
            INNER JOIN tb_addresses e USING(idaddress) 
            INNER JOIN tb_persons f ON f.idperson = d.idperson
            WHERE a.idorder = :id OR f.desperson LIKE :search 
            ORDER BY a.dtregister DESC
            LIMIT $start, $itemsPerPage;
        ",[
            ':search'=>'%'.$search.'%',
            ':id'=>$search
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
        
        return $sql->select("
            SELECT * FROM tb_orders a
            INNER JOIN tb_ordersstatus b USING(idstatus)
            INNER JOIN tb_carts c USING(idcart)
            INNER JOIN tb_users d ON d.iduser = a.iduser
            INNER JOIN tb_addresses e USING(idaddress) 
            INNER JOIN tb_persons f ON f.idperson = d.idperson
            ORDER BY a.dtregister DESC
        ");
        
    }
    
    public static function setSuccess($msg){
        
        $_SESSION[Order::SUCCESS] = $msg;
        
    }
   
    public static function getSuccess(){
        
        //Check if the SUCCESS is defined and exists, if it does, it'll return the SUCCESS msg
        $msg = (isset($_SESSION[Order::SUCCESS]) && $_SESSION[Order::SUCCESS]? $_SESSION[Order::SUCCESS] : '' );
        
        Order::clearSuccess();
        
        return $msg;
        
    }
    
    public static function clearSuccess(){
        
        $_SESSION[Order::SUCCESS] = null;
        
    }
    
    public static function setError($msg){
        
        $_SESSION[Order::ERROR] = $msg;
        
    }
   
    public static function getError(){
        
        //Check if the ERROR is defined and exists, if it does, it'll return the ERROR msg
        $msg = (isset($_SESSION[Order::ERROR]) && $_SESSION[Order::ERROR]? $_SESSION[Order::ERROR] : '' );
        
        Order::clearError();
        
        return $msg;
        
    }
    
    public static function clearError(){
        
        $_SESSION[Order::ERROR] = null;
        
    }
    
    public function save(){
        $sql = new Sql();
        
        $rs = $sql->select("CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus, :idaddress, :vltotal)", [
            ':idorder'=>$this->getidorder(), 
            ':idcart'=>$this->getidcart(), 
            ':iduser'=>$this->getiduser(),
            ':idstatus'=>$this->getidstatus(),
            ':idaddress'=>$this->getidaddress(), 
            ':vltotal'=>$this->getvltotal()
        ]);
        
        if($rs > 0){
            $this->setData($rs[0]);
        }
        
    }
    
    public function get($idorder){
        $sql = new Sql();
        
        $rs = $sql->select("
            SELECT * FROM tb_orders a
            INNER JOIN tb_ordersstatus b USING(idstatus)
            INNER JOIN tb_carts c USING(idcart)
            INNER JOIN tb_users d ON d.iduser = a.iduser
            INNER JOIN tb_addresses e USING(idaddress) 
            INNER JOIN tb_persons f ON f.idperson = d.idperson
            WHERE a.idorder = :idorder
        ",[
            ':idorder'=>$idorder
        ]);
        
        if(count($rs) > 0){
            $this->setData($rs[0]);
        }
    }
    
    public function delete(){
        $sql = new Sql();
        
        $sql->query("DELETE FROM tb_orders WHERE idorder = :idorder",[
           ':idorder'=>$this->getidorder() 
        ]);
    }
    
    public function getCart() : Cart{
        $cart = new Cart();
        $cart->get((int)$this->getidcart());
        return $cart;        
    }

   
}

?>