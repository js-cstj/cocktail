<?php
include_once 'Api.php';

$uri = Api::uri();
if ($uri === '') {
	$output = [
		'url_categories' => Api::root('categories'),
		'url_ingredients' => Api::root('ingredients'),
		'url_glasses' => Api::root('glasses'),
		'url_alcoholic' => Api::root('alcoholic'),
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
	$output = Api::results($results, 'alcoholic', ['url_original' => Api::remote('list.php?a=list')]);
	Api::output($output);
}
if (preg_match('#drinks/([0-9]+)#', $uri, $matches)) {
	$output = Api::drink($matches[1]);
	Api::output($output);
	// Api::output($matches);
}

Api::output($_SERVER, JSON_PRETTY_PRINT);
// echo $uri;