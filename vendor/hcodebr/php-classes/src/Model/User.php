<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{

	const SESSION = "User";
	const SECRET = "Ecommerce_Secret";
	const SECRET_IV = "Ecommerce_Secret_IV";

	public static function login($login, $password){

		$sql = new Sql();

		$results = $sql->select("select * from tb_users where deslogin = :LOGIN", array(":LOGIN"=>$login ));

		if(count($results) === 0){

			throw new \Exception("Usuario ou senha incorreto", 1);
			
		}
		$data = $results[0];

		if(password_verify($password, $data["despassword"]) === true ){

			$user = new User();
			$user->setData($data);
			$_SESSION[User::SESSION] = $user->getValues();
			if($data["inadmin"] == 0){
			header("Location: /res/admin/index2.html");
			}else if($data["inadmin"] == 3){
			header("Location: /");
			}else{
			header("Location: /admin");
				}
		}else {

			throw new \Exception("Usuario ou senha incorreto", 1);
			header("Location: /admin/login");
		}

	}
//verificação de login
	public static function verify_login($inadmin = true){
		if(
			!isset($_SESSION[User::SESSION]) 
			|| !$_SESSION[User::SESSION] 
			|| !(int)$_SESSION[User::SESSION]["iduser"] > 0 
			|| (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin ){
			header("Location: /admin/login");
			exit;
		}
	}
	//deslogar
	public static function logout() {

		$_SESSION[User::SESSION] = NULL; 

	}
	//lista todos os usuarios
	public static function listAll(){
			$sql = new Sql();
			return $sql->select("select*from tb_users a inner join tb_persons b USING(idperson) order by b.desperson;");
		}
	//lista o usuario que vai editado
	public function get($iduser)
	{
	 
	 $sql = new Sql();
	 
	 $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser;", array(
	 ":iduser"=>$iduser
	 ));
	 
	 $data = $results[0];
	 
	 $this->setData($data);
	 
	 }

	 //salvar insert no sistema cadastro de usuario.
 	public function save(){
 		/*
		pdesperson VARCHAR(64), 
		pdeslogin VARCHAR(64), 
		pdespassword VARCHAR(256), 
		pdesemail VARCHAR(128), 
		pnrphone BIGINT, 
		pinadmin TINYINT
 		*/

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", 
			array(
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));
		$this->setData($results[0]);

 	}

 	//update da tela de usuarios
 	public function update(){
 		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", 
			array(
			":iduser"=>$this->getiduser(),
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));
		$this->setData($results[0]);
 	}

 	public function delete(){
 		$sql = new Sql();
	 
	 $results = $sql->query("CALL sp_users_delete(:iduser)", array(
	 ":iduser"=>$this->getiduser()
	 ));
 	}
//manda o email ao ususario. e encrypta o codigo	
 	public static function getForgot($email){
 		$sql = new Sql();
		$results = $sql->select("select*from tb_persons a inner join tb_users b USING(idperson) where a.desemail = :email", 
		array(
			":email"=>$email
		));
		if(count($results) === 0){
			throw new \Exception("Não foi possivel recuperar a senha");
		}else {

			$data = $results[0];

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data['iduser'],
				":desip"=>$_SERVER['REMOTE_ADDR']
			));
			if(count($results2) === 0){
			throw new \Exception("Não foi possivel recuperar a senha");

		} else {
			$dataRecovery = $results2[0];

			$code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

				$code = base64_encode($code);

			//alterar aqui o link para subir.
			$link = "http://www.gestcon.com/admin/forgot/reset?code=$code";
			$mailer = new Mailer($data["desemail"], $data["desperson"], "GESTCON - Redefinir Senha", "forgot",array(
					"name"=>$data["desperson"],
					"link"=>$link
			));
			$mailer->send();

			return $data;
		}
 	}

}
//decrytpa o codigo. faz a verificação de 1hora.
	public static function validForgotDecrypt($code)
	{

		$code = base64_decode($code);

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
		", array(
			":idrecovery"=>$idrecovery
		));

		if (count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else
		{

			return $results[0];

		}

	}
	//atualiza a data de recuperação.
	public static function setFogotUsed($idrecovery)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));

	}
	//atualiza a senhaw
	public function setPassword($password)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));

	}

}
?>