<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;


/**
 * Cliente da API do Microblog.
 */
class ClienteSupermarket {
    private GuzzleClient $guzzle;


    /**
     * @param string url_servico - URL da API.
     */
    public function __construct(private string $url_servico) {
        $this->guzzle = new GuzzleClient([
            'base_uri' => $this->url_servico,
            'http_errors' => false
        ]);
    }


    /**
     * Retorna a lista de publicações.
     */
    public function getProdutos(){
        $resposta = $this->guzzle->get('produtos');

        $produtos = json_decode($resposta->getBody());
        return $produtos;
    }


    /**
     * Cria uma publicação.
     * 
     * @param array p - array contendo os dados da publicação.
     */
    public function criarProduto($p) {
        $resposta = $this->guzzle->post(
            'produtos',
            ['json' => $p]
        );
        return $resposta;
    }

    
    /**
     * Excui uma publicação.
     * 
     * @param int id - ID da publicação a ser excluída.
     */
    public function exluirProdutos($id) {
        $resposta = $this->guzzle->delete("produtos/$id");
        return $resposta;
    }
}