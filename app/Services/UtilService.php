<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class UtilService
{
    /**
     * Exécute une requête personnalisée sur une table donnée.
     *
     * @param string $table Le nom de la table (obligatoire)
     * @param array $where Conditions where sous forme de tableau ['colonne' => 'valeur'] (optionnel)
     * @param array $joins Jointures sous forme de tableau [['type' => 'join|leftJoin', 'table' => 'nom_table', 'on' => ['col1', 'col2']]] (optionnel)
     * @param array $select Colonnes à sélectionner (optionnel)
     * @param string $method Méthode de récupération ('first', 'pluck', 'get')
     * @param string|null $pluckColumn Colonne à extraire si 'pluck' est choisi
     * @return mixed
     */
    public function queryTable(string $table, array $where = [], array $joins = [], array $select = ['*'], string $method = 'get', string $pluckColumn = null)
    {
        $query = DB::table($table);

        foreach ($joins as $join) {
            if ($join['type'] === 'join') {
                $query->join($join['table'], $join['on'][0], '=', $join['on'][1]);
            } elseif ($join['type'] === 'leftJoin') {
                $query->leftJoin($join['table'], $join['on'][0], '=', $join['on'][1]);
            }
        }

        if (!empty($where)) {
            foreach ($where as $column => $value) {
                $query->where($column, $value);
            }
        }

        $query->select($select);

        if ($method === 'first') {
            return $query->first();
        } elseif ($method === 'pluck' && $pluckColumn) {
            return $query->pluck($pluckColumn);
        } else {
            return $query->get();
        }
    }

    public function formatPrice($price)
    {
        if ($price == 0) {
            $price = 0 . " ";
        } else if ($price < 1000000 and $price != 0) {
            $price = number_format($price / 1000, 2) . "K ";
        } else if ($price < 1000000000) {
            $price = number_format($price / 1000000, 2) . 'M ';
        } else {
            $price = number_format($price / 1000000000, 2) . 'B ';
        }

        return $price;
    }
}
