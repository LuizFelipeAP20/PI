<?php 
//namespace do Hcode.
namespace Hcode;


//PageAdmin puxando a extends da Page, no caso puxando todos os elementos já usados lá.
class PageAdmin extends Page {

	//aqui ele passa o construct e fala que o tpl_dir não o mesmo do PAge pq aqui se refere ao pageAdmin.
	public function __construct($opts = array(), $tpl_dir = "/views/admin/"){

		//Aqui ele fala que o ele qr passar os valores desse aqui para o __construct da page. e o resto ela faz a verificação das mesmo telas só que pegando da pasta diferente.  e tem a modificação tb do caminho que é passado pelo index principal de verificação de rotas.
		parent::__construct($opts, $tpl_dir);
	}




}


?>