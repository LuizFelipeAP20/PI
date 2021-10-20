<?php 
//INDEX DAS ROTAS

session_start();

//carregar o autoload das classes;
require_once("vendor/autoload.php");

use \Slim\Slim;//Slim Questão das rotas.
use \Hcode\Page;//Class da Page padrão
use \Hcode\PageAdmin;//class page Admin
use \Hcode\Model\User;//class user valida login.

//instanciando o Slim para usar.
$app = new Slim();
//config do slim
$app->config('debug', true);
//trabalhando com as portas na function. Então esse "/"" é pagina raiz; Mas conhecida como padrão. Exemplo : Localhost:8080/
$app->get('/', function() {
    
    //$page recebe a classe page que passa via setTPL o Index onde fala que Index é o principal
    //ai a propria classe já entende que deve carregar os elementos

	$page = new Page();
	$page->setTpl("Index");

});

//Dentro desses $app de function  a unica coisa que vai mudar é questão de rota e a classe e as paginas. 
//e são as unicas coisas que vão ser alteradas.


//Indo da raiz para a parte do admin. Exemplo : Localhost:8080/Admin/
$app->get('/admin', function() {
    
    User::verify_login();

	//$page recebe a classe page que passa via setTPL o Index onde fala que Index é o principal
    //ai a propria classe já entende que deve carregar os elementos

	$page = new PageAdmin();
	$page->setTpl("Index");

});

$app->get('/admin/login', function() {
    
	//No caso esse aqui é de login então o header e o footer não carrega aqui.
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("login");

});

$app->post('/admin/login', function() {
    //Passa via post os valores para a classe user de function login
    User::login($_POST["login"], $_POST["password"]);

    exit;

});

$app->get('/admin/logout', function() {
    //vai para logout na class User usando a function logout;
    User::logout();

    header("location: /admin/login/sistem");
    exit;

});


$app->get("/admin/users", function(){
	//User::verify_login();

	$users = User::listAll();
	$page = new PageAdmin();
	$page->setTpl("users", array(
		"users"=>$users
	));
});
$app->get("/admin/users/creates", function(){
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("users-creates");
});

$app->get("/admin/users/create", function(){
	User::verify_login();
	$page = new PageAdmin();
	$page->setTpl("users-create");
});

$app->get("/admin/users/:iduser/delete", function($iduser){
	User::verify_login();

	$user = new User();
 
   $user->get((int)$iduser);

	$user->delete();
 
   header("Location: /admin/users");
 	exit;


});

//para editar é mandado o id so usuario onde passa qual ID deve ser editado
$app->get('/admin/users/:iduser', function($iduser){
 
   User::verify_login();
 
   $user = new User();
 
   $user->get((int)$iduser);
 
   $page = new PageAdmin();
 
   $page ->setTpl("users-update", array(
        "user"=>$user->getValues()
    ));
});
$app->post("/admin/users/creates", function () {

	$user = new User();

 	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 0 : 3;

 	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [

 		"cost"=>12

 	]);

 	$user->setData($_POST);

	$user->save();

	header("Location: /admin/login/sistem");
 	exit;

});
//link cara criar user.
$app->post("/admin/users/create", function () {

 	User::verify_Login();

	$user = new User();

 	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

 	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [

 		"cost"=>12

 	]);

 	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
 	exit;

});

//link para editar!
$app->post("/admin/users/:iduser", function($iduser){
	User::verify_login();
 
   $user = new User();
   $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
   $user->get((int)$iduser);
 
   $user->setData($_POST);
   $user->update();
 
   header("Location: /admin/users");
 	exit;
});

//link para esqueci a senha!
$app->get("/admin/forgot", function(){
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot");
});

//link para esqueci a senha!
$app->post("/admin/forgot", function(){
	$email = $_POST['email'];
	$user = User::getForgot($email);

	header("Location: /admin/forgot/sent");
 	exit;

});
//sucesso envio de email.
$app->get("/admin/forgot/sent", function(){
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-sent");


});
//reset vai para o alterar email.
$app->get("/admin/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
});
//resetar a senha
$app->post("/admin/forgot/reset", function(){
//decrypta e pega o id da recuperação.
	$forgot = User::validForgotDecrypt($_POST["code"]);
//verifica a data que foi feita o idrecovery se esta dentro de 1 hora, se não estiver não é mais um cod valido.
	//e sera preciso pedir outro codigo.
	$user = User::setFogotUsed($forgot["idrecovery"]);

//da um set no password onde muda a senha do usuario passado pelo id do usuario.
	$user = new User();
	$user->get((int)$forgot["iduser"]);


	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);


	$user->setPassword($password);
	
//traz a pagina na qual ele qr quando atualizar a senha.
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-reset-success");
});

//pi

$app->get('/admin/login/sistem', function() {
    
	//No caso esse aqui é de login então o header e o footer não carrega aqui.
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("login2");

});

//link para esqueci a senha!
$app->get("/admin/forgot/sistem", function(){
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot");
});

//link para esqueci a senha!
$app->post("/admin/forgot/sistem", function(){
	$email = $_POST['email'];
	$user = User::getForgot($email);

	header("Location: /admin/forgot/sent");
 	exit;


$app->get('/admin/logout', function() {
    //vai para logout na class User usando a function logout;
    User::logout();

    header("location: /admin/login");
    exit;

});


$app->get("/admin/users/sistem", function(){
	
	

	$users = User::listAll();
	$page = new PageAdmin();
	$page->setTpl("users", array(
		"users"=>$users
	));
});



$app->get("/admin/users/:iduser/delete", function($iduser){
	User::verify_login();

	$user = new User();
 
   $user->get((int)$iduser);

	$user->delete();
 
   header("Location: /admin/users");
 	exit;


});

//para editar é mandado o id so usuario onde passa qual ID deve ser editado
$app->get('/admin/users/:iduser', function($iduser){
 
   User::verify_login();
 
   $user = new User();
 
   $user->get((int)$iduser);
 
   $page = new PageAdmin();
 
   $page ->setTpl("users-update", array(
        "user"=>$user->getValues()
    ));
});
//link cara criar user.


//link para editar!
$app->post("/admin/users/:iduser", function($iduser){
	User::verify_login();
 
   $user = new User();
   $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
   $user->get((int)$iduser);
 
   $user->setData($_POST);
   $user->update();
 
   header("Location: /admin/users");
 	exit;
});
});



//Esse run faz a verificação geral, se está tudo carregado corretamente ai ele executa o total.

$app->run();

 ?>