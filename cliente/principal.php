<?php

require 'vendor/autoload.php';
require 'cliente_supermarket.php';



/* CONSTANTES */

const OP_SAIR = 'Sair';
const OP_CANCELAR = 'Cancelar';
const OP_ADICIONAR = 'Adicionar produto';
const OP_EXCLUIR = 'Excluir produto';
const OP_EXIBIR_USUARIO = 'Exibir meus dados pessoais';

const OP_INVALIDA = 'Operação inválida';



/* CLASSES */

/**
 * Interface CLI para o supermarket.
 */
class InterfaceSupermarket {
    private array $usuario = [];
    /**
     * @param ClienteSupermarket cliente_supermarket - Cliente da API do supermarket.
     * @param string temp_msg - Uma mensagem temporária a ser exibida uma vez.
     */
    public function __construct(
        private ClienteSupermarket $cliente_supermarket,
        private string $temp_msg = ''
    ) {}


    /**
     * Exibe o menu principal em loop.
     */
    public function menuPrincipal() {
        $this->menuLogin();
        do {
            $this->limparTela();

            $this->exibirTitulo();

            $this->exibirUsuario();
            
            
            $produtos = $this->cliente_supermarket->getProdutos();
            $this->exibirProdutos($produtos);
        
            $this->exibirMensagemTemporaria();
            
            echo "\n";
            $operacao = $this->menuOperacoes();
        
            switch ($operacao) {
                case OP_SAIR:
                    # Não faz nada, apenas sai
                    break;
                case OP_ADICIONAR:
                    $p = $this->menuAdicionarProduto();
                    $resposta = $this->cliente_supermarket->criarProduto($p);
                   
                    readline();
                    break;
                case OP_EXCLUIR:
                    $id = $this->menuExcluirProduto();
                    $resposta = $this->cliente_supermarket->exluirProdutos($id);
                    $this->exibirErroNaResposta($resposta);
                    break;
                case OP_EXIBIR_USUARIO:
                    $this->menuExibirUsuario();
                    break;
            }
        } while ($operacao != OP_SAIR);
        
        $this->tchau();
    }


    /**
     * Limpa a tela do terminal.
     */
    private function limparTela() {
        echo "\033c";
    }

    
    /**
     * Exibe o título da aplicação.
     */
    public function exibirTitulo() {
        echo
        "\r---------------------------------------------------------------------
        \r                            supermarket
        \r---------------------------------------------------------------------
        ";
    }


    /**
     * Exibe a lista de publicações.
     * 
     * @param array produtos - lista de publicações.
     */
    public function exibirProdutos($produtos) {
        
        foreach ($produtos as $p) {
            echo "
            \r#$p->id $p->nome | Marca: $p->created_at Preço: R$ $p->preco
            \r\"Descrição: $p->descricao\"
            \r";
        }
    }
    
    
    /**
     * Exibe a lista de operações disponíveis e retorna a que o usuário
     * escolher.
     */
    public function menuOperacoes(): string {
        echo "Operações:\n";
        $operacoes = [
            1 => OP_ADICIONAR,
            2 => OP_EXCLUIR,
            3 => OP_EXIBIR_USUARIO,
            0 => OP_SAIR
        ];
        foreach ($operacoes as $i => $op) {
            echo "[$i] $op\n";
        }

        $escolhida = (int) readline('O que você deseja fazer? ');

        if ($escolhida >= count($operacoes) || $escolhida < 0) {
            $this->temp_msg = 'Operação inválida';
            return OP_INVALIDA;
        }
        
        return $operacoes[$escolhida];
    }

    private function menuLogin() {
        $this->limparTela();
        
        $this->exibirTitulo();

        echo "Faça login para começar.\n\n";

        echo 'Matrícula: ';
        $usuario = readline();

        echo 'Senha: ';
        $senha = Seld\CliPrompt\CliPrompt::hiddenPrompt();

        $this->usuario = $this->cliente_supermarket->login($usuario, $senha);
       
    }

    private function exibirUsuario() {
        echo "{$this->usuario['nome']} ({$this->usuario['matricula']})\n";
    }

    public function menuExibirUsuario() {
        echo "Nome: {$this->usuario['nome']}\n";
        echo "Matrícula: {$this->usuario['matricula']}\n";
        echo "Token SUAP: {$this->usuario['suap_token']}\n";
        readline("Aperte ENTER para voltar");
    }

    /**
     * Exibe menu para ler os dados de uma publicação a ser criada.
     */
    public function menuAdicionarProduto() {
        $p = [];
        $p['nome'] = readline('Escreva o nome do Produto: ');
        echo "Escreva a marca do produto:\n";
        $p['marca'] = readline();
        echo "Escreva o preço do produto:\n";
        $p['preco'] = readline();
        echo "Escreva a descrição do produto:\n";
        $p['descricao'] = readline();

        return $p;
    }


    /**
     * Exibe menu para ler o id de uma publicação a ser excluída.
     */
    public function menuExcluirProduto() {
        $id = readline('Digite o # do Produto que você deseja excluir: ');
        return $id;
    }


    /**
     * Exibe uma mensagem temporária, que é apagada em seguida.
     */
    public function exibirMensagemTemporaria() {
        if ($this->temp_msg != '') {
            echo "\n$this->temp_msg\n";
            $this->temp_msg = '';
        }
    }


    /**
     * Exibe uma possível a mensagem de reposta de erro.
     * Se não houver erro, nada é exibido.
     */
    public function exibirErroNaResposta($resposta) {
        if ($resposta->getStatusCode() != 200) {
            $msg = json_decode($resposta->getBody());
            $this->temp_msg = "[$msg->tipo] $msg->conteudo";
        }
    }


    /**
     * Exibe uma mensagem de despedida.
     */
    public function tchau() {
        echo "\nObrigado por usar o Supermarket:)\n\n";
    }
}



/* PROGRAMA PRINCIPAL */


$cliente_supermarket = new ClienteSupermarket(
    'http://localhost:8000/api/',
    'https://suap.ifrn.edu.br/api/v2/'
);

$interface = new InterfaceSupermarket($cliente_supermarket);

$interface->menuPrincipal();

