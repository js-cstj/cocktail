<?php
class Api {
	static $root = "";
	static $api = "https://www.thecocktaildb.com/api/json/v1/1";
	static function init() {
		$root = '';
		$root .= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
		$root .= $_SERVER['HTTP_HOST'];
		$root .= str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
		self::$root = $root;
	}
	static function get($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::api($url));
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
	static function output($output) {
		header('Content-Type: application/json');
		header('Access-Control-Allow-Origin: *');
		echo json_encode($output);
		exit;
	}
	static function root($file = "") {
		if ($file === "") {
			return self::$root;
		}
		return self::$root . '/' . $file;
	}
	static function api($file = "") {
		if ($file === "") {
			return self::$api;
		}
		return self::$api . '/' . $file;
	}
	static function listCategories() {
		$url = 'list.php?c=list';
		$output = self::get($url);
		$output = array_map(function ($item) {
			$slug = self::slugify($item->strCategory);
			return [
				'slug' => $slug,
				'category' => $item->strCategory,
				'url' => self::root('categories/' . $slug),
				'original_url' => self::api('filter.php?c=' . $slug),
			];
		}, $output->drinks);
		return self::results($output, 'categories', ['original_url' => self::api($url)]);
	}
	static function listDrinksByCategory($category) {
		$url = 'filter.php?c=' . $category;
		$output = self::get($url);
		$output = array_map(function ($item) {
			$slug = self::slugify($item->strDrink);
			return [
				'id' => $item->idDrink,
				'slug' => $slug,
				'name' => $item->strDrink,
				'thumb' => $item->strDrinkThumb,
				'url' => self::root('drinks/' . $item->idDrink),
				'original_url' => self::api('www.thecocktaildb.com/api/json/v1/1/lookup.php?i=' . $item->idDrink),
			];
		}, $output->drinks);
		return Api::results($output, 'categories', ['original_url' => Api::api($url)]);
	}
	static function listGlasses() {
		$url = 'list.php?g=list';
		$output = self::get($url);
		$output = array_map(function ($item) {
			$slug = str_replace(" ", "_", $item->strGlass);
			return [
				'slug' => $slug,
				'glass' => $item->strGlass,
				'url' => self::root('glasses/' . $slug),
				'original_url' => self::api('filter.php?g=' . $slug),
			];
		}, $output->drinks);
		return self::results($output, 'glasses', ['original_url' => self::api($url)]);
	}
	static function listIngredients() {
		$url = 'list.php?i=list';
		$output = self::get($url);

		$output = array_map(function ($item) {
			$slug = str_replace(" ", "_", $item->strIngredient1);
			return [
				'slug' => $slug,
				'name' => $item->strIngredient1,
				'url' => self::root('ingredients/' . $slug),
				'original_url' => self::api('filter.php?i=' . $slug),
				'thumb_url' => 'https://www.thecocktaildb.com/images/ingredients/' . str_replace(" ", "%20", $item->strIngredient1) . '-Small.png',
			];
		}, $output->drinks);
		return self::results($output, 'ingredients', ['original_url' => self::api($url)]);
	}
	static function listAlcoholic() {
		$url = 'list.php?a=list';
		$output = self::get($url);
		$output = array_map(function ($item) {
			$slug = str_replace(" ", "_", $item->strAlcoholic);
			return [
				'slug' => $slug,
				'alcoholic' => $item->strAlcoholic,
				'url' => self::root('ingredients/' . $slug),
				'original_url' => self::api('filter.php?a=' . $slug),
			];
		}, $output->drinks);
		return self::results($output, 'alcoholic', ['original_url' => self::api('list.php?a=list')]);
	}
	static function results($results, $type, $other = []) {
		$output['type'] = $type;
		$output['count'] = count($results);
		$output['url'] = self::root($type);
		$output = array_merge($output, $other);
		$output['results'] = $results;
		return $output;
	}
	static function slugify($string) {
		$slug = str_replace([" ", "/"], ["_", "%2F"], $string);
		return $slug;
	}
}
Api::init();
