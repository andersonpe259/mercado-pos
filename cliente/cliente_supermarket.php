<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;


/**
 * Cliente da API do Microblog.
 */
class ClienteSupermarket {
    private GuzzleClient $suap;
    
    private GuzzleClient $supermarket;


    /**
     * @param string url_servico - URL da API.
     */
    public function __construct(string $uri_supermarket, string $uri_suap, private string $suap_token = '') {

        $this->supermarket = new GuzzleClient([
            'base_uri' => $uri_supermarket,
            'http_errors' => false
        ]);
        
        $this->suap = new GuzzleClient([
            'base_uri' => $uri_suap,
        ]);
    }


    /**
     * Retorna a lista de publicações.
     */
    public function getProdutos(){
        $resposta = $this->supermarket->get('produtos');
        $produtos = json_decode($resposta->getBody());
        return $produtos;
    }


    /**
     * Cria uma publicação.
     * 
     * @param array p - array contendo os dados da publicação.
     */
    public function criarProduto($p) {
        $resposta = $this->supermarket->post(
            'produtos',
            [
                'json' => $p,
                'headers' => ['Authorization' => "Bearer $this->suap_token"]
            ]
        );
        return $resposta;
    }

    
    /**
     * Excui uma publicação.
     * 
     * @param int id - ID da publicação a ser excluída.
     */
    public function exluirProdutos($id) {
        $resposta = $this->supermarket->delete(
            "produtos/$id",
            ['headers' => ['Authorization' => "Bearer $this->suap_token"]]
        );
        return $resposta;
    }

    public function login($matricula, $senha): array {
        $this->suap_token = $this->criarTokenSUAP($matricula, $senha);

        $usuario = $this->getDadosUsuarioSUAP();
        $usuario['suap_token'] = $this->suap_token;
        
        return $usuario;
    }


    /**
     * Cria o token de acesso ao SUAP.
     * 
     * @param string $matricula A matrícula SUAP do usuário.
     * @param string $senha A senha SUAP do usuário.
     * 
     * @return string O token de acesso gerado pelo SUAP.
     */
    private function criarTokenSUAP($matricula, $senha): string {
        # Envia matrícula e senha no corpo da requisição
        $params = [
            'form_params' => [
                'username' => $matricula,
                'password' => $senha
            ]
        ];

        # Envia requisição ao SUAP para gerar o token de acesso
        $resp = $this->suap->post(
            '/api/v2/autenticacao/token/',
            $params
        );

        # Decodifica os dados da resposta JSON
        $resp_json = json_decode($resp->getBody());
        # Pega o token de acesso
        $token = $resp_json->access;

        return $token;
    }


    /**
     * Pega os dados do usuário no SUAP.
     * 
     * @return array Os dados do usuário no SUAP.
     */
    private function getDadosUsuarioSUAP(): array {
        $res = json_decode(
            $this->suap->get(
                'minhas-informacoes/meus-dados/',
                ['headers' => ['Authorization' => "Bearer $this->suap_token"]]
            )->getBody()->getContents(),
            associative: true
        );

        $dados = [
            'nome' => $res['nome_usual'],
            'matricula' => $res['matricula']
            # Poderia retornar mais dados aqui
        ];

        return $dados;
    }
}