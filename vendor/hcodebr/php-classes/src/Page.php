<?php 
//Namespace qr dizer que todos os elementos aqui são desse namespace. 
//Geralmente usado para  trabalhar com outro projetos.
namespace Hcode;

//Use é para usar a classe TPL que é padrão carregada pelo composer.
use Rain\Tpl;

class Page {
	//instanciando como private para não ser acessadas por outras classes 
	private $tpl;
	//options por padrão já vai ser um array
	private $options = [];
	//defaults por padrão já vai ser um array, com data que no caso é questão de dados.
	private $defaults = [
		"header"=>true,
		"footer"=>true,
		"data"=>[]
	];
 	//Method da class page. __construct pega por padrão opts como array que no caso é o que vai ser  definido a pagina padrão.
 	//e o tpl_dir pega o diretorio onde ele vai estar.
	public function __construct($opts = array(), $tpl_dir ="/views/"){

		// aqui ele define que um merge onde a ordem é de um sobrescrever o outro então no caso : Defaults é o primeiro
		//tecnicamente se não passar nada no $opts  ele pega o primeiro, se for definido noo segundo ele pega o do segundo. 
		// e armazena no PRIVATE da classe, essa referenciação é data pelo "this". ta falando que é o de fora da classe
		// um exemplo é o global..

		$this->options = array_merge($this->defaults, $opts );

		//passando as configurações de diretorios.
		$config = array(
					"tpl_dir"       =>$_SERVER["DOCUMENT_ROOT"].$tpl_dir,
					"cache_dir"     =>$_SERVER["DOCUMENT_ROOT"]."/views-cache/",
					"debug"         => false
				   );

		Tpl::configure($config);

		//instanciando o Tpl e passando a informação que veio no options.
		//passando pro setData onde ele faz a verificação de chave e valor ali em baixo.
		$this->tpl = new Tpl;
		$this->setData($this->options["data"]);

		//header ele pega e escreve o header na pagina.
		if($this->options["header"]===true)$this->tpl->draw("header");

	}
	//ele tras os valores de options passando pro set data como parametro.
	// onde ele faz chave e valor dentro dos elementos.
	public function setData($data = array()){

		foreach ($data as $key => $value) {
			$this->tpl->assign($key, $value);
		}

	}

	//aqui ele faz as verificações, pega todos os elemntos e passa para o tpl e o tpl da um draw, no caso escreve em tela.
	public function setTpl($name, $data = array(),$returnHTML = false ){
		//aqui ele manda novamente para o setData onde faz as verificações de chave e valor de cada elemento.
		$this->setData($data);		
		// o retorno dele com o nome é os valores da pagina no returnHTML.
		return $this->tpl->draw($name, $returnHTML); 

	}

	//passa ao tpl o footer, para executar. junto com os demais elementos
	public function __destruct(){
		if($this->options["footer"]===true)$this->tpl->draw("footer");
	}

}

?>