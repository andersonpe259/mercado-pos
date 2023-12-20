<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Supermarket;
use App\Http\Middleware\VerificarTokenSUAP;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('produtos',
    function(Request $request) {
        $produtos = Supermarket::all();
        return response($produtos, 200);
    }
);


Route::get('produtos/{id}',
    function(Request $request, $id) {
        $produto = Supermarket::find($id);
        if($produto != null){ 

            return response()->json([
                $produto
            ]);

        }
        else{
            return response()->json(
                    [
                        'tipo' => 'erro',
                        'conteudo' => 'Não encontrado.'
                    ],
                404
            );
        }
        
    }
);

Route::middleware(VerificarTokenSUAP::class)->group(function(){
    Route::post('produtos',
        function(Request $request) {
            $request->validate([
                'nome' => 'required',
                'marca' => 'required',
                'preco' => 'required|numeric',
                'descricao' => 'required'
            ]);
            $produto = new Supermarket;
            $produto->nome = $request->nome;
            $produto->marca = $request->marca;
            $produto->preco = $request->preco;
            $produto->descricao = $request->descricao;
            $produto->save();
        
            return response()->json([
            $produto
            ], 201);
        }
    );

    Route::put('produtos/{id}',
    function(Request $request, $id) {
        $request->validate([
            'nome' => 'required',
            'marca' => 'required',
            'preco' => 'required|numeric',
            'descricao' => 'required'
        ]);
        $produto = Supermarket::find($id);
        
        $produto->update([
            'nome' => $request->nome,
            'marca' => $request->marca,
            'preco' => $request->preco,
            'descricao' => $request->descricao,
        ]);
        
        return response()->json([$produto],
            200
        );
    }
    );

    Route::delete('produtos/{id}',
        function(Request $request, $id) {
            # Apenas um exemplo de resposta. Os dados deveriam vir do banco.
            $produto = Supermarket::findOrFail($id);
            $produto->delete();
            return response()->json(
                [
                    'tipo' => 'info',
                    'conteudo' => "Produto apagado.",
                ], 200
            );
            
        }
    );
});
