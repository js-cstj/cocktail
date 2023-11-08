<?php
class Api
{
	static $root = "";
	static $api_root = "https://www.thecocktaildb.com/";
	static $api = "api/json/v1/1/";
	static function init()
	{
		$root = '';
		$root .= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
		$root .= $_SERVER['HTTP_HOST'];
		$root .= str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
		self::$root = $root;
	}
	static function get($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::remote($url));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		if (curl_errno($ch)) {
			echo json_encode(
				[
					'error' => curl_error($ch),
				]
			);
			exit;
		}
		curl_close($ch);
		return json_decode($output);
	}
	static function output($output)
	{
		header('Content-Type: application/json');
		header('Access-Control-Allow-Origin: *');
		echo json_encode($output);
		exit;
	}
	static function root($file = "")
	{
		if ($file === "") {
			return self::$root;
		}
		return self::$root . '/' . $file;
	}
	static function remote($file = "")
	{
		if ($file === "") {
			return self::$api_root.self::$api;
		}
		return self::$api_root.self::$api . $file;
	}
	static function listCategories()
	{
		$url = 'list.php?c=list';
		$output = self::get($url);
		$output = array_map(function ($item) {
			$slug = self::slugify($item->strCategory);
			return [
				'slug' => $slug,
				'category' => $item->strCategory,
				'url' => self::root('categories/' . $slug),
				'url_original' => self::remote('filter.php?c=' . $slug),
			];
		}, $output->drinks);
		return self::results($output, 'categories', ['url_original' => self::remote($url)]);
	}
	static function listDrinksByCategory($category)
	{
		$url = 'filter.php?c=' . $category;
		$raw = self::get($url);
		$output = array_map(function ($item) {
			$slug = self::slugify($item->strDrink);
			return [
				'id' => $item->idDrink,
				'slug' => $slug,
				'name' => $item->strDrink,
				'url_thumb' => $item->strDrinkThumb,
				'url' => self::root('drinks/' . $item->idDrink),
				'url_original' => self::remote('lookup.php?i=' . $item->idDrink),
			];
		}, $raw->drinks);
		return Api::results($output, 'categories', ['category' => $category, 'url_original' => Api::remote($url)]);
	}
	static function drink($id)
	{
		$url = 'lookup.php?i=' . $id;
		$raw = self::get($url);
		$drink = $raw->drinks[0];
		$output = [
			'type' => 'drink',
			'id' => $id,
			'name' => $drink->strDrink,
			'name_alternate' => $drink->strDrinkAlternate,
			'tags' => (is_null($drink->strTags)) ? [] : explode(",", $drink->strTags),
			'url' => self::root('drinks/' . $id),
			'url_original' => Api::remote($url),
			'url_thumb' => $drink->strDrinkThumb,
			'url_video' => $drink->strVideo,
			'iba' => $drink->strIBA,
			'category' => [
				'name' => $drink->strCategory,
				'slug' => self::slugify($drink->strCategory),
				'url' => self::root('categories/' . self::slugify($drink->strCategory)),
			],
			'glass' => [
				'name' => $drink->strGlass,
				'slug' => self::slugify($drink->strGlass),
				'url' => self::root('glasses/' . self::slugify($drink->strGlass)),
			],
			'alcoholic' => [
				'name' => $drink->strAlcoholic,
				'slug' => self::slugify($drink->strAlcoholic),
				'url' => self::root('alcoholic/' . self::slugify($drink->strAlcoholic)),
			],
			'ingredients' => self::getIngredients($drink),
			'instructions' => self::getInstructions($drink),
			'image_source' => $drink->strImageSource,
			'image_attribution' => $drink->strImageAttribution,
			'creative_commons_confirmed' => ($drink->strCreativeCommonsConfirmed) != "No",
			'date_modified' => $drink->dateModified,
		];
		return Api::output($output);
	}
	static function getInstructions($drink) {
		$instructions0 = array_filter((array) $drink, function ($key) {
			return (strpos($key, 'strInstructions') !== false);
		}, ARRAY_FILTER_USE_KEY);
		$instructions = [];
		foreach ($instructions0 as $key => $value) {
			$key = str_replace("strInstructions", "", $key);
			$instructions[$key ?: "EN"] = $value;
		}
		return $instructions;
	}
	static function getIngredients($drink) {
		$ingredients0 = array_filter((array) $drink, function ($value, $key) {
			return (strpos($key, 'strIngredient') !== false) && !is_null($value);
		}, ARRAY_FILTER_USE_BOTH);
		$ingredients = [];
		foreach ($ingredients0 as $key => $value) {
			$ingredient = [
				'name' => $value,
				'url' => self::root('ingredients/' . self::slugify($value)),
				'url_thumb' => self::urlImageIngredient($value, "Small"),
				'measure' => $drink->{'strMeasure' . str_replace("strIngredient", "", $key)},
			];
			$ingredients[] = $ingredient;
		}
		return $ingredients;
	}
	static function listGlasses()
	{
		$url = 'list.php?g=list';
		$output = self::get($url);
		$output = array_map(function ($item) {
			$slug = str_replace(" ", "_", $item->strGlass);
			return [
				'slug' => $slug,
				'glass' => $item->strGlass,
				'url' => self::root('glasses/' . $slug),
				'url_original' => self::remote('filter.php?g=' . $slug),
			];
		}, $output->drinks);
		return self::results($output, 'glasses', ['url_original' => self::remote($url)]);
	}
	static function listIngredients()
	{
		$url = 'list.php?i=list';
		$output = self::get($url);

		$output = array_map(function ($item) {
			$slug = str_replace(" ", "_", $item->strIngredient1);
			return [
				'slug' => $slug,
				'name' => $item->strIngredient1,
				'url' => self::root('ingredients/' . $slug),
				'url_original' => self::remote('filter.php?i=' . $slug),
				'url_thumb' => self::urlImageIngredient($item->strIngredient1),
			];
		}, $output->drinks);
		return self::results($output, 'ingredients', ['url_original' => self::remote($url)]);
	}
	static function urlImageIngredient($ingredient, $size = "")
	{
		$size = ($size === "") ? "" : "-".$size;
		return self::$api_root . 'images/ingredients/' . str_replace(" ", "%20", $ingredient) .$size.'.png';
	}
	static function listAlcoholic()
	{
		$url = 'list.php?a=list';
		$output = self::get($url);
		$output = array_map(function ($item) {
			$slug = str_replace(" ", "_", $item->strAlcoholic);
			return [
				'slug' => $slug,
				'alcoholic' => $item->strAlcoholic,
				'url' => self::root('ingredients/' . $slug),
				'url_original' => self::remote('filter.php?a=' . $slug),
			];
		}, $output->drinks);
		return self::results($output, 'alcoholic', ['url_original' => self::remote('list.php?a=list')]);
	}
	static function results($results, $type, $other = [])
	{
		$output['type'] = $type;
		$output['count'] = count($results);
		$output['url'] = self::root($type);
		$output = array_merge($output, $other);
		$output['results'] = $results;
		return $output;
	}
	static function slugify($string)
	{
		$slug = str_replace([" ", "/"], ["_", "%2F"], $string);
		return $slug;
	}
	static function uri()
	{
		$sub = substr($_SERVER['SCRIPT_NAME'], 0, -strlen(basename($_SERVER['SCRIPT_NAME'])));
		$uri = $_SERVER['REQUEST_URI'];
		$uri = substr($uri, strlen($sub));
		// $uri = trim($uri, "/");
		return $uri;
	}
}
Api::init();
