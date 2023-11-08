<?php
include_once 'Api.php';

$uri = rtrim($_SERVER['REQUEST_URI'], '/');
$sub = dirname($_SERVER['SCRIPT_NAME']);
$uri = substr($uri, strlen($sub));
if ($uri === '') {
	$output = [
		'categories_url' => Api::root('categories'),
		'ingredients_url' => Api::root('ingredients'),
		'glasses_url' => Api::root('glasses'),
		'alcoholic_url' => Api::root('alcoholic'),
	];
	Api::output($output);
}
if ($uri === 'categories') {
	$output = Api::listCategories();
	Api::output($output);
}
if (preg_match('#categories/([a-zA-Z0-9\/_-]+)#', $uri, $matches)) {
	$output = Api::listDrinksByCategory($matches[1]);
	Api::output($output);
	// Api::output($matches);
}
if ($uri === 'glasses') {
	$output = Api::listGlasses();
	Api::output($output);
}
if ($uri === 'ingredients') {
	$output = Api::listIngredients();
	Api::output($output);
}
if ($uri === 'alcoholic') {
	$results = Api::listAlcoholic();
	$output = Api::results($results, 'alcoholic', ['original_url' => Api::api('list.php?a=list')]);
	Api::output($output);
}

Api::output($_SERVER, JSON_PRETTY_PRINT);
// echo $uri;