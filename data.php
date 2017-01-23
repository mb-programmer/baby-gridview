<?php

$req = json_decode($_POST['req']);

$filters = $req->filters;
$sorts = $req->sorts;
$pagination = $req->pagination;

//var_dump($pagination);

$db = new PDO('sqlite:database.sqlite');
$query = 'SELECT * FROM users';
$query = processFilters($query, $filters);
$query = processSorts($query, $sorts);

$queryCount = str_replace('*', 'count(*)', $query);
$result = $db->query($queryCount);
$pagination->total = (integer)$result->fetchColumn();

$query = $query . ' limit ' . $pagination->per_page;
$query = $query . ' offset ' . $pagination->from;

//var_dump($query);
$result = $db->query($query, PDO::FETCH_ASSOC);
//var_dump($result);

$users = $result->fetchAll();




header('Content-Type: application/json');
echo json_encode([
    'data' => $users,
    'pagination' => $pagination,
]);


function processFilters($query, $filters)
{
    if (count($filters) > 0) {
        $query .= ' WHERE ';
        for ($i = 0; $i < count($filters); $i++) {
            $filter = $filters[$i];
            if ($i != 0) {
                $query .= ' AND ';
            }
            switch ($filter->operator) {
                case 'equal':
                    $query .= $filter->name . '=' . "'" . $filter->oprand1 . "'";
                    break;
                case 'contain':
                    $query .= $filter->name . ' LIKE' . " '%" . $filter->oprand1 . "%'";
                    break;
            }
        }

    }

    return $query;
}

function processSorts($query, $sorts)
{
    if (count($sorts) > 0) {
        $sql = ' order by ';
        foreach ($sorts as $sort) {
            $query .= ' order by ' . $sort->name . ' ' . $sort->type;
        }
        $query .= $sql;
    }

    return $query;
}