<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use function Symfony\Component\Console\Input\isArray;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    /**
     * My custom Function to transform an array to paginate date (is used princapaly in when get data from traccar)
     * @param $request
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getPaginatedData($request,$data)
    {

        // Étape 2: Paramètres de pagination
        $currentPage = $request->get('page', 1); // Page actuelle (par défaut 1)
        $perPage = 0;
        $paginatedData = [];
        // Étape 3: Découper les données en fonction de la pagination
        if ($data){
            $perPage = $request->get('per_page', count($data)); // Nombre d'éléments par page (par défaut 15)
            if (is_array($data)  && $currentPage )
            $paginatedData = array_slice($data, ($currentPage - 1) * $perPage, $perPage);
        }else{
            $data = [];
        }
        $paginator = [];
        // Étape 4: Créer une instance de LengthAwarePaginator
        if (count($data) > 0 ){
            $paginator = new LengthAwarePaginator(
                $paginatedData, // Données paginées
                count($data),   // Nombre total d'éléments
                $perPage,       // Nombre d'éléments par page
                $currentPage,   // Page actuelle
                [
                    'path' => $request->url(), // URL actuelle
                    'query' => $request->query() // Paramètres de requête (pour les liens de pagination)
                ]
            );
        }


        // Étape 5: Retourner les données sous forme JSON
        return response()->json($paginator);
    }

    protected function json(string $message = null, $data = [], $statusCode = 200, array $headers = [])
    {
        $content = [];

        if ($message) {
            $content['message'] = $message;
        }

        if (!empty($data)) {
            $content['data'] = $data;
        }

        return response()->json($content, $statusCode, $headers, JSON_PRESERVE_ZERO_FRACTION);
    }
}
